<?php

namespace App\Services;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Materializes a legacy sale (Enrollment, DigitalOrder or ServiceOrder) into
 * the unified Order → OrderItem → Payment structure, using the same
 * state-faithful mapping that the historical backfill established.
 *
 * Idempotent on (orders.legacy_source, orders.legacy_id): when the order
 * already exists the existing record is returned and nothing is written.
 * Legacy records are never modified. Each materialization runs inside its own
 * transaction, so a failure can never leave a half-created Order/Item/Payment.
 */
class OrderMaterializer
{
    /**
     * @return array{order: Order|null, orders: int, items: int, payments: int, skipped: int, warnings: list<string>}
     */
    public function materialize(Model $legacy): array
    {
        $source = $this->sourceFor($legacy);

        $existing = Order::where('legacy_source', $source)
            ->where('legacy_id', $legacy->getKey())
            ->first();

        if ($existing !== null) {
            return $this->result($existing, 0, 0, 0, 1, []);
        }

        $counts = ['items' => 0, 'payments' => 0, 'warnings' => []];

        $order = DB::transaction(function () use ($legacy, $source, &$counts): Order {
            return $this->buildSale($legacy, $source, $counts);
        });

        return $this->result($order, 1, $counts['items'], $counts['payments'], 0, $counts['warnings']);
    }

    /**
     * @param  array{items: int, payments: int, warnings: list<string>}  $counts
     */
    protected function buildSale(Model $legacy, string $source, array &$counts): Order
    {
        return match ($source) {
            Order::SOURCE_ENROLLMENT => $this->buildEnrollmentSale($legacy, $counts),
            Order::SOURCE_DIGITAL_ORDER => $this->buildDigitalOrderSale($legacy, $counts),
            Order::SOURCE_SERVICE_ORDER => $this->buildServiceOrderSale($legacy, $counts),
        };
    }

    /**
     * @param  array{items: int, payments: int, warnings: list<string>}  $counts
     */
    protected function buildEnrollmentSale(Enrollment $enrollment, array &$counts): Order
    {
        $total = $this->contractAmount($enrollment->amount, $enrollment->price_paid);

        $order = $this->createOrderRecord(
            Order::SOURCE_ENROLLMENT,
            $enrollment,
            $total,
            $this->legacyEnrollmentStatusToOrderStatus($enrollment),
            $enrollment->admin_notes,
            $this->nonNullMarkers([
                'order_received_email_sent_at' => $enrollment->order_received_email_sent_at,
                'outcome_email_sent_at' => $enrollment->confirmation_email_sent_at,
                'rejection_email_sent_at' => $enrollment->rejection_email_sent_at,
                'attention_email_sent_at' => $enrollment->attention_email_sent_at,
                'payment_reminder_sent_at' => $enrollment->payment_reminder_sent_at,
            ]),
        );

        $course = $this->purchasable(Course::class, $enrollment->course_id);
        $this->createItemRecord($order, Course::class, $enrollment->course_id, $total, $course?->title, 'Course', $counts);

        foreach ($this->enrollmentPayments($enrollment, $total, $counts) as $attributes) {
            $order->payments()->create($attributes);
            $counts['payments']++;
        }

        return $order;
    }

    /**
     * @param  array{items: int, payments: int, warnings: list<string>}  $counts
     */
    protected function buildDigitalOrderSale(DigitalOrder $digitalOrder, array &$counts): Order
    {
        $total = $this->contractAmount($digitalOrder->amount, null);

        $order = $this->createOrderRecord(
            Order::SOURCE_DIGITAL_ORDER,
            $digitalOrder,
            $total,
            $this->digitalOrderStatusToOrderStatus($digitalOrder),
            null,
            $this->nonNullMarkers([
                'order_received_email_sent_at' => $digitalOrder->order_received_email_sent_at,
                'outcome_email_sent_at' => $digitalOrder->approval_email_sent_at,
                'rejection_email_sent_at' => $digitalOrder->rejection_email_sent_at,
                'attention_email_sent_at' => $digitalOrder->attention_email_sent_at,
            ]),
        );

        $product = $this->purchasable(Product::class, $digitalOrder->product_id);
        $this->createItemRecord($order, Product::class, $digitalOrder->product_id, $total, $product?->title, 'Product', $counts);

        foreach ($this->digitalOrderPayments($digitalOrder, $total, $counts) as $attributes) {
            $order->payments()->create($attributes);
            $counts['payments']++;
        }

        return $order;
    }

    /**
     * @param  array{items: int, payments: int, warnings: list<string>}  $counts
     */
    protected function buildServiceOrderSale(ServiceOrder $serviceOrder, array &$counts): Order
    {
        $total = $this->contractAmount($serviceOrder->amount, null);

        $order = $this->createOrderRecord(
            Order::SOURCE_SERVICE_ORDER,
            $serviceOrder,
            $total,
            $this->legacyEnrollmentStatusToOrderStatus($serviceOrder),
            $serviceOrder->admin_notes,
        );

        $service = $this->purchasable(Service::class, $serviceOrder->service_id);
        $this->createItemRecord($order, Service::class, $serviceOrder->service_id, $total, $service?->name, 'Service', $counts);

        foreach ($this->serviceOrderPayments($serviceOrder) as $attributes) {
            $order->payments()->create($attributes);
            $counts['payments']++;
        }

        return $order;
    }

    /**
     * @param  array<string, mixed>  $markers
     */
    protected function createOrderRecord(string $source, Model $legacy, float $total, string $status, ?string $adminNotes, array $markers = []): Order
    {
        $attributes = [
            'legacy_source' => $source,
            'legacy_id' => $legacy->getKey(),
            'user_id' => $legacy->getAttribute('user_id'),
            'student_name' => $this->textOrNull($legacy->getAttribute('student_name')),
            'student_email' => $this->textOrNull($legacy->getAttribute('student_email')),
            'student_phone' => $this->textOrNull($legacy->getAttribute('student_phone')),
            'total_amount' => $total,
            'order_status' => $status,
            'admin_notes' => $this->textOrNull($adminNotes),
        ];

        return Order::create(array_merge($attributes, $markers));
    }

    /**
     * The legacy tables hold a single line item per sale, so the snapshot
     * unit price is the order total and quantity is one. The purchasable
     * title is snapshotted when the record still exists; a missing
     * purchasable falls back to a deterministic label without aborting.
     *
     * @param  array{items: int, payments: int, warnings: list<string>}  $counts
     */
    protected function createItemRecord(Order $order, string $purchasableType, ?int $purchasableId, float $unitPrice, ?string $title, string $fallbackNoun, array &$counts): void
    {
        if ($purchasableId === null) {
            throw new \RuntimeException(sprintf('Cannot backfill item: %s reference is missing.', $fallbackNoun));
        }

        if (! filled($title)) {
            $counts['warnings'][] = sprintf('%s #%d: %s #%d no longer exists — item snapshotted with fallback title.', $order->legacy_source, $order->legacy_id, $fallbackNoun, $purchasableId);
        }

        $order->items()->create([
            'purchasable_type' => $purchasableType,
            'purchasable_id' => $purchasableId,
            'title' => filled($title) ? $title : sprintf('Deleted %s #%d', $fallbackNoun, $purchasableId),
            'unit_price' => $unitPrice,
            'quantity' => 1,
        ]);

        $counts['items']++;
    }

    /**
     * Payment rows for an enrollment reflect the money actually received and
     * the review state of the claim. Amounts that were never received are
     * never written as paid.
     *
     * @param  array{items: int, payments: int, warnings: list<string>}  $counts
     * @return list<array<string, mixed>>
     */
    protected function enrollmentPayments(Enrollment $enrollment, float $total, array &$counts): array
    {
        $status = $this->textOrNull($enrollment->payment_status) ?? 'pending';
        $paid = (float) $enrollment->amount_paid;
        $common = [
            'method' => $this->normalizeMethod($enrollment->payment_method),
            'trx_reference' => $this->textOrNull($enrollment->transaction_id),
            'sender_number' => $this->textOrNull($enrollment->sender_number),
        ];

        switch ($status) {
            case Enrollment::PAYMENT_STATUS_PAID:
                return [$this->paymentRow($common, 'paid', $paid, $enrollment->paid_at)];

            case Enrollment::PAYMENT_STATUS_PARTIALLY_PAID:
                if ($paid <= 0) {
                    return $this->unmappablePayment($enrollment->payment_status, $counts);
                }

                return [$this->paymentRow($common, 'paid', $paid, $enrollment->paid_at)];

            case Enrollment::PAYMENT_STATUS_PENDING:
                return [$this->paymentRow($common, 'pending', $total, null)];

            case Enrollment::PAYMENT_STATUS_REJECTED:
                return [$this->paymentRow($common, 'rejected', $total, null, $enrollment->rejection_reason)];

            case Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION:
                return [$this->paymentRow($common, 'needs_attention', $total, null, $enrollment->attention_reason)];

            case Enrollment::PAYMENT_STATUS_REFUNDED:
                if ($paid <= 0) {
                    return $this->unmappablePayment($enrollment->payment_status, $counts);
                }

                return [$this->paymentRow($common, 'refunded', $paid, null)];
        }

        return $this->unmappablePayment($enrollment->payment_status, $counts);
    }

    /**
     * Digital orders never recorded the payment channel, so method stays
     * null — it is never guessed. The order lifecycle maps approved to the
     * canonical completed order state (the file was delivered on approval).
     *
     * @param  array{items: int, payments: int, warnings: list<string>}  $counts
     * @return list<array<string, mixed>>
     */
    protected function digitalOrderPayments(DigitalOrder $digitalOrder, float $total, array &$counts): array
    {
        $common = [
            'method' => null,
            'trx_reference' => $this->textOrNull($digitalOrder->trx_id),
            'sender_number' => null,
        ];

        if ($digitalOrder->status === DigitalOrder::STATUS_PENDING && filled($digitalOrder->attention_reason)) {
            return [$this->paymentRow($common, 'needs_attention', $total, null, $digitalOrder->attention_reason)];
        }

        switch ($digitalOrder->status) {
            case DigitalOrder::STATUS_PENDING:
                return [$this->paymentRow($common, 'pending', $total, null)];

            case DigitalOrder::STATUS_APPROVED:
                return [$this->paymentRow($common, 'paid', $total, null)];

            case DigitalOrder::STATUS_REJECTED:
                return [$this->paymentRow($common, 'rejected', $total, null, $digitalOrder->rejection_reason)];
        }

        return $this->unmappablePayment($digitalOrder->status, $counts);
    }

    /**
     * Service orders recorded only how much was received (amount_paid), not
     * per-installment references or paid timestamps, so only paid/refunded
     * amounts that are actually known are written.
     *
     * @return list<array<string, mixed>>
     */
    protected function serviceOrderPayments(ServiceOrder $serviceOrder): array
    {
        $paid = (float) $serviceOrder->amount_paid;

        if ($paid <= 0) {
            return [];
        }

        $status = $serviceOrder->payment_status === 'refunded'
            ? 'refunded'
            : 'paid';

        return [[
            'method' => $this->normalizeMethod($serviceOrder->payment_method),
            'trx_reference' => null,
            'sender_number' => null,
            'amount' => $paid,
            'status' => $status,
            'review_note' => null,
            'paid_at' => null,
            'rejected_at' => null,
            'refunded_at' => null,
        ]];
    }

    /**
     * @param  array{method: string|null, trx_reference: string|null, sender_number: string|null}  $common
     * @return array<string, mixed>
     */
    protected function paymentRow(array $common, string $status, float $amount, mixed $paidAt, ?string $reviewNote = null): array
    {
        return [
            'method' => $common['method'],
            'trx_reference' => $common['trx_reference'],
            'sender_number' => $common['sender_number'],
            'amount' => $amount,
            'status' => $status,
            'review_note' => $this->textOrNull($reviewNote),
            // paid_at is only carried over when the legacy table records it
            // (enrollments). rejected_at/refunded_at are never fabricated:
            // those instants were not recorded anywhere in the legacy data.
            'paid_at' => $status === 'paid' ? $paidAt : null,
            'rejected_at' => null,
            'refunded_at' => null,
        ];
    }

    /**
     * @param  array{items: int, payments: int, warnings: list<string>}  $counts
     * @return list<array<string, mixed>>
     */
    protected function unmappablePayment(?string $legacyStatus, array &$counts): array
    {
        $counts['warnings'][] = sprintf('Payment skipped: unrecognized legacy payment_status "%s".', (string) $legacyStatus);

        return [];
    }

    /**
     * @return array{order: Order|null, orders: int, items: int, payments: int, skipped: int, warnings: list<string>}
     */
    protected function result(?Order $order, int $orders, int $items, int $payments, int $skipped, array $warnings): array
    {
        return [
            'order' => $order,
            'orders' => $orders,
            'items' => $items,
            'payments' => $payments,
            'skipped' => $skipped,
            'warnings' => $warnings,
        ];
    }

    protected function sourceFor(Model $legacy): string
    {
        return match (true) {
            $legacy instanceof Enrollment => Order::SOURCE_ENROLLMENT,
            $legacy instanceof DigitalOrder => Order::SOURCE_DIGITAL_ORDER,
            $legacy instanceof ServiceOrder => Order::SOURCE_SERVICE_ORDER,
            default => throw new \InvalidArgumentException('Unsupported legacy sale model '.$legacy::class.'.'),
        };
    }

    protected function contractAmount(mixed $amount, mixed $legacyPrice): float
    {
        $amount = (float) $amount;
        $legacyPrice = (float) $legacyPrice;

        if ($amount > 0) {
            return $amount;
        }

        return $legacyPrice > 0 ? $legacyPrice : $amount;
    }

    protected function legacyEnrollmentStatusToOrderStatus(Model $model): string
    {
        $status = $this->textOrNull($model->getAttribute('enrollment_status'));

        if ($status !== null && in_array($status, array_keys(Order::STATUSES), true)) {
            return $status;
        }

        // Pre-normalization rows only carry the legacy status column.
        $status = $this->textOrNull($model->getAttribute('status'));

        return match ($status) {
            'active', 'paid' => Order::STATUS_IN_PROGRESS,
            'completed' => Order::STATUS_COMPLETED,
            'cancelled' => Order::STATUS_CANCELLED,
            default => Order::STATUS_PENDING,
        };
    }

    protected function digitalOrderStatusToOrderStatus(DigitalOrder $digitalOrder): string
    {
        if ($digitalOrder->status === DigitalOrder::STATUS_APPROVED) {
            return Order::STATUS_COMPLETED;
        }

        if ($digitalOrder->status === DigitalOrder::STATUS_REJECTED) {
            return Order::STATUS_CANCELLED;
        }

        return Order::STATUS_PENDING;
    }

    protected function normalizeMethod(?string $method): ?string
    {
        $method = $this->textOrNull($method);

        if ($method === null) {
            return null;
        }

        return in_array($method, array_keys(Payment::METHODS), true) ? $method : null;
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
     * @param  array<string, mixed>  $markers
     * @return array<string, mixed>
     */
    protected function nonNullMarkers(array $markers): array
    {
        return array_filter($markers, fn ($value): bool => $value !== null);
    }

    protected function purchasable(string $class, ?int $id): ?Model
    {
        if ($id === null) {
            return null;
        }

        return $class::find($id);
    }
}
