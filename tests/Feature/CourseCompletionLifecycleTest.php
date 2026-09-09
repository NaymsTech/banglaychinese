<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Services\EnrollmentApprovalService;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CourseCompletionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_moves_the_canonical_order_to_in_progress(): void
    {
        $enrollment = $this->enrollment();

        app(PaymentReviewService::class)->approve($enrollment);

        $this->assertSame('in_progress', $this->canonicalOrder($enrollment)->order_status);
        $this->assertSame('in_progress', $enrollment->fresh()->enrollment_status);
    }

    public function test_completion_moves_both_the_enrollment_and_the_canonical_order_to_completed(): void
    {
        $enrollment = $this->enrollment();
        app(PaymentReviewService::class)->approve($enrollment);

        $completed = app(EnrollmentApprovalService::class)->complete($enrollment->fresh());

        $this->assertTrue($completed);
        $this->assertSame('completed', $enrollment->fresh()->enrollment_status);
        $this->assertSame('completed', $this->canonicalOrder($enrollment)->order_status);
    }

    public function test_repeated_completion_is_idempotent(): void
    {
        $enrollment = $this->enrollment();
        app(PaymentReviewService::class)->approve($enrollment);

        $service = app(EnrollmentApprovalService::class);
        $service->complete($enrollment->fresh());

        $second = $service->complete($enrollment->fresh());

        $this->assertFalse($second);
        $this->assertSame('completed', $enrollment->fresh()->enrollment_status);
        $this->assertSame('completed', $this->canonicalOrder($enrollment)->order_status);
    }

    public function test_an_unpaid_enrollment_can_never_be_completed(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);

        $completed = app(EnrollmentApprovalService::class)->complete($enrollment->fresh());

        $this->assertFalse($completed);
        $this->assertSame('pending', $enrollment->fresh()->enrollment_status);
        $this->assertSame('pending', $this->canonicalOrder($enrollment)->order_status);
    }

    public function test_legacy_and_canonical_lifecycle_stay_consistent_after_approval_and_completion(): void
    {
        $enrollment = $this->enrollment();
        app(PaymentReviewService::class)->approve($enrollment);
        app(EnrollmentApprovalService::class)->complete($enrollment->fresh());

        $order = $this->canonicalOrder($enrollment);

        $this->assertSame('paid', $enrollment->fresh()->payment_status);
        $this->assertSame('completed', $enrollment->fresh()->enrollment_status);
        $this->assertSame('completed', $order->order_status);
        $this->assertSame('paid', $order->payments()->first()->status);
    }

    protected function enrollment(): Enrollment
    {
        return Enrollment::create([
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

    protected function canonicalOrder(Enrollment $enrollment): Order
    {
        return Order::where('legacy_source', 'enrollment')
            ->where('legacy_id', $enrollment->id)
            ->firstOrFail();
    }
}
