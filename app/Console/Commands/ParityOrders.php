<?php

namespace App\Console\Commands;

use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ServiceOrder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Read-only legacy ↔ canonical parity check for orders/order_items/payments.
 *
 * Detects drift between the legacy sale tables and the unified structure so a
 * future read cutover can be proven safe. Never writes to the database.
 *
 * Outcomes:
 *   OK      — consistent
 *   WARNING — legitimate historical ambiguity (refunded rows, legacy-only
 *             creation markers, pre-Phase-5A completed rows, …)
 *   ERROR   — actual drift that must be fixed before a read cutover
 *
 * Exit code is non-zero when at least one ERROR is detected.
 */
#[Signature('orders:parity')]
#[Description('Read-only legacy/canonical parity check for orders, order items and payments')]
class ParityOrders extends Command
{
    private const DETAIL_LIMIT = 25;

    protected int $checked = 0;

    protected int $ok = 0;

    protected int $warnings = 0;

    protected int $errors = 0;

    /** @var list<string> */
    protected array $warningDetails = [];

    /** @var list<string> */
    protected array $errorDetails = [];

    public function handle(): int
    {
        $this->info('orders:parity — read-only legacy/canonical consistency check');
        $this->newLine();

        $this->parityEnrollments();
        $this->parityDigitalOrders();
        $this->integrityChecks();

        $this->printSummary();

        return $this->errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function parityEnrollments(): void
    {
        Enrollment::query()->orderBy('id')->chunkById(200, function ($rows): void {
            foreach ($rows as $enrollment) {
                $order = Order::where('legacy_source', Order::SOURCE_ENROLLMENT)
                    ->where('legacy_id', $enrollment->getKey())
                    ->first();

                if ($order === null) {
                    $this->recordError(sprintf('enrollment #%d: missing canonical order', $enrollment->getKey()));

                    continue;
                }

                $this->checkEnrollmentMoney($enrollment, $order);
                $this->checkEnrollmentState($enrollment, $order);
                $this->checkEnrollmentMarkers($enrollment, $order);
            }
        });
    }

    protected function checkEnrollmentMoney(Enrollment $enrollment, Order $order): void
    {
        $total = $this->contractAmount($enrollment->amount, $enrollment->price_paid);

        if (! $this->same((float) $order->total_amount, $total)) {
            $this->recordError(sprintf('enrollment #%d: total amount mismatch (canonical %s, legacy %s)', $enrollment->getKey(), $order->total_amount, $total));

            return;
        }

        $payments = $order->payments()->get();
        $netPaid = $this->netPaid($payments);
        $legacyPaid = (float) $enrollment->amount_paid;

        if ($enrollment->payment_status === 'refunded') {
            // Legacy refunded rows keep amount_paid while the canonical ledger
            // represents the return of funds — intentionally fuzzy history.
            if (! $this->same($netPaid, 0.0) || ! $this->same($legacyPaid, 0.0)) {
                $this->recordWarning(sprintf('enrollment #%d: refunded row with paid/refunded money present — historical semantics, verify manually', $enrollment->getKey()));
            }
        } elseif (! $this->same($netPaid, $legacyPaid)) {
            $this->recordError(sprintf('enrollment #%d: net paid mismatch (canonical %s, legacy amount_paid %s)', $enrollment->getKey(), number_format($netPaid, 2), number_format($legacyPaid, 2)));
        }
    }

    protected function checkEnrollmentState(Enrollment $enrollment, Order $order): void
    {
        $expectedStatus = $this->legacyEnrollmentStatusToOrderStatus($enrollment);

        if ($expectedStatus === Order::STATUS_COMPLETED && $order->order_status === Order::STATUS_IN_PROGRESS) {
            $this->recordWarning(sprintf('enrollment #%d: completed in legacy but canonical order still in_progress (pre-mirror completion rows)', $enrollment->getKey()));
        } elseif ($order->order_status !== $expectedStatus) {
            $this->recordError(sprintf('enrollment #%d: lifecycle mismatch (canonical order_status %s, expected %s)', $enrollment->getKey(), $order->order_status, $expectedStatus));
        }

        $payments = $order->payments()->get();
        $netPaid = $this->netPaid($payments);
        $claims = $payments->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION]);

        switch ($enrollment->payment_status) {
            case Enrollment::PAYMENT_STATUS_PAID:
                if (! $this->same((float) $order->total_amount, $netPaid) && $netPaid < (float) $order->total_amount) {
                    $this->recordError(sprintf('enrollment #%d: legacy paid but canonical money is not fully received', $enrollment->getKey()));
                }
                break;

            case Enrollment::PAYMENT_STATUS_PARTIALLY_PAID:
                if (! $this->same($netPaid, (float) $enrollment->amount_paid)) {
                    $this->recordError(sprintf('enrollment #%d: partially paid money mismatch', $enrollment->getKey()));
                }
                break;

            case Enrollment::PAYMENT_STATUS_REJECTED:
                if ($order->order_status !== Order::STATUS_CANCELLED) {
                    $this->recordError(sprintf('enrollment #%d: rejected in legacy but canonical order not cancelled', $enrollment->getKey()));
                }
                break;

            case Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION:
                if ($claims->isEmpty()) {
                    $this->recordWarning(sprintf('enrollment #%d: needs_attention in legacy but no canonical claim row flagged', $enrollment->getKey()));
                }
                break;

            case Enrollment::PAYMENT_STATUS_PENDING:
                if ($claims->isEmpty() && $netPaid <= 0 && (float) $enrollment->amount > 0) {
                    $this->recordWarning(sprintf('enrollment #%d: pending in legacy but no canonical pending claim row', $enrollment->getKey()));
                }
                break;

            default:
                $this->recordWarning(sprintf('enrollment #%d: unrecognized legacy payment_status "%s"', $enrollment->getKey(), (string) $enrollment->payment_status));
                break;
        }
    }

    protected function checkEnrollmentMarkers(Enrollment $enrollment, Order $order): void
    {
        $this->markerMirror('enrollment', $enrollment->getKey(), $order->outcome_email_sent_at, $enrollment->confirmation_email_sent_at, 'outcome');
        $this->markerMirror('enrollment', $enrollment->getKey(), $order->rejection_email_sent_at, $enrollment->rejection_email_sent_at, 'rejection');
        $this->markerMirror('enrollment', $enrollment->getKey(), $order->attention_email_sent_at, $enrollment->attention_email_sent_at, 'attention');

        // Creation + reminder markers are still claimed on the legacy table
        // only (deliberate Phase-4 boundary) — canonical stays null.
        if ($enrollment->order_received_email_sent_at !== null && $order->order_received_email_sent_at === null) {
            $this->recordWarning(sprintf('enrollment #%d: order_received marker is legacy-only (known boundary)', $enrollment->getKey()));
        }

        if ($enrollment->payment_reminder_sent_at !== null && $order->payment_reminder_sent_at === null) {
            $this->recordWarning(sprintf('enrollment #%d: payment_reminder marker is legacy-only (known boundary)', $enrollment->getKey()));
        }
    }

    protected function parityDigitalOrders(): void
    {
        DigitalOrder::query()->orderBy('id')->chunkById(200, function ($rows): void {
            foreach ($rows as $digitalOrder) {
                $order = Order::where('legacy_source', Order::SOURCE_DIGITAL_ORDER)
                    ->where('legacy_id', $digitalOrder->getKey())
                    ->first();

                if ($order === null) {
                    $this->recordError(sprintf('digital_order #%d: missing canonical order', $digitalOrder->getKey()));

                    continue;
                }

                if (! $this->same((float) $order->total_amount, (float) $digitalOrder->amount)) {
                    $this->recordError(sprintf('digital_order #%d: total amount mismatch (canonical %s, legacy %s)', $digitalOrder->getKey(), $order->total_amount, $digitalOrder->amount));

                    continue;
                }

                $payments = $order->payments()->get();
                $netPaid = $this->netPaid($payments);
                $claims = $payments->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_NEEDS_ATTENTION]);

                switch ($digitalOrder->status) {
                    case DigitalOrder::STATUS_APPROVED:
                        if (! $this->same($netPaid, (float) $order->total_amount)) {
                            $this->recordError(sprintf('digital_order #%d: approved in legacy but canonical money not fully received', $digitalOrder->getKey()));
                        }
                        if ($order->order_status !== Order::STATUS_COMPLETED) {
                            $this->recordError(sprintf('digital_order #%d: approved in legacy but canonical order not completed', $digitalOrder->getKey()));
                        }
                        break;

                    case DigitalOrder::STATUS_REJECTED:
                        if ($order->order_status !== Order::STATUS_CANCELLED) {
                            $this->recordError(sprintf('digital_order #%d: rejected in legacy but canonical order not cancelled', $digitalOrder->getKey()));
                        }
                        break;

                    case DigitalOrder::STATUS_PENDING:
                        if ($order->order_status !== Order::STATUS_PENDING) {
                            $this->recordError(sprintf('digital_order #%d: pending in legacy but canonical order_status is %s', $digitalOrder->getKey(), $order->order_status));
                        }
                        if ($claims->isEmpty() && $netPaid <= 0) {
                            $this->recordWarning(sprintf('digital_order #%d: pending in legacy but no canonical claim row', $digitalOrder->getKey()));
                        }
                        break;

                    default:
                        $this->recordWarning(sprintf('digital_order #%d: unrecognized legacy status "%s"', $digitalOrder->getKey(), (string) $digitalOrder->status));
                        break;
                }

                $this->checkDigitalOrderReference($digitalOrder, $payments);
                $this->markerMirror('digital_order', $digitalOrder->getKey(), $order->outcome_email_sent_at, $digitalOrder->approval_email_sent_at, 'approval');
                $this->markerMirror('digital_order', $digitalOrder->getKey(), $order->rejection_email_sent_at, $digitalOrder->rejection_email_sent_at, 'rejection');
                $this->markerMirror('digital_order', $digitalOrder->getKey(), $order->attention_email_sent_at, $digitalOrder->attention_email_sent_at, 'attention');

                if ($digitalOrder->order_received_email_sent_at !== null && $order->order_received_email_sent_at === null) {
                    $this->recordWarning(sprintf('digital_order #%d: order_received marker is legacy-only (known boundary)', $digitalOrder->getKey()));
                }
            }
        });
    }

    protected function checkDigitalOrderReference(DigitalOrder $digitalOrder, $payments): void
    {
        $legacyReference = $this->clean($digitalOrder->trx_id);
        $canonicalReference = $this->clean($payments->first()?->trx_reference);

        if ($legacyReference !== $canonicalReference) {
            $this->recordError(sprintf('digital_order #%d: transaction reference mismatch (canonical %s, legacy %s)', $digitalOrder->getKey(), $canonicalReference ?? 'null', $legacyReference ?? 'null'));
        }
    }

    protected function integrityChecks(): void
    {
        $duplicates = Order::select('legacy_source', 'legacy_id')
            ->whereNotNull('legacy_source')
            ->whereNotNull('legacy_id')
            ->groupBy('legacy_source', 'legacy_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $this->recordError(sprintf('duplicate canonical orders for legacy_source=%s legacy_id=%s', $duplicate->legacy_source, $duplicate->legacy_id));
        }

        Order::query()->orderBy('id')->chunkById(200, function ($orders): void {
            foreach ($orders as $order) {
                if (! $order->items()->exists()) {
                    $this->recordError(sprintf('order #%d: canonical order has no order items', $order->getKey()));
                }

                $legacyExists = match ($order->legacy_source) {
                    Order::SOURCE_ENROLLMENT => Enrollment::whereKey($order->legacy_id)->exists(),
                    Order::SOURCE_DIGITAL_ORDER => DigitalOrder::whereKey($order->legacy_id)->exists(),
                    Order::SOURCE_SERVICE_ORDER => ServiceOrder::whereKey($order->legacy_id)->exists(),
                    default => false,
                };

                if (! $legacyExists) {
                    $this->recordError(sprintf('order #%d: canonical order has no matching legacy row (%s #%d)', $order->getKey(), (string) $order->legacy_source, (int) $order->legacy_id));
                }

                if (! in_array($order->order_status, array_keys(Order::STATUSES), true)) {
                    $this->recordError(sprintf('order #%d: unexpected canonical order_status "%s"', $order->getKey(), $order->order_status));
                }

                $payments = $order->payments()->get();

                if ($this->netPaid($payments) < 0) {
                    // A net below zero is expected for historical rows mapped
                    // from the legacy "refunded" state, which carries only a
                    // refunded row. Anything else is genuine drift.
                    if ($payments->isNotEmpty() && $payments->every(fn (Payment $payment): bool => $payment->status === Payment::STATUS_REFUNDED)) {
                        $this->recordWarning(sprintf('order #%d: negative net balance from refunded-only rows (historical refunded mapping)', $order->getKey()));
                    } else {
                        $this->recordError(sprintf('order #%d: negative net payment balance', $order->getKey()));
                    }
                }

                foreach ($payments as $payment) {
                    if (! in_array($payment->status, array_keys(Payment::STATUSES), true)) {
                        $this->recordError(sprintf('payment #%d: unexpected status "%s"', $payment->getKey(), $payment->status));
                    }

                    if ($payment->method !== null && ! in_array($payment->method, array_keys(Payment::METHODS), true)) {
                        $this->recordError(sprintf('payment #%d: unexpected method "%s"', $payment->getKey(), $payment->method));
                    }

                    if ((float) $payment->amount < 0) {
                        $this->recordError(sprintf('payment #%d: negative amount', $payment->getKey()));
                    }
                }
            }
        });

        $orphanItems = OrderItem::whereDoesntHave('order')->count();

        if ($orphanItems > 0) {
            $this->recordError(sprintf('%d orphaned order item(s) without an order', $orphanItems));
        }

        $orphanPayments = Payment::whereDoesntHave('order')->count();

        if ($orphanPayments > 0) {
            $this->recordError(sprintf('%d orphaned payment(s) without an order', $orphanPayments));
        }
    }

    protected function markerMirror(string $domain, int $legacyId, mixed $canonical, mixed $legacy, string $label): void
    {
        $canonicalSet = $canonical !== null;
        $legacySet = $legacy !== null;

        if ($canonicalSet !== $legacySet) {
            $kind = $canonicalSet ? 'canonical-only' : 'legacy-only';
            $this->recordError(sprintf('%s #%d: %s email marker is %s', $domain, $legacyId, $label, $kind));
        } else {
            $this->recordOk();
        }
    }

    protected function netPaid($payments): float
    {
        $paid = (float) $payments->where('status', Payment::STATUS_PAID)->sum('amount');
        $refunded = (float) $payments->where('status', Payment::STATUS_REFUNDED)->sum('amount');

        return round($paid - $refunded, 2);
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

    protected function legacyEnrollmentStatusToOrderStatus(Enrollment $enrollment): string
    {
        $status = $this->clean($enrollment->enrollment_status);

        if ($status !== null && in_array($status, array_keys(Order::STATUSES), true)) {
            return $status;
        }

        $status = $this->clean($enrollment->status);

        return match ($status) {
            'active', 'paid' => Order::STATUS_IN_PROGRESS,
            'completed' => Order::STATUS_COMPLETED,
            'cancelled' => Order::STATUS_CANCELLED,
            default => Order::STATUS_PENDING,
        };
    }

    protected function same(float $left, float $right): bool
    {
        return abs($left - $right) < 0.009;
    }

    protected function clean(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);
        } elseif ($value !== null) {
            $value = trim((string) $value);
        }

        return filled($value) ? $value : null;
    }

    protected function recordOk(): void
    {
        $this->checked++;
        $this->ok++;
    }

    protected function recordWarning(string $message): void
    {
        $this->checked++;
        $this->warnings++;

        if (count($this->warningDetails) < self::DETAIL_LIMIT) {
            $this->warningDetails[] = $message;
        }
    }

    protected function recordError(string $message): void
    {
        $this->checked++;
        $this->errors++;

        if (count($this->errorDetails) < self::DETAIL_LIMIT) {
            $this->errorDetails[] = $message;
        }
    }

    protected function printSummary(): void
    {
        $this->newLine();
        $this->info('Parity check complete.');

        $this->table(['Metric', 'Count'], [
            ['Checks', $this->checked],
            ['OK', $this->ok],
            ['Warnings', $this->warnings],
            ['Errors', $this->errors],
        ]);

        foreach ($this->warningDetails as $warning) {
            $this->warn('WARNING: '.$warning);
        }

        foreach ($this->errorDetails as $error) {
            $this->error('ERROR: '.$error);
        }

        if ($this->errors > 0) {
            $this->newLine();
            $this->error(sprintf('%d error(s) detected — resolve before any read cutover.', $this->errors));
        }
    }
}
