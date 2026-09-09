<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentDashboardCanonicalDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_amount_comes_from_canonical_order_total(): void
    {
        $user = $this->student();
        $this->approvedDigitalOrder($user, amount: 800);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('৳800.00');
    }

    public function test_changing_legacy_amount_alone_does_not_change_the_canonical_display(): void
    {
        $user = $this->student();
        $digitalOrder = $this->approvedDigitalOrder($user, amount: 800);

        DB::table('digital_orders')->where('id', $digitalOrder->id)->update(['amount' => 9999]);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('৳800.00')
            ->assertDontSee('৳9,999.00');
    }

    public function test_course_sections_still_split_on_enrollment_fulfillment_state(): void
    {
        $user = $this->student();
        $enrollment = $this->enrollmentFor($user);
        app(OrderMaterializer::class)->materialize($enrollment);

        // Legacy fulfillment alone moves the course into the active section;
        // the canonical order stays pending. Access reads must stay legacy.
        DB::table('enrollments')->where('id', $enrollment->id)->update(['enrollment_status' => 'in_progress']);

        $this->assertSame('pending', Order::where('legacy_source', 'enrollment')->first()->order_status);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('ACTIVE')
            ->assertDontSee('পেমেন্ট পর্যালোচনায় আছে');
    }

    public function test_partial_payment_keeps_canonical_paid_and_due_state_correct(): void
    {
        $user = $this->student();
        $enrollment = $this->enrollmentFor($user, [
            'amount' => 5000,
            'amount_paid' => 2000,
            'amount_due' => 3000,
            'payment_status' => 'partially_paid',
        ]);
        $order = app(OrderMaterializer::class)->materialize($enrollment)['order'];

        $this->actingAs($user)->get(route('dashboard.index'))->assertOk();

        $this->assertSame(2000.0, $order->paidTotal());
        $this->assertSame(3000.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PARTIALLY_PAID, $order->paymentState());
    }

    public function test_refunds_reduce_net_received_and_never_increase_money_state(): void
    {
        $user = $this->student();
        $enrollment = $this->enrollmentFor($user, [
            'amount' => 1000,
            'amount_paid' => 1000,
            'amount_due' => 0,
            'payment_status' => 'paid',
        ]);
        $order = app(OrderMaterializer::class)->materialize($enrollment)['order'];
        $order->payments()->create(['amount' => 300, 'status' => Payment::STATUS_REFUNDED, 'method' => 'bkash']);

        $this->actingAs($user)->get(route('dashboard.index'))->assertOk();

        $this->assertSame(700.0, $order->netReceived());
        $this->assertSame(300.0, $order->dueTotal());
    }

    public function test_multiple_payment_rows_never_duplicate_the_download_listing(): void
    {
        $user = $this->student();
        $digitalOrder = $this->approvedDigitalOrder($user, amount: 1000);

        $order = Order::where('legacy_source', 'digital_order')->where('legacy_id', $digitalOrder->id)->firstOrFail();
        $order->payments()->delete();
        $order->payments()->create(['amount' => 400, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => now()]);
        $order->payments()->create(['amount' => 600, 'status' => Payment::STATUS_PAID, 'method' => 'nagad', 'paid_at' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard.index'));
        $response->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), $digitalOrder->product->title));
    }

    public function test_digital_correction_form_prefills_the_canonical_transaction_reference(): void
    {
        $user = $this->student();
        $digitalOrder = $this->digitalOrderWithCanonical($user, 'CANON-TRX-9');
        app(PaymentReviewService::class)->requestCorrection($digitalOrder, 'Send correct screenshot.');

        DB::table('digital_orders')->where('id', $digitalOrder->id)->update(['trx_id' => 'LEGACY-TRX-9']);

        $this->actingAs($user)
            ->get(route('dashboard.payments.order.edit', $digitalOrder))
            ->assertOk()
            ->assertSee('CANON-TRX-9')
            ->assertDontSee('LEGACY-TRX-9');
    }

    public function test_enrollment_correction_form_prefills_canonical_payment_details(): void
    {
        $user = $this->student();
        $enrollment = $this->enrollmentFor($user, ['payment_method' => 'bkash', 'transaction_id' => '9JQ2A3B4C5', 'sender_number' => '01712345678']);
        app(OrderMaterializer::class)->materialize($enrollment);
        app(PaymentReviewService::class)->requestCorrection($enrollment, 'Wrong amount.');

        DB::table('enrollments')->where('id', $enrollment->id)->update(['transaction_id' => 'LEGACY12345']);

        $this->actingAs($user)
            ->get(route('dashboard.payments.enrollment.edit', $enrollment))
            ->assertOk()
            ->assertSee('9JQ2A3B4C5')
            ->assertDontSee('LEGACY12345');
    }

    public function test_missing_canonical_order_shows_no_amount_and_never_materializes(): void
    {
        $user = $this->student();
        $digitalOrder = DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_email' => $user->email,
            'student_name' => $user->name,
            'student_phone' => '01712345678',
            'amount' => 800,
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertDontSee('৳800.00');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_legacy_only_needs_attention_record_cannot_open_the_correction_form(): void
    {
        $user = $this->student();
        $enrollment = $this->enrollmentFor($user, [
            'payment_status' => 'needs_attention',
            'attention_reason' => 'Fix your transaction id.',
            'transaction_id' => '9JQ2A3B4C5',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.payments.enrollment.edit', $enrollment))
            ->assertRedirect(route('dashboard.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_missing_canonical_payment_never_exposes_stale_legacy_transaction_data(): void
    {
        $user = $this->student();
        $enrollment = $this->enrollmentFor($user, [
            'payment_status' => 'pending',
            'transaction_id' => 'LEGACY-OLD-TXN',
        ]);
        app(OrderMaterializer::class)->materialize($enrollment);
        app(PaymentReviewService::class)->requestCorrection($enrollment, 'Fix the reference.');

        // Canonical Payment rows are gone while the legacy mirror still holds
        // the old transaction id — the correction form must not be reachable.
        $order = Order::where('legacy_source', 'enrollment')->where('legacy_id', $enrollment->id)->firstOrFail();
        $order->payments()->delete();

        $this->actingAs($user)
            ->get(route('dashboard.payments.enrollment.edit', $enrollment))
            ->assertRedirect(route('dashboard.index'));

        $this->assertSame('needs_attention', $enrollment->fresh()->payment_status);
        $this->assertSame('LEGACY-OLD-TXN', $enrollment->fresh()->transaction_id);
        $this->assertSame(0, $order->payments()->count());
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_correction_resubmission_still_runs_through_the_review_service(): void
    {
        $user = $this->student();
        $enrollment = $this->enrollmentFor($user);
        app(OrderMaterializer::class)->materialize($enrollment);
        app(PaymentReviewService::class)->requestCorrection($enrollment, 'Wrong amount.');

        $this->actingAs($user)
            ->post(route('dashboard.payments.enrollment.resubmit', $enrollment), [
                'payment_method' => 'nagad',
                'transaction_id' => 'NAGAD11223344',
                'sender_number' => '01812345678',
            ])
            ->assertRedirect(route('dashboard.index'));

        $legacy = $enrollment->fresh();
        $this->assertSame('pending', $legacy->payment_status);
        $this->assertSame('NAGAD11223344', $legacy->transaction_id);

        $payment = Order::where('legacy_source', 'enrollment')->firstOrFail()->payments()->firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertSame('NAGAD11223344', $payment->trx_reference);
    }

    public function test_unrelated_dashboard_behavior_remains_unchanged(): void
    {
        $user = $this->student();
        $this->enrollmentFor($user);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('My Courses')
            ->assertSee('My Downloads');
    }

    protected function approvedDigitalOrder(User $user, int $amount): DigitalOrder
    {
        $digitalOrder = $this->digitalOrderWithCanonical($user, 'TRX-'.$amount);
        app(PaymentReviewService::class)->approve($digitalOrder);

        return $digitalOrder;
    }

    protected function digitalOrderWithCanonical(User $user, string $trx): DigitalOrder
    {
        $digitalOrder = DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_email' => $user->email,
            'student_name' => $user->name,
            'student_phone' => '01712345678',
            'trx_id' => $trx,
            'amount' => 800,
            'status' => 'pending',
        ]);

        app(OrderMaterializer::class)->materialize($digitalOrder);

        return $digitalOrder;
    }

    protected function enrollmentFor(User $user, array $overrides = []): Enrollment
    {
        return Enrollment::create(array_merge([
            'user_id' => $user->id,
            'course_id' => $this->course()->id,
            'student_name' => $user->name,
            'student_email' => $user->email,
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

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
            'email' => 'student-'.Str::lower(Str::random(6)).'@example.com',
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
            'title' => 'HSK Vocabulary PDF '.Str::lower(Str::random(5)),
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => 800,
            'file_path' => 'products/hsk-vocabulary.pdf',
        ]);
    }
}
