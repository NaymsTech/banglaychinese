<?php

namespace Tests\Feature;

use App\Filament\Widgets\PendingPaymentsOverview;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PendingPaymentsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_fully_paid_course_order_is_reported_paid_this_month(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $this->paidCourseOrder(total: 1000, paidAt: '2026-06-10 09:00:00');
        $this->paidCourseOrder(total: 500, paidAt: '2026-05-20 09:00:00'); // outside the month window

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(0, $due);
        $this->assertSame(1, $paid);
        $this->assertSame('৳ 1,000.00', $collected);
    }

    public function test_pending_course_order_counts_as_due(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $order = $this->courseOrder(1000);
        $order->payments()->create([
            'amount' => 1000,
            'status' => Payment::STATUS_PENDING,
            'method' => 'bkash',
        ]);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(1, $due);
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 0.00', $collected);
    }

    public function test_partially_paid_course_order_counts_as_due(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $order = $this->courseOrder(5000);
        $order->payments()->create([
            'amount' => 2000,
            'status' => Payment::STATUS_PAID,
            'method' => 'bkash',
            'paid_at' => '2026-06-05 10:00:00',
        ]);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(1, $due);
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 2,000.00', $collected);
    }

    public function test_refunded_payment_never_increases_net_received_or_paid_totals(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $order = $this->courseOrder(1000);
        $order->payments()->create(['amount' => 1000, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => '2026-06-10 09:00:00']);
        $order->payments()->create(['amount' => 1000, 'status' => Payment::STATUS_REFUNDED, 'method' => 'bkash']);

        [$due, $paid, $collected] = $this->stats();

        // Fully refunded: not outstanding, not "paid this month" (not fully
        // settled), and the refunded row never adds to collected money.
        $this->assertSame(0, $due);
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 1,000.00', $collected);
    }

    public function test_partial_refund_only_reduces_the_net_received(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $order = $this->courseOrder(500);
        $order->payments()->create(['amount' => 300, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => '2026-06-10 09:00:00']);
        $order->payments()->create(['amount' => 100, 'status' => Payment::STATUS_REFUNDED, 'method' => 'bkash']);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(1, $due); // net 200 of 500 → still outstanding
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 300.00', $collected); // refunded 100 never added
    }

    public function test_totals_never_depend_on_legacy_enrollment_money_columns(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        // Legacy rows with inflated amount_paid but no canonical order must
        // have no effect: the widget reads Order → Payment only.
        $this->legacyEnrollment(amountPaid: 9999);
        $this->legacyEnrollment(amountPaid: 5000);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(0, $due);
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 0.00', $collected);
    }

    public function test_scope_includes_course_digital_and_service_orders(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $courseOrder = $this->courseOrder(1000);
        $courseOrder->payments()->create(['amount' => 1000, 'status' => Payment::STATUS_PENDING, 'method' => 'bkash']);

        $this->paidCanonicalOrder('digital_order', 5000, '2026-06-10 09:00:00');
        $this->paidCanonicalOrder('service_order', 3000, '2026-06-11 09:00:00');

        [$due, $paid, $collected] = $this->stats();

        // Course order awaits review; the fully paid digital and service
        // orders count as paid this month and their money is collected.
        $this->assertSame(1, $due);
        $this->assertSame(2, $paid);
        $this->assertSame('৳ 8,000.00', $collected);
    }

    public function test_multiple_payments_on_one_order_never_double_count_totals(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $order = $this->courseOrder(1000);
        $order->payments()->create(['amount' => 600, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => '2026-06-02 09:00:00']);
        $order->payments()->create(['amount' => 400, 'status' => Payment::STATUS_PAID, 'method' => 'nagad', 'paid_at' => '2026-06-10 09:00:00']);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(0, $due);
        $this->assertSame(1, $paid); // the order counts once, not twice
        $this->assertSame('৳ 1,000.00', $collected);
    }

    protected function courseOrder(int $total): Order
    {
        return Order::create([
            'legacy_source' => Order::SOURCE_ENROLLMENT,
            'legacy_id' => random_int(100000, 999999),
            'total_amount' => $total,
            'order_status' => Order::STATUS_PENDING,
        ]);
    }

    protected function paidCourseOrder(int $total, string $paidAt): Order
    {
        $order = $this->courseOrder($total);
        $order->payments()->create([
            'amount' => $total,
            'status' => Payment::STATUS_PAID,
            'method' => 'bkash',
            'paid_at' => $paidAt,
        ]);

        return $order;
    }

    protected function paidCanonicalOrder(string $source, int $amount, string $paidAt): Order
    {
        $order = Order::create([
            'legacy_source' => $source,
            'legacy_id' => random_int(100000, 999999),
            'total_amount' => $amount,
            'order_status' => Order::STATUS_COMPLETED,
        ]);
        $order->payments()->create([
            'amount' => $amount,
            'status' => Payment::STATUS_PAID,
            'method' => 'bkash',
            'paid_at' => $paidAt,
        ]);

        return $order;
    }

    protected function legacyEnrollment(int $amountPaid): Enrollment
    {
        return Enrollment::create([
            'course_id' => $this->course()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 9500,
            'amount_paid' => $amountPaid,
            'amount_due' => 9500 - $amountPaid,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);
    }

    protected function course(): Course
    {
        return Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => 9500,
            'is_published' => true,
        ]);
    }

    /**
     * @return array{0: int, 1: int, 2: string}
     */
    protected function stats(): array
    {
        $widget = new PendingPaymentsOverview;
        $method = (new \ReflectionClass($widget))->getMethod('getStats');
        $method->setAccessible(true);

        $stats = $method->invoke($widget);

        return [
            (int) $stats[0]->getValue(),
            (int) $stats[1]->getValue(),
            (string) $stats[2]->getValue(),
        ];
    }
}
