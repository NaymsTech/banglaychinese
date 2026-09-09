<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PartialPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_partial_payments_accumulate_and_settle_through_canonical_payments(): void
    {
        $enrollment = $this->courseEnrollment(10000);
        $service = app(PaymentReviewService::class);

        // 1st payment: 4000 of 10000
        $first = $service->recordPayment($enrollment, $this->paymentData(4000));
        $order = $first['payment']->order;
        $this->assertSame(4000.0, $order->paidTotal());
        $this->assertSame(6000.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PARTIALLY_PAID, $order->paymentState());

        $legacy = $enrollment->fresh();
        $this->assertSame('4000.00', $legacy->amount_paid);
        $this->assertSame('6000.00', $legacy->amount_due);
        $this->assertSame('partially_paid', $legacy->payment_status);

        // 2nd payment: 3000 → 7000 paid, 3000 due
        $service->recordPayment($enrollment->fresh(), $this->paymentData(3000));
        $this->assertSame(7000.0, $order->fresh()->paidTotal());
        $this->assertSame(3000.0, $order->fresh()->dueTotal());

        // 3rd payment settles and mirrors the enrollment to paid/in_progress.
        $final = $service->recordPayment($enrollment->fresh(), $this->paymentData(3000));
        $this->assertSame(10000.0, $final['paid_total']);
        $this->assertSame(0.0, $final['due']);
        $this->assertSame('paid', $final['state']);

        $settled = $enrollment->fresh();
        $this->assertSame('paid', $settled->payment_status);
        $this->assertSame('in_progress', $settled->enrollment_status);
        $this->assertNotNull($settled->paid_at);

        // All three recorded payments are retained as history.
        $this->assertSame(3, Order::firstOrFail()->payments()->where('status', 'paid')->count());
    }

    public function test_a_pending_customer_claim_is_superseded_by_a_recorded_payment(): void
    {
        $enrollment = $this->courseEnrollment(10000);
        // Guest checkout leaves a pending claim of the full amount.
        $order = Order::firstOrFail();
        $claim = $order->payments()->firstOrFail();
        $this->assertSame('pending', $claim->status);

        app(PaymentReviewService::class)->recordPayment($enrollment, $this->paymentData(4000));

        $this->assertSame('rejected', $claim->fresh()->status);
        $this->assertSame('Superseded by admin-recorded payment.', $claim->fresh()->review_note);
        $this->assertSame(4000.0, $order->fresh()->paidTotal());
    }

    public function test_recorded_payment_above_due_or_non_positive_is_rejected(): void
    {
        $enrollment = $this->courseEnrollment(10000);
        $service = app(PaymentReviewService::class);

        foreach ([0, -100, 11000] as $amount) {
            try {
                $service->recordPayment($enrollment->fresh(), $this->paymentData($amount));
                $this->fail('Invalid payment amount must be rejected: '.$amount);
            } catch (\InvalidArgumentException) {
                // expected
            }
        }

        $this->assertSame(0, Order::firstOrFail()->payments()->where('status', 'paid')->count());
    }

    public function test_digital_products_reject_partial_payments(): void
    {
        $product = Product::create([
            'title' => 'E-Book',
            'slug' => 'ebook-'.Str::lower(Str::random(6)),
            'price' => 800,
            'file_path' => 'products/ebook.pdf',
            'is_published' => true,
        ]);

        $digitalOrder = DigitalOrder::create([
            'product_id' => $product->id,
            'student_name' => 'Buyer',
            'student_email' => 'buyer@example.com',
            'student_phone' => '01712345678',
            'trx_id' => 'TRXFULLPAY1',
            'amount' => 800,
            'status' => 'pending',
        ]);
        app(OrderMaterializer::class)->materialize($digitalOrder);

        try {
            app(PaymentReviewService::class)->recordPayment($digitalOrder, $this->paymentData(300));
            $this->fail('Partial payment on a digital product must be rejected.');
        } catch (\InvalidArgumentException) {
            // expected
        }

        $this->assertSame('pending', $digitalOrder->fresh()->status);

        $result = app(PaymentReviewService::class)->recordPayment($digitalOrder, $this->paymentData(800));

        $this->assertSame('approved', $digitalOrder->fresh()->status);
        $this->assertSame('completed', Order::firstOrFail()->order_status);
        $this->assertSame(0.0, $result['due']);
    }

    public function test_service_order_partial_payments_flow_through_canonical_payments(): void
    {
        $service = Service::create([
            'name' => 'Full Application Service',
            'slug' => 'service-'.Str::lower(Str::random(6)),
            'price' => 50000,
        ]);

        $serviceOrder = ServiceOrder::create([
            'service_id' => $service->id,
            'student_name' => 'Service Buyer',
            'student_email' => 'service-buyer@example.com',
            'student_phone' => '01712345678',
            'amount' => 50000,
            'amount_paid' => 0,
            'amount_due' => 50000,
            'payment_method' => 'bank',
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);
        app(OrderMaterializer::class)->materialize($serviceOrder);

        $review = app(PaymentReviewService::class);

        $review->recordPayment($serviceOrder, $this->paymentData(20000));
        $review->recordPayment($serviceOrder->fresh(), $this->paymentData(30000));

        $order = Order::firstOrFail();
        $this->assertSame(50000.0, $order->paidTotal());
        $this->assertSame(0.0, $order->dueTotal());
        $this->assertSame(2, $order->payments()->where('status', 'paid')->count());

        $legacy = $serviceOrder->fresh();
        $this->assertSame('50000.00', $legacy->amount_paid);
        $this->assertSame('0.00', $legacy->amount_due);
        $this->assertSame('paid', $legacy->payment_status);
        $this->assertSame('in_progress', $legacy->enrollment_status);
    }

    public function test_student_dashboard_shows_canonical_paid_and_due_for_a_partially_paid_course(): void
    {
        $user = $this->student();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $this->course(10000)->id,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'student_phone' => '01712345678',
            'amount' => 10000,
            'amount_paid' => 0,
            'amount_due' => 10000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);
        app(OrderMaterializer::class)->materialize($enrollment);

        app(PaymentReviewService::class)->recordPayment($enrollment, $this->paymentData(4000));

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Paid: ৳4,000.00')
            ->assertSee('Due: ৳6,000.00');
    }

    protected function paymentData(int $amount): array
    {
        return [
            'amount' => $amount,
            'method' => 'bkash',
            'trx_reference' => 'TRX'.Str::upper(Str::random(8)),
            'sender_number' => '01712345678',
        ];
    }

    protected function courseEnrollment(int $price): Enrollment
    {
        $user = $this->student();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $this->course($price)->id,
            'student_name' => 'Partial Buyer',
            'student_email' => $user->email,
            'student_phone' => '01712345678',
            'amount' => $price,
            'amount_paid' => 0,
            'amount_due' => $price,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);

        app(OrderMaterializer::class)->materialize($enrollment);

        return $enrollment;
    }

    protected function course(int $price): Course
    {
        return Course::create([
            'title' => 'HSK Course',
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => $price,
            'is_published' => true,
        ]);
    }

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
            'email' => 'student-'.Str::lower(Str::random(6)).'@example.com',
            'phone' => '01712345678',
        ]);
    }
}
