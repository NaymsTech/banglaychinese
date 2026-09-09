<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\OrderMaterializer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_consistent_legacy_and_canonical_rows_report_no_errors(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);
        $freeEnrollment = $this->enrollment([
            'amount' => 0,
            'amount_due' => 0,
            'payment_method' => 'free',
        ]);
        app(OrderMaterializer::class)->materialize($freeEnrollment);
        $digitalOrder = $this->digitalOrder();
        app(OrderMaterializer::class)->materialize($digitalOrder);

        $this->artisan('orders:parity')->assertExitCode(0);
    }

    public function test_legacy_total_amount_divergence_is_an_error(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);

        DB::table('enrollments')->where('id', $enrollment->id)->update(['amount' => 5010, 'amount_due' => 5010]);

        $this->artisan('orders:parity')
            ->expectsOutputToContain('total amount mismatch')
            ->assertExitCode(1);
    }

    public function test_a_legacy_row_without_canonical_records_is_an_error(): void
    {
        $this->enrollment();

        $this->artisan('orders:parity')
            ->expectsOutputToContain('missing canonical order')
            ->assertExitCode(1);
    }

    public function test_an_orphan_canonical_order_is_an_error(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);

        DB::table('enrollments')->where('id', $enrollment->id)->delete();

        $this->artisan('orders:parity')
            ->expectsOutputToContain('no matching legacy row')
            ->assertExitCode(1);
    }

    public function test_a_canonical_order_without_items_is_an_error(): void
    {
        Order::create([
            'legacy_source' => 'enrollment',
            'legacy_id' => 999999,
            'total_amount' => 1000,
            'order_status' => 'pending',
        ]);

        $this->artisan('orders:parity')
            ->expectsOutputToContain('has no order items')
            ->assertExitCode(1);
    }

    public function test_historical_refunded_rows_only_warn(): void
    {
        $enrollment = $this->enrollment([
            'amount' => 5000,
            'amount_paid' => 5000,
            'amount_due' => 0,
            'payment_status' => 'refunded',
            'enrollment_status' => 'in_progress',
        ]);
        app(OrderMaterializer::class)->materialize($enrollment);

        $this->artisan('orders:parity')
            ->expectsOutputToContain('refunded')
            ->assertExitCode(0);
    }

    public function test_legacy_only_order_received_marker_is_a_known_warning(): void
    {
        $digitalOrder = $this->digitalOrder();
        app(OrderMaterializer::class)->materialize($digitalOrder);

        DB::table('digital_orders')
            ->where('id', $digitalOrder->id)
            ->update(['order_received_email_sent_at' => now()]);

        $this->artisan('orders:parity')
            ->expectsOutputToContain('order_received marker is legacy-only')
            ->assertExitCode(0);
    }

    public function test_the_command_never_modifies_the_database(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);
        $digitalOrder = $this->digitalOrder();
        app(OrderMaterializer::class)->materialize($digitalOrder);

        $before = [
            'enrollments' => $enrollment->fresh()->getAttributes(),
            'digital_orders' => $digitalOrder->fresh()->getAttributes(),
            'orders' => Order::count(),
            'order_items' => OrderItem::count(),
            'payments' => Payment::count(),
        ];

        $this->artisan('orders:parity')->assertExitCode(0);
        $this->artisan('orders:parity')->assertExitCode(0);

        $this->assertSame($before['enrollments'], $enrollment->fresh()->getAttributes());
        $this->assertSame($before['digital_orders'], $digitalOrder->fresh()->getAttributes());
        $this->assertSame($before['orders'], Order::count());
        $this->assertSame($before['order_items'], OrderItem::count());
        $this->assertSame($before['payments'], Payment::count());
    }

    protected function enrollment(array $overrides = []): Enrollment
    {
        return Enrollment::create(array_merge([
            'course_id' => $this->course()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 5000,
            'amount_paid' => 0,
            'amount_due' => 5000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
        ], $overrides));
    }

    protected function digitalOrder(): DigitalOrder
    {
        return DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => 'TRX987654321',
            'amount' => 800,
            'status' => 'pending',
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
}
