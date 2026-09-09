<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrdersBackfillCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_enrollment_backfills_order_item_and_payment_with_all_details(): void
    {
        $user = User::factory()->create();
        $course = $this->course();
        $enrollment = $this->enrollment([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 5000,
            'amount_paid' => 5000,
            'amount_due' => 0,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
            'admin_notes' => 'Verified over the phone.',
        ]);

        $this->artisan('orders:backfill')->assertExitCode(0);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'legacy_source' => 'enrollment',
            'legacy_id' => $enrollment->id,
            'user_id' => $user->id,
            'student_name' => 'Nusrat Rahman',
            'student_email' => 'nusrat@example.com',
            'student_phone' => '01712345678',
            'total_amount' => 5000,
            'currency' => 'BDT',
            'order_status' => 'in_progress',
            'admin_notes' => 'Verified over the phone.',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => Order::where('legacy_source', 'enrollment')->first()->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'title' => $course->title,
            'unit_price' => 5000,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('payments', [
            'method' => 'bkash',
            'trx_reference' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
            'amount' => 5000,
            'status' => 'paid',
            'review_note' => null,
        ]);
    }

    public function test_approved_digital_order_backfills_but_never_guesses_the_payment_method(): void
    {
        $product = $this->product();
        $digitalOrder = DigitalOrder::create([
            'product_id' => $product->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01812345678',
            'trx_id' => 'TRX987654321',
            'amount' => 800,
            'status' => 'approved',
        ]);

        $this->artisan('orders:backfill')->assertExitCode(0);

        $order = Order::where('legacy_source', 'digital_order')->first();

        $this->assertNotNull($order);
        $this->assertSame('completed', $order->order_status);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'purchasable_type' => Product::class,
            'purchasable_id' => $product->id,
            'title' => $product->title,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => null,
            'trx_reference' => 'TRX987654321',
            'sender_number' => null,
            'amount' => 800,
            'status' => 'paid',
        ]);
    }

    public function test_pending_digital_order_with_attention_flag_maps_to_needs_attention_payment(): void
    {
        $digitalOrder = DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01812345678',
            'trx_id' => 'TRX555',
            'amount' => 800,
            'status' => 'pending',
            'attention_reason' => 'Send the correct transaction screenshot.',
        ]);

        $this->artisan('orders:backfill')->assertExitCode(0);

        $this->assertDatabaseHas('orders', [
            'legacy_source' => 'digital_order',
            'legacy_id' => $digitalOrder->id,
            'order_status' => 'pending',
        ]);
        $this->assertDatabaseHas('payments', [
            'status' => 'needs_attention',
            'review_note' => 'Send the correct transaction screenshot.',
        ]);
    }

    public function test_service_orders_backfill_known_cash_payment_and_skip_pending_without_money(): void
    {
        $service = $this->service();

        $paidOrder = ServiceOrder::create([
            'service_id' => $service->id,
            'student_name' => 'Karim Mia',
            'student_email' => 'karim@example.com',
            'student_phone' => '01912345678',
            'amount' => 2000,
            'amount_paid' => 2000,
            'amount_due' => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
        ]);

        $pendingOrder = ServiceOrder::create([
            'service_id' => $service->id,
            'student_name' => 'Karim Mia',
            'student_email' => 'karim@example.com',
            'student_phone' => '01912345678',
            'amount' => 1500,
            'amount_paid' => 0,
            'amount_due' => 1500,
            'payment_method' => 'bkash',
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);

        $this->artisan('orders:backfill')->assertExitCode(0);

        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseHas('orders', [
            'legacy_source' => 'service_order',
            'legacy_id' => $paidOrder->id,
            'order_status' => 'in_progress',
        ]);
        $this->assertDatabaseHas('orders', [
            'legacy_source' => 'service_order',
            'legacy_id' => $pendingOrder->id,
            'order_status' => 'pending',
        ]);

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'method' => 'cash',
            'amount' => 2000,
            'status' => 'paid',
        ]);
    }

    public function test_partially_paid_enrollment_backfills_only_the_known_received_amount(): void
    {
        $enrollment = $this->enrollment([
            'amount' => 5000,
            'amount_paid' => 2000,
            'amount_due' => 3000,
            'payment_status' => 'partially_paid',
            'enrollment_status' => 'in_progress',
            'payment_method' => 'nagad',
            'transaction_id' => 'NAGAD111222',
            'sender_number' => '01712345678',
        ]);

        $this->artisan('orders:backfill')->assertExitCode(0);

        $order = Order::where('legacy_source', 'enrollment')->first();

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'nagad',
            'trx_reference' => 'NAGAD111222',
            'amount' => 2000,
            'status' => 'paid',
        ]);

        $this->assertSame(2000.0, $order->paidTotal());
        $this->assertSame(3000.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PARTIALLY_PAID, $order->paymentState());
    }

    public function test_rejected_and_needs_attention_enrollments_preserve_reasons(): void
    {
        $rejected = $this->enrollment([
            'payment_status' => 'rejected',
            'enrollment_status' => 'cancelled',
            'payment_method' => 'bkash',
            'transaction_id' => 'REJ12345678',
            'sender_number' => '01712345678',
            'rejection_reason' => 'The transaction id does not exist.',
        ]);

        $needsAttention = $this->enrollment([
            'payment_status' => 'needs_attention',
            'enrollment_status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => 'ATT12345678',
            'sender_number' => '01712345678',
            'attention_reason' => 'Send the correct transaction screenshot.',
        ]);

        $this->artisan('orders:backfill')->assertExitCode(0);

        $this->assertDatabaseHas('orders', [
            'legacy_source' => 'enrollment',
            'legacy_id' => $rejected->id,
            'order_status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('payments', [
            'status' => 'rejected',
            'review_note' => 'The transaction id does not exist.',
        ]);

        $this->assertDatabaseHas('orders', [
            'legacy_source' => 'enrollment',
            'legacy_id' => $needsAttention->id,
            'order_status' => 'pending',
        ]);
        $this->assertDatabaseHas('payments', [
            'status' => 'needs_attention',
            'review_note' => 'Send the correct transaction screenshot.',
        ]);
    }

    public function test_repeating_the_command_never_creates_duplicate_records(): void
    {
        $this->enrollment([
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'amount' => 5000,
            'amount_paid' => 5000,
            'amount_due' => 0,
        ]);

        DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_name' => 'Rahim',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01812345678',
            'amount' => 800,
            'status' => 'pending',
        ]);

        ServiceOrder::create([
            'service_id' => $this->service()->id,
            'student_name' => 'Karim',
            'student_email' => 'karim@example.com',
            'student_phone' => '01912345678',
            'amount' => 2000,
            'amount_paid' => 2000,
            'amount_due' => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
        ]);

        $this->artisan('orders:backfill')->assertExitCode(0);
        $this->assertDatabaseCount('orders', 3);
        $this->assertDatabaseCount('order_items', 3);
        $this->assertDatabaseCount('payments', 3);

        $this->artisan('orders:backfill')->assertExitCode(0);
        $this->assertDatabaseCount('orders', 3);
        $this->assertDatabaseCount('order_items', 3);
        $this->assertDatabaseCount('payments', 3);

        $this->artisan('orders:backfill')->assertExitCode(0);
        $this->assertDatabaseCount('orders', 3);
        $this->assertDatabaseCount('order_items', 3);
        $this->assertDatabaseCount('payments', 3);
    }

    public function test_the_command_never_modifies_legacy_records(): void
    {
        $enrollment = $this->enrollment([
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'amount' => 5000,
            'amount_paid' => 5000,
            'amount_due' => 0,
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
        ]);

        $digitalOrder = DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_name' => 'Rahim',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01812345678',
            'amount' => 800,
            'status' => 'approved',
        ]);

        $before = [
            'enrollment' => $enrollment->fresh()->getAttributes(),
            'digital_order' => $digitalOrder->fresh()->getAttributes(),
        ];

        $this->artisan('orders:backfill')->assertExitCode(0);
        $this->artisan('orders:backfill')->assertExitCode(0);

        $this->assertSame($before['enrollment'], $enrollment->fresh()->getAttributes());
        $this->assertSame($before['digital_order'], $digitalOrder->fresh()->getAttributes());
    }

    public function test_an_unrecognized_payment_status_is_skipped_without_aborting_other_rows(): void
    {
        $this->enrollment([
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
            'amount' => 1000,
            'amount_paid' => 0,
            'amount_due' => 1000,
        ]);

        $weird = $this->enrollment([
            'payment_status' => 'awaiting_courier',
            'enrollment_status' => 'pending',
            'amount' => 1000,
            'amount_paid' => 0,
            'amount_due' => 1000,
        ]);

        $this->artisan('orders:backfill')
            ->expectsOutputToContain('Payment skipped: unrecognized legacy payment_status "awaiting_courier"')
            ->assertExitCode(0);

        $this->assertDatabaseCount('orders', 2);
        $this->assertSame(0, Order::where('legacy_id', $weird->id)->first()->payments()->count());
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_missing_amount_falls_back_to_the_legacy_price_paid_column(): void
    {
        $enrollment = $this->enrollment([
            'amount' => 0,
            'price_paid' => 9500,
            'amount_paid' => 0,
            'amount_due' => 9500,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);

        $this->artisan('orders:backfill')->assertExitCode(0);

        $this->assertDatabaseHas('orders', [
            'legacy_source' => 'enrollment',
            'legacy_id' => $enrollment->id,
            'total_amount' => 9500,
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

    protected function product(): Product
    {
        return Product::create([
            'title' => 'HSK Vocabulary PDF',
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => 800,
            'file_path' => 'products/hsk-vocabulary.pdf',
        ]);
    }

    protected function service(): Service
    {
        return Service::create([
            'name' => 'Guided Application',
            'slug' => 'service-'.Str::lower(Str::random(8)),
            'price' => 2000,
        ]);
    }

    protected function enrollment(array $overrides = []): Enrollment
    {
        return Enrollment::create(array_merge([
            'course_id' => $this->course()->id,
            'student_name' => 'Nusrat Rahman',
            'student_email' => 'nusrat@example.com',
            'student_phone' => '01712345678',
            'amount' => 5000,
            'amount_paid' => 0,
            'amount_due' => 5000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => null,
            'transaction_id' => null,
            'sender_number' => null,
        ], $overrides));
    }
}
