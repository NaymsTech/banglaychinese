<?php

namespace Tests\Feature;

use App\Filament\Widgets\PendingPaymentsOverview;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin dashboard payment analytics over the canonical Order → Payment ledger.
 */
class AdminDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejected_payment_never_counts_as_collection_or_due(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $order = $this->order('enrollment', 1000);
        $order->payments()->create(['amount' => 1000, 'status' => Payment::STATUS_REJECTED, 'method' => 'bkash']);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(0, $due);
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 0.00', $collected);
    }

    public function test_needs_attention_payment_never_counts_as_collection(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $order = $this->order('enrollment', 1000);
        $order->payments()->create(['amount' => 1000, 'status' => Payment::STATUS_NEEDS_ATTENTION, 'method' => 'bkash']);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame('৳ 0.00', $collected);
        $this->assertSame(0, $paid);
    }

    public function test_pending_customer_claim_never_counts_as_collection(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $order = $this->order('digital_order', 299);
        $order->payments()->create(['amount' => 299, 'status' => Payment::STATUS_PENDING, 'method' => 'bkash']);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(1, $due); // awaiting review
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 0.00', $collected);
    }

    public function test_payment_recorded_today_for_an_older_order_counts_in_this_months_collection(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        // Order was created in the previous month…
        $order = Order::create([
            'legacy_source' => 'enrollment',
            'legacy_id' => random_int(100000, 999999),
            'total_amount' => 10000,
            'order_status' => Order::STATUS_PENDING,
            'created_at' => '2026-05-30 10:00:00',
            'updated_at' => '2026-05-30 10:00:00',
        ]);

        // …but the money was actually received this month.
        $order->payments()->create([
            'amount' => 10000,
            'status' => Payment::STATUS_PAID,
            'method' => 'bkash',
            'paid_at' => '2026-06-09 09:00:00',
        ]);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(0, $due);
        $this->assertSame(1, $paid);
        $this->assertSame('৳ 10,000.00', $collected);
    }

    public function test_digital_and_service_orders_contribute_to_due_and_collection(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        $digital = $this->order('digital_order', 2000);
        $digital->payments()->create(['amount' => 2000, 'status' => Payment::STATUS_PENDING, 'method' => 'bkash']);

        $service = $this->order('service_order', 3000);
        $service->payments()->create(['amount' => 1000, 'status' => Payment::STATUS_PAID, 'method' => 'bank', 'paid_at' => '2026-06-05 09:00:00']);

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(2, $due);
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 1,000.00', $collected);
    }

    public function test_multiple_partial_orders_are_aggregated_without_double_counting(): void
    {
        $this->travelTo('2026-06-15 12:00:00');

        foreach ([4000, 6000] as $partial) {
            $order = $this->order('enrollment', 10000);
            $order->payments()->create(['amount' => $partial, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => '2026-06-03 09:00:00']);
        }

        [$due, $paid, $collected] = $this->stats();

        $this->assertSame(2, $due);
        $this->assertSame(0, $paid);
        $this->assertSame('৳ 10,000.00', $collected);
    }

    public function test_dashboard_keeps_three_cards_with_the_existing_shapes(): void
    {
        $widget = new PendingPaymentsOverview;
        $method = (new \ReflectionClass($widget))->getMethod('getStats');
        $method->setAccessible(true);

        $stats = $method->invoke($widget);

        // The three existing cards (due count, paid count, collected money)
        // are preserved; the money card keeps the ৳ format.
        $this->assertCount(3, $stats);
        $this->assertIsInt($stats[0]->getValue());
        $this->assertIsInt($stats[1]->getValue());
        $this->assertMatchesRegularExpression('/^৳ [\d,]+\.\d{2}$/', (string) $stats[2]->getValue());
    }

    protected function order(string $source, int $total): Order
    {
        return Order::create([
            'legacy_source' => $source,
            'legacy_id' => random_int(100000, 999999),
            'total_amount' => $total,
            'order_status' => Order::STATUS_PENDING,
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
