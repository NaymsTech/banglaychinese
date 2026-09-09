<?php

namespace App\Services;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * Shadow-writes the unified Order → OrderItem → Payment records for a brand
 * new sale. Invoked from inside the same database transaction that creates
 * the legacy row, so both structures stay consistent or both roll back.
 *
 * Only the state a new public purchase can reach is mapped here (pending);
 * the orders:backfill command owns the historical mapping for every other
 * status and must stay in sync with what is written below.
 */
class CheckoutOrderWriter
{
    public function recordEnrollmentSale(Enrollment $enrollment): Order
    {
        $total = $this->contractAmount($enrollment->amount, $enrollment->price_paid);

        $order = $this->createOrderRecord(
            Order::SOURCE_ENROLLMENT,
            $enrollment,
            $total,
        );

        $course = $enrollment->course;

        $this->createItemRecord(
            $order,
            Course::class,
            $course?->getKey() ?? $enrollment->course_id,
            $total,
            $course?->title,
            'Course',
        );

        $order->payments()->create([
            'method' => $this->normalizeMethod($enrollment->payment_method),
            'trx_reference' => $this->textOrNull($enrollment->transaction_id),
            'sender_number' => $this->textOrNull($enrollment->sender_number),
            'amount' => $total,
            'status' => Payment::STATUS_PENDING,
            'review_note' => null,
            'paid_at' => null,
        ]);

        return $order;
    }

    /**
     * Canonical records for a zero-price (free) course enrollment.
     *
     * A free course is a legitimate zero-price sale: the Order total is zero
     * and the order is immediately in progress (the enrollment is already
     * active). No Payment row is written — there is no money movement to
     * record, and a zero-value claim would only pollute the ledger. The
     * existing canonical semantics treat a zero-total order as paid
     * (Order::paymentState), so nothing else is required.
     */
    public function recordFreeEnrollmentSale(Enrollment $enrollment): Order
    {
        $order = Order::create([
            'legacy_source' => Order::SOURCE_ENROLLMENT,
            'legacy_id' => $enrollment->getKey(),
            'user_id' => $enrollment->getAttribute('user_id'),
            'student_name' => $this->textOrNull($enrollment->getAttribute('student_name')),
            'student_email' => $this->textOrNull($enrollment->getAttribute('student_email')),
            'student_phone' => $this->textOrNull($enrollment->getAttribute('student_phone')),
            'total_amount' => 0,
            'order_status' => Order::STATUS_IN_PROGRESS,
            'admin_notes' => null,
        ]);

        $course = $enrollment->course;

        $this->createItemRecord(
            $order,
            Course::class,
            $course?->getKey() ?? $enrollment->course_id,
            0.0,
            $course?->title,
            'Course',
        );

        return $order;
    }

    /**
     * @param  array{method?: string|null, sender_number?: string|null, trx_reference?: string|null}  $paymentDetails
     */
    public function recordDigitalOrderSale(DigitalOrder $digitalOrder, array $paymentDetails = []): Order
    {
        $total = (float) $digitalOrder->amount;

        $order = $this->createOrderRecord(
            Order::SOURCE_DIGITAL_ORDER,
            $digitalOrder,
            $total,
        );

        $product = $digitalOrder->product;

        $this->createItemRecord(
            $order,
            Product::class,
            $product?->getKey() ?? $digitalOrder->product_id,
            $total,
            $product?->title,
            'Product',
        );

        // Method/sender are only recorded when the checkout collected them
        // (the unified checkout does); legacy shop submissions stay null.
        $order->payments()->create([
            'method' => $this->normalizeMethod($paymentDetails['method'] ?? null),
            'trx_reference' => $this->textOrNull($paymentDetails['trx_reference'] ?? $digitalOrder->trx_id),
            'sender_number' => $this->textOrNull($paymentDetails['sender_number'] ?? null),
            'amount' => $total,
            'status' => Payment::STATUS_PENDING,
            'review_note' => null,
            'paid_at' => null,
        ]);

        return $order;
    }

    protected function createOrderRecord(string $source, Model $legacy, float $total): Order
    {
        return Order::create([
            'legacy_source' => $source,
            'legacy_id' => $legacy->getKey(),
            'user_id' => $legacy->getAttribute('user_id'),
            'student_name' => $this->textOrNull($legacy->getAttribute('student_name')),
            'student_email' => $this->textOrNull($legacy->getAttribute('student_email')),
            'student_phone' => $this->textOrNull($legacy->getAttribute('student_phone')),
            'total_amount' => $total,
            'order_status' => Order::STATUS_PENDING,
            'admin_notes' => null,
        ]);
    }

    protected function createItemRecord(Order $order, string $purchasableType, ?int $purchasableId, float $unitPrice, ?string $title, string $fallbackNoun): void
    {
        $order->items()->create([
            'purchasable_type' => $purchasableType,
            'purchasable_id' => $purchasableId,
            'title' => filled($title) ? $title : sprintf('Deleted %s #%d', $fallbackNoun, (int) $purchasableId),
            'unit_price' => $unitPrice,
            'quantity' => 1,
        ]);
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
}
