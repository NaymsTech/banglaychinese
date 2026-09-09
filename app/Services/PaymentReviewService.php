<?php

namespace App\Services;

use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Canonical payment review for course enrollments and digital product
 * orders. Every transition changes the unified Order/Payment records first
 * and mirrors the resulting state onto the legacy record inside the same
 * database transaction, so the two structures can never drift apart.
 *
 * Transactional emails keep the existing email architecture untouched. The
 * canonical Order markers are the single claim location; the claimed marker
 * is mirrored onto the legacy table so dashboards and rollback paths agree.
 * Reminders, creation-time emails and the enrollment "completed" transition
 * deliberately stay in their legacy homes.
 */
class PaymentReviewService
{
    public function __construct(private OrderMaterializer $materializer) {}

    /**
     * @return array{changed: bool, emailed: bool}
     */
    public function approve(Enrollment|DigitalOrder $sale): array
    {
        $fresh = $this->fresh($sale);
        $order = $this->resolveOrder($fresh);

        $changed = false;

        DB::transaction(function () use ($fresh, $order, &$changed): void {
            $settled = $this->settlePaymentsToPaid($order, $fresh);
            $mirrored = $this->mirrorApproval($fresh);

            $changed = $settled || $mirrored;
        });

        $emailed = $this->sendOutcomeEmail($fresh, $order);

        return ['changed' => $changed, 'emailed' => $emailed];
    }

    /**
     * @return array{changed: bool, emailed: bool}
     */
    public function reject(Enrollment|DigitalOrder $sale, ?string $reason = null): array
    {
        $fresh = $this->fresh($sale);
        $order = $this->resolveOrder($fresh);

        $changed = false;

        DB::transaction(function () use ($fresh, $order, $reason, &$changed): void {
            $rejectedClaims = $this->rejectClaimPayments($order, $reason);
            $mirrored = $this->mirrorRejection($fresh, $reason);

            $changed = $rejectedClaims || $mirrored;

            if ($changed) {
                $this->setOrderStatus($order, Order::STATUS_CANCELLED);
                $this->clearOutcomeMarker($fresh);
            }
        });

        if (! $changed) {
            return ['changed' => false, 'emailed' => false];
        }

        $emailed = $this->sendRejectionEmail($fresh, $order, $reason);

        return ['changed' => true, 'emailed' => $emailed];
    }

    /**
     * @return array{changed: bool, emailed: bool}
     */
    public function requestCorrection(Enrollment|DigitalOrder $sale, string $action): array
    {
        $fresh = $this->fresh($sale);
        $order = $this->resolveOrder($fresh);

        $changed = false;

        DB::transaction(function () use ($fresh, $order, $action, &$changed): void {
            // The legacy record is the guard: a sale only becomes eligible for
            // correction when the legacy transition succeeds, mirroring the
            // exact eligibility the current admin actions rely on.
            $changed = $this->mirrorCorrection($fresh, $action);

            if ($changed) {
                $this->flagClaimsForCorrection($order, $fresh, $action);
                $this->setOrderStatus($order, Order::STATUS_PENDING);
                $this->clearOutcomeMarker($fresh);
            }
        });

        if (! $changed) {
            return ['changed' => false, 'emailed' => false];
        }

        $emailed = $this->sendAttentionEmail($fresh, $order, $action);

        return ['changed' => true, 'emailed' => $emailed];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function resubmit(Enrollment|DigitalOrder $sale, array $data): bool
    {
        $fresh = $this->fresh($sale);
        $order = $this->resolveOrder($fresh);

        $changed = false;

        DB::transaction(function () use ($fresh, $order, $data, &$changed): void {
            $changed = $this->mirrorResubmission($fresh, $data);

            if ($changed) {
                $this->applyResubmissionToCanonical($order, $fresh, $data);
            }
        });

        return $changed;
    }

    protected function settlePaymentsToPaid(Order $order, Enrollment|DigitalOrder $sale): bool
    {
        $total = round((float) $order->total_amount, 2);
        $expectedStatus = $sale instanceof Enrollment
            ? Order::STATUS_IN_PROGRESS
            : Order::STATUS_COMPLETED;

        $payments = $order->payments()->lockForUpdate()->get();
        $netPaid = $this->netPaid($payments);
        $claims = $payments->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION]);

        if ($claims->isEmpty() && $netPaid >= $total && $order->order_status === $expectedStatus) {
            return false;
        }

        $changed = false;

        foreach ($claims as $payment) {
            $updated = DB::table('payments')
                ->where('id', $payment->id)
                ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION])
                ->update([
                    'status' => Payment::STATUS_PAID,
                    'review_note' => null,
                    'paid_at' => now(),
                ]);

            $changed = $changed || $updated === 1;
        }

        // Claims are converted in full; anything still missing after that is a
        // balancing installment for previously recorded partial payments.
        $freshNetPaid = $this->netPaid($order->payments()->lockForUpdate()->get());
        $remaining = round($total - $freshNetPaid, 2);

        if ($remaining > 0) {
            $snapshot = $this->paymentSnapshot($sale);

            $order->payments()->create([
                'method' => $snapshot['method'],
                'trx_reference' => $snapshot['trx_reference'],
                'sender_number' => $snapshot['sender_number'],
                'amount' => $remaining,
                'status' => Payment::STATUS_PAID,
                'review_note' => null,
                'paid_at' => now(),
            ]);

            $changed = true;
        }

        if ($order->order_status !== $expectedStatus) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update(['order_status' => $expectedStatus]);

            $changed = true;
        }

        return $changed;
    }

    protected function mirrorApproval(Enrollment|DigitalOrder $sale): bool
    {
        if ($sale instanceof Enrollment) {
            return DB::table('enrollments')
                ->where('id', $sale->id)
                ->where('payment_status', '!=', Enrollment::PAYMENT_STATUS_PAID)
                ->update([
                    'payment_status' => Enrollment::PAYMENT_STATUS_PAID,
                    'enrollment_status' => 'in_progress',
                    'amount_paid' => (float) $sale->amount,
                    'amount_due' => 0,
                    'paid_at' => now(),
                    'rejection_reason' => null,
                    'attention_reason' => null,
                ]) === 1;
        }

        return DB::table('digital_orders')
            ->where('id', $sale->id)
            ->where('status', '!=', DigitalOrder::STATUS_APPROVED)
            ->update([
                'status' => DigitalOrder::STATUS_APPROVED,
                'rejection_reason' => null,
                'attention_reason' => null,
                'attention_email_sent_at' => null,
            ]) === 1;
    }

    protected function rejectClaimPayments(Order $order, ?string $reason): bool
    {
        $claims = $order->payments()
            ->lockForUpdate()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION])
            ->get();

        $changed = false;

        foreach ($claims as $payment) {
            $updated = DB::table('payments')
                ->where('id', $payment->id)
                ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION])
                ->update([
                    'status' => Payment::STATUS_REJECTED,
                    'review_note' => $this->textOrNull($reason),
                ]);

            $changed = $changed || $updated === 1;
        }

        return $changed;
    }

    protected function mirrorRejection(Enrollment|DigitalOrder $sale, ?string $reason): bool
    {
        if ($sale instanceof Enrollment) {
            return DB::table('enrollments')
                ->where('id', $sale->id)
                ->whereIn('payment_status', [
                    Enrollment::PAYMENT_STATUS_PENDING,
                    Enrollment::PAYMENT_STATUS_PARTIALLY_PAID,
                    Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION,
                ])
                ->update([
                    'payment_status' => Enrollment::PAYMENT_STATUS_REJECTED,
                    'enrollment_status' => 'cancelled',
                    'rejection_reason' => $this->textOrNull($reason),
                    'paid_at' => null,
                    'confirmation_email_sent_at' => null,
                ]) === 1;
        }

        return DB::table('digital_orders')
            ->where('id', $sale->id)
            ->where('status', '!=', DigitalOrder::STATUS_REJECTED)
            ->update([
                'status' => DigitalOrder::STATUS_REJECTED,
                'rejection_reason' => $this->textOrNull($reason),
                'attention_reason' => null,
                'approval_email_sent_at' => null,
            ]) === 1;
    }

    protected function flagClaimsForCorrection(Order $order, Enrollment|DigitalOrder $sale, string $action): bool
    {
        $claims = $order->payments()
            ->lockForUpdate()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION])
            ->get();

        $changed = false;

        foreach ($claims as $payment) {
            $updated = DB::table('payments')
                ->where('id', $payment->id)
                ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION])
                ->update([
                    'status' => Payment::STATUS_NEEDS_ATTENTION,
                    'review_note' => $action,
                ]);

            $changed = $changed || $updated === 1;
        }

        if ($claims->isEmpty()) {
            // Nothing is under review yet (e.g. a rejected or partially paid
            // sale): record the correction request as the outstanding claim.
            $snapshot = $this->paymentSnapshot($sale);
            $due = round((float) $order->total_amount - $this->netPaid($order->payments()->lockForUpdate()->get()), 2);

            $order->payments()->create([
                'method' => $snapshot['method'],
                'trx_reference' => $snapshot['trx_reference'],
                'sender_number' => $snapshot['sender_number'],
                'amount' => max($due, 0),
                'status' => Payment::STATUS_NEEDS_ATTENTION,
                'review_note' => $action,
                'paid_at' => null,
            ]);

            $changed = true;
        }

        return $changed;
    }

    protected function mirrorCorrection(Enrollment|DigitalOrder $sale, string $action): bool
    {
        if ($sale instanceof Enrollment) {
            return DB::table('enrollments')
                ->where('id', $sale->id)
                ->where('payment_status', '!=', Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION)
                ->whereIn('payment_status', [
                    Enrollment::PAYMENT_STATUS_PENDING,
                    Enrollment::PAYMENT_STATUS_PARTIALLY_PAID,
                    Enrollment::PAYMENT_STATUS_REJECTED,
                ])
                ->update([
                    'payment_status' => Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION,
                    'enrollment_status' => 'pending',
                    'attention_reason' => $action,
                    'confirmation_email_sent_at' => null,
                ]) === 1;
        }

        return DB::table('digital_orders')
            ->where('id', $sale->id)
            ->where('status', DigitalOrder::STATUS_PENDING)
            ->whereNull('attention_reason')
            ->update([
                'attention_reason' => $action,
                'approval_email_sent_at' => null,
            ]) === 1;
    }

    protected function mirrorResubmission(Enrollment|DigitalOrder $sale, array $data): bool
    {
        if ($sale instanceof Enrollment) {
            return DB::table('enrollments')
                ->where('id', $sale->id)
                ->where('payment_status', Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION)
                ->update([
                    'payment_status' => Enrollment::PAYMENT_STATUS_PENDING,
                    'enrollment_status' => 'pending',
                    'payment_method' => $data['payment_method'] ?? null,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'sender_number' => $data['sender_number'] ?? null,
                    'attention_reason' => null,
                    'attention_email_sent_at' => null,
                    'rejection_reason' => null,
                ]) === 1;
        }

        return DB::table('digital_orders')
            ->where('id', $sale->id)
            ->where('status', DigitalOrder::STATUS_PENDING)
            ->whereNotNull('attention_reason')
            ->update([
                'trx_id' => $data['trx_id'] ?? null,
                'attention_reason' => null,
                'attention_email_sent_at' => null,
                'rejection_reason' => null,
            ]) === 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function applyResubmissionToCanonical(Order $order, Enrollment|DigitalOrder $sale, array $data): void
    {
        $fields = match (true) {
            $sale instanceof Enrollment => [
                'method' => $this->normalizeMethod($data['payment_method'] ?? null),
                'trx_reference' => $this->textOrNull($data['transaction_id'] ?? null),
                'sender_number' => $this->textOrNull($data['sender_number'] ?? null),
            ],
            default => [
                'trx_reference' => $this->textOrNull($data['trx_id'] ?? null),
            ],
        };

        DB::table('payments')
            ->where('order_id', $order->id)
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION])
            ->update(array_merge($fields, [
                'status' => Payment::STATUS_PENDING,
                'review_note' => null,
            ]));

        DB::table('orders')
            ->where('id', $order->id)
            ->update(['attention_email_sent_at' => null]);
    }

    protected function sendOutcomeEmail(Enrollment|DigitalOrder $sale, Order $order): bool
    {
        if ($sale instanceof Enrollment) {
            $sale->loadMissing('course');

            return $this->sendMarkedEmail($sale, $order, 'outcome_email_sent_at', 'course_enrollment_confirmation', [
                'student_name' => $this->recipientName($sale) ?? 'there',
                'course_title' => $sale->course?->title ?? 'your course',
            ]);
        }

        $sale->loadMissing('product');

        return $this->sendMarkedEmail($sale, $order, 'outcome_email_sent_at', 'product_approved', [
            'student_name' => $sale->student_name ?? 'there',
            'product_title' => $sale->product?->title ?? 'your product',
            'download_link' => route('dashboard.downloads.download', $sale),
        ]);
    }

    protected function sendRejectionEmail(Enrollment|DigitalOrder $sale, Order $order, ?string $reason): bool
    {
        return $this->sendMarkedEmail($sale, $order, 'rejection_email_sent_at', 'payment_verification_failed', [
            'student_name' => $this->recipientName($sale) ?? 'there',
            'order_number' => (string) $sale->id,
            'product_title' => $this->productTitleFor($sale),
            'amount' => number_format((float) $this->saleAmount($sale), 2),
            'rejection_reason' => filled($reason) ? $reason : 'অনুগ্রহ করে সহায়তার সাথে যোগাযোগ করুন।',
            'support_url' => route('contact'),
        ]);
    }

    protected function sendAttentionEmail(Enrollment|DigitalOrder $sale, Order $order, string $action): bool
    {
        return $this->sendMarkedEmail($sale, $order, 'attention_email_sent_at', 'payment_information_needs_attention', [
            'student_name' => $this->recipientName($sale) ?? 'there',
            'order_number' => (string) $sale->id,
            'product_title' => $this->productTitleFor($sale),
            'required_action' => $action,
            'order_url' => $sale instanceof Enrollment
                ? route('dashboard.payments.enrollment.edit', $sale)
                : route('dashboard.payments.order.edit', $sale),
        ]);
    }

    /**
     * Claim the canonical Order marker atomically, dispatch through the
     * existing EmailService, and mirror the marker to the legacy record only
     * after the queue accepted the email. A failed attempt releases the
     * canonical marker so the same admin action can retry.
     *
     * @param  array<string, mixed>  $variables
     */
    protected function sendMarkedEmail(Enrollment|DigitalOrder $sale, Order $order, string $marker, string $templateKey, array $variables): bool
    {
        $to = $this->recipientEmail($sale);
        $name = $this->recipientName($sale);

        if (! filled($to)) {
            Log::warning('Payment review email skipped: no recipient.', [
                'order_id' => $order->id,
                'template_key' => $templateKey,
            ]);

            return false;
        }

        $claimed = DB::table('orders')
            ->where('id', $order->id)
            ->whereNull($marker)
            ->update([$marker => now()]) === 1;

        if (! $claimed) {
            return true; // Another request already owns this email.
        }

        try {
            $result = app(EmailService::class)->sendTemplate($templateKey, $to, $variables, $name);

            if ($result['success'] ?? false) {
                $this->mirrorMarkerToLegacy($sale, $marker);

                return true;
            }

            Log::warning('Payment review email not queued.', [
                'order_id' => $order->id,
                'template_key' => $templateKey,
                'recipient_email' => $to,
                'error' => $result['error'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Payment review email failed — marker released for retry.', [
                'order_id' => $order->id,
                'template_key' => $templateKey,
                'recipient_email' => $to,
                'error' => $exception->getMessage(),
            ]);
        }

        DB::table('orders')->where('id', $order->id)->update([$marker => null]);

        return false;
    }

    protected function setOrderStatus(Order $order, string $status): void
    {
        DB::table('orders')
            ->where('id', $order->id)
            ->where('order_status', '!=', $status)
            ->update(['order_status' => $status]);
    }

    protected function clearOutcomeMarker(Enrollment|DigitalOrder $sale): void
    {
        $order = Order::where('legacy_source', $this->sourceFor($sale))
            ->where('legacy_id', $sale->id)
            ->first();

        if ($order === null) {
            return;
        }

        DB::table('orders')
            ->where('id', $order->id)
            ->update(['outcome_email_sent_at' => null]);
    }

    protected function mirrorMarkerToLegacy(Enrollment|DigitalOrder $sale, string $marker): void
    {
        $column = $marker === 'outcome_email_sent_at'
            ? ($sale instanceof Enrollment ? 'confirmation_email_sent_at' : 'approval_email_sent_at')
            : $marker;

        $table = $sale instanceof Enrollment ? 'enrollments' : 'digital_orders';

        DB::table($table)->where('id', $sale->id)->update([$column => now()]);
    }

    protected function resolveOrder(Enrollment|DigitalOrder $sale): Order
    {
        $source = $this->sourceFor($sale);

        $order = Order::where('legacy_source', $source)
            ->where('legacy_id', $sale->id)
            ->first();

        if ($order !== null) {
            return $order;
        }

        $result = $this->materializer->materialize($sale);

        return $result['order'];
    }

    protected function sourceFor(Enrollment|DigitalOrder $sale): string
    {
        return $sale instanceof Enrollment
            ? Order::SOURCE_ENROLLMENT
            : Order::SOURCE_DIGITAL_ORDER;
    }

    protected function fresh(Enrollment|DigitalOrder $sale): Enrollment|DigitalOrder
    {
        return $sale->fresh() ?? $sale;
    }

    /**
     * @param  Collection<int, Payment>  $payments
     */
    protected function netPaid($payments): float
    {
        $paid = (float) $payments->where('status', Payment::STATUS_PAID)->sum('amount');
        $refunded = (float) $payments->where('status', Payment::STATUS_REFUNDED)->sum('amount');

        return round($paid - $refunded, 2);
    }

    /**
     * @return array{method: string|null, trx_reference: string|null, sender_number: string|null}
     */
    protected function paymentSnapshot(Enrollment|DigitalOrder $sale): array
    {
        if ($sale instanceof DigitalOrder) {
            return ['method' => null, 'trx_reference' => $this->textOrNull($sale->trx_id), 'sender_number' => null];
        }

        return [
            'method' => $this->normalizeMethod($sale->payment_method),
            'trx_reference' => $this->textOrNull($sale->transaction_id),
            'sender_number' => $this->textOrNull($sale->sender_number),
        ];
    }

    protected function normalizeMethod(?string $method): ?string
    {
        $method = $this->textOrNull($method);

        if ($method === null) {
            return null;
        }

        return in_array($method, array_keys(Payment::METHODS), true) ? $method : null;
    }

    protected function recipientEmail(Enrollment|DigitalOrder $sale): ?string
    {
        if (filled($sale->student_email)) {
            return $sale->student_email;
        }

        $sale->loadMissing('user');

        return $sale->user?->email ?? null;
    }

    protected function recipientName(Enrollment|DigitalOrder $sale): ?string
    {
        if (filled($sale->student_name)) {
            return $sale->student_name;
        }

        $sale->loadMissing('user');

        return $sale->user?->name ?? null;
    }

    protected function productTitleFor(Enrollment|DigitalOrder $sale): string
    {
        if ($sale instanceof Enrollment) {
            $sale->loadMissing('course');

            return $sale->course?->title ?? 'your course';
        }

        $sale->loadMissing('product');

        return $sale->product?->title ?? 'your product';
    }

    protected function saleAmount(Enrollment|DigitalOrder $sale): float
    {
        return (float) ($sale instanceof Enrollment ? $sale->amount : $sale->amount);
    }

    protected function textOrNull(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);
        } elseif ($value !== null) {
            $value = trim((string) $value);
        }

        return filled($value) ? $value : null;
    }

    /**
     * Admin-side “Record Payment” for course enrollments, digital orders and
     * service orders. Creates a NEW canonical Payment row (never overwrites
     * history) and mirrors the running totals onto the legacy record in the
     * same transaction.
     *
     * Rules:
     *  - amount must be positive and not exceed the outstanding due;
     *  - digital products only accept the full outstanding amount (full
     *    payment before approval/download stays the rule);
     *  - when the outstanding due reaches zero the order moves to its
     *    completed state and, for enrollments/digital orders, the legacy
     *    fulfillment flags are mirrored exactly as an approval would.
     *
     * @param  array{amount: float, method?: ?string, trx_reference?: ?string, sender_number?: ?string, review_note?: ?string}  $data
     * @return array{payment: Payment, paid_total: float, due: float, state: string}
     */
    public function recordPayment(Enrollment|DigitalOrder|ServiceOrder $sale, array $data): array
    {
        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be positive.');
        }

        $source = $this->sourceForSale($sale);

        $payment = DB::transaction(function () use ($sale, $source, $amount, $data): Payment {
            $order = $this->orderForSale($sale, $source);

            $paid = $this->sumPayments($order, Payment::STATUS_PAID);
            $refunded = $this->sumPayments($order, Payment::STATUS_REFUNDED);
            $total = (float) $order->total_amount;
            $due = round(max(0, $total - ($paid - $refunded)), 2);

            if ($amount > $due + 0.009) {
                throw new \InvalidArgumentException('Payment amount exceeds the outstanding due.');
            }

            if ($sale instanceof DigitalOrder && $amount < $due - 0.009) {
                throw new \InvalidArgumentException('Digital products require the full outstanding amount.');
            }

            // Any unverified customer claim (pending/needs-attention) is
            // superseded by the admin-recorded payment so a later approval can
            // never double-count the claim on top of the recorded money.
            DB::table('payments')
                ->where('order_id', $order->id)
                ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION])
                ->update([
                    'status' => Payment::STATUS_REJECTED,
                    'review_note' => 'Superseded by admin-recorded payment.',
                ]);

            $created = Payment::create([
                'order_id' => $order->id,
                'method' => $this->normalizeMethod($data['method'] ?? null),
                'trx_reference' => $this->textOrNull($data['trx_reference'] ?? null),
                'sender_number' => $this->textOrNull($data['sender_number'] ?? null),
                'amount' => $amount,
                'status' => Payment::STATUS_PAID,
                'review_note' => $this->textOrNull($data['review_note'] ?? null),
                'paid_at' => now(),
            ]);

            $newPaid = round($paid + $amount - $refunded, 2);
            $newDue = round(max(0, $total - $newPaid), 2);
            $settled = $newDue <= 0.009;

            $this->syncOrderLifecycle($order, $sale, $settled);
            $this->mirrorRecordedPayment($sale, $order, $newPaid, $newDue, $settled);

            return $created;
        });

        $state = $payment->order->paymentState();
        $due = $payment->order->dueTotal();

        // Newly settled enrollments/digital orders send their outcome email
        // once, through the existing canonical marker + mirror mechanism.
        if ($due <= 0.009 && ! $sale instanceof ServiceOrder) {
            $this->sendOutcomeEmail($sale->fresh() ?? $sale, $payment->order);
        }

        return [
            'payment' => $payment,
            'paid_total' => (float) $payment->order->paidTotal(),
            'due' => (float) $due,
            'state' => $state,
        ];
    }

    protected function sourceForSale(Enrollment|DigitalOrder|ServiceOrder $sale): string
    {
        if ($sale instanceof Enrollment) {
            return Order::SOURCE_ENROLLMENT;
        }

        if ($sale instanceof DigitalOrder) {
            return Order::SOURCE_DIGITAL_ORDER;
        }

        return Order::SOURCE_SERVICE_ORDER;
    }

    protected function orderForSale(Model $sale, string $source): Order
    {
        $order = Order::where('legacy_source', $source)
            ->where('legacy_id', $sale->getKey())
            ->first();

        if ($order !== null) {
            return $order;
        }

        $result = $this->materializer->materialize($sale);

        return $result['order'];
    }

    protected function sumPayments(Order $order, string $status): float
    {
        return (float) DB::table('payments')
            ->where('order_id', $order->id)
            ->where('status', $status)
            ->sum('amount');
    }

    protected function syncOrderLifecycle(Order $order, Enrollment|DigitalOrder|ServiceOrder $sale, bool $settled): void
    {
        if (! $settled) {
            return;
        }

        $status = $sale instanceof DigitalOrder
            ? Order::STATUS_COMPLETED
            : Order::STATUS_IN_PROGRESS;

        DB::table('orders')
            ->where('id', $order->id)
            ->where('order_status', '!=', $status)
            ->update(['order_status' => $status]);
    }

    protected function mirrorRecordedPayment(Enrollment|DigitalOrder|ServiceOrder $sale, Order $order, float $paid, float $due, bool $settled): void
    {
        if ($sale instanceof Enrollment) {
            DB::table('enrollments')->where('id', $sale->id)->update([
                'amount_paid' => $paid,
                'amount_due' => $due,
                'payment_status' => $settled ? Enrollment::PAYMENT_STATUS_PAID : Enrollment::PAYMENT_STATUS_PARTIALLY_PAID,
            ]);

            if ($settled) {
                DB::table('enrollments')->where('id', $sale->id)->update([
                    'enrollment_status' => 'in_progress',
                    'paid_at' => now(),
                    'rejection_reason' => null,
                    'attention_reason' => null,
                ]);
            }

            return;
        }

        if ($sale instanceof DigitalOrder) {
            DB::table('digital_orders')->where('id', $sale->id)->update([
                'status' => DigitalOrder::STATUS_APPROVED,
                'rejection_reason' => null,
                'attention_reason' => null,
            ]);

            return;
        }

        DB::table('service_orders')->where('id', $sale->id)->update([
            'amount_paid' => $paid,
            'amount_due' => $due,
            'payment_status' => $settled ? 'paid' : 'partially_paid',
        ]);

        if ($settled) {
            DB::table('service_orders')->where('id', $sale->id)->update([
                'enrollment_status' => 'in_progress',
            ]);
        }
    }
}
