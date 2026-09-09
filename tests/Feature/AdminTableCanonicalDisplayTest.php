<?php

namespace Tests\Feature;

use App\Filament\Resources\DigitalOrders\Pages\ListDigitalOrders;
use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderMaterializer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTableCanonicalDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_payment_display_comes_from_canonical_payment_data(): void
    {
        $this->paidEnrollment('Nusrat Rahman');

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->assertOk()
            ->assertSee('৳ 5,000.00')
            ->assertSee('৳ 0.00');
    }

    public function test_changing_legacy_amount_paid_alone_never_changes_the_canonical_display(): void
    {
        $enrollment = $this->paidEnrollment('Nusrat Rahman');

        DB::table('enrollments')->where('id', $enrollment->id)->update(['amount_paid' => 100, 'amount_due' => 4900]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->assertSee('৳ 5,000.00')
            ->assertDontSee('৳ 100.00');
    }

    public function test_partial_payments_display_canonical_paid_and_due_state(): void
    {
        $this->enrollmentWithCanonical([
            'student_name' => 'Karim Ahmed',
            'amount' => 5000,
            'amount_paid' => 2000,
            'amount_due' => 3000,
            'payment_status' => 'partially_paid',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->assertSee('৳ 2,000.00')
            ->assertSee('৳ 3,000.00');
    }

    public function test_refunds_reduce_net_paid_and_never_increase_totals(): void
    {
        $enrollment = $this->paidEnrollment('Refunded Student', amount: 1000);
        $order = $this->canonicalOrder($enrollment);

        $order->payments()->create([
            'amount' => 300,
            'status' => Payment::STATUS_REFUNDED,
            'method' => 'bkash',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->assertSee('৳ 700.00')   // net paid
            ->assertDontSee('৳ 1,300.00');
    }

    public function test_multiple_payment_rows_never_duplicate_the_table_record(): void
    {
        $enrollment = $this->enrollmentWithCanonical([
            'student_name' => 'Only Once Student',
            'amount' => 1000,
            'amount_paid' => 1000,
            'amount_due' => 0,
            'payment_status' => 'paid',
        ]);

        $order = $this->canonicalOrder($enrollment);
        $order->payments()->delete();
        $order->payments()->create(['amount' => 400, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => now()]);
        $order->payments()->create(['amount' => 600, 'status' => Payment::STATUS_PAID, 'method' => 'nagad', 'paid_at' => now()]);

        $html = Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->assertOk()
            ->html();

        $this->assertSame(1, substr_count($html, 'Only Once Student'));
    }

    public function test_digital_transaction_reference_comes_from_canonical_payment(): void
    {
        $digitalOrder = $this->digitalOrderWithCanonical('TRX-CANONICAL-1');

        // Simulate a legacy column that drifted away from the canonical value.
        DB::table('digital_orders')->where('id', $digitalOrder->id)->update(['trx_id' => 'TRX-LEGACY-1']);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->assertSee('TRX-CANONICAL-1')
            ->assertDontSee('TRX-LEGACY-1');
    }

    public function test_a_missing_canonical_order_is_displayed_safely_without_inventing_data(): void
    {
        $this->enrollmentWithCanonical(['student_name' => 'Missing Canonical Student']);

        // Delete the canonical order; the legacy row remains.
        Order::query()->delete();

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->assertOk()
            ->assertSee('—');
    }

    public function test_course_fulfillment_status_still_comes_from_the_enrollment(): void
    {
        $enrollment = $this->enrollmentWithCanonical(['student_name' => 'Fulfil Student']);

        // Legacy fulfillment moves to in_progress without any canonical mirror
        // (direct legacy-only state change) — the canonical order stays pending
        // and the canonical review state stays pending (no payment claim paid).
        DB::table('enrollments')->where('id', $enrollment->id)->update(['enrollment_status' => 'in_progress']);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->assertSee('Fulfil Student');

        $this->assertSame('in_progress', $enrollment->fresh()->enrollment_status);
        $order = $this->canonicalOrder($enrollment);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('pending', $order->reviewStatus());
    }

    public function test_approval_action_still_runs_through_the_review_service(): void
    {
        $enrollment = $this->enrollmentWithCanonical(['student_name' => 'Action Student']);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('markPaid', $enrollment);

        $this->assertSame('paid', $enrollment->fresh()->payment_status);
        $this->assertSame('paid', $this->canonicalOrder($enrollment)->payments()->first()->status);
    }

    protected function paidEnrollment(string $name, int $amount = 5000): Enrollment
    {
        return $this->enrollmentWithCanonical([
            'student_name' => $name,
            'amount' => $amount,
            'amount_paid' => $amount,
            'amount_due' => 0,
            'payment_status' => 'paid',
        ]);
    }

    protected function enrollmentWithCanonical(array $overrides = []): Enrollment
    {
        $enrollment = Enrollment::create(array_merge([
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

        app(OrderMaterializer::class)->materialize($enrollment);

        return $enrollment;
    }

    protected function digitalOrderWithCanonical(string $trx): DigitalOrder
    {
        $digitalOrder = DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => $trx,
            'amount' => 800,
            'status' => 'pending',
        ]);

        app(OrderMaterializer::class)->materialize($digitalOrder);

        return $digitalOrder;
    }

    protected function canonicalOrder(Enrollment $enrollment): Order
    {
        return Order::where('legacy_source', 'enrollment')
            ->where('legacy_id', $enrollment->id)
            ->firstOrFail();
    }

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
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
