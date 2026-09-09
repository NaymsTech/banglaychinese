<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_a_pending_enrollment_reconciles_canonical_and_mirrors_legacy(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);

        $result = app(PaymentReviewService::class)->approve($enrollment);

        $this->assertTrue($result['changed']);
        $this->assertFalse($result['emailed']); // no template seeded — fail-open

        $legacy = $enrollment->fresh();
        $this->assertSame('paid', $legacy->payment_status);
        $this->assertSame('in_progress', $legacy->enrollment_status);
        $this->assertSame('5000.00', $legacy->amount_paid);
        $this->assertNotNull($legacy->paid_at);

        $order = $this->canonicalOrder($enrollment);
        $this->assertSame('in_progress', $order->order_status);
        $payment = $order->payments()->first();
        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_approving_a_partially_paid_enrollment_adds_a_balancing_payment(): void
    {
        $enrollment = $this->enrollment([
            'amount' => 5000,
            'amount_paid' => 2000,
            'amount_due' => 3000,
            'payment_status' => 'partially_paid',
        ]);
        app(OrderMaterializer::class)->materialize($enrollment);

        $result = app(PaymentReviewService::class)->approve($enrollment);

        $this->assertTrue($result['changed']);
        $this->assertSame('paid', $enrollment->fresh()->payment_status);

        $order = $this->canonicalOrder($enrollment);
        $this->assertSame(2, $order->payments()->count());
        $this->assertSame(5000.0, (float) $order->payments()->where('status', 'paid')->get()->sum('amount'));
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'paid',
            'amount' => 3000,
        ]);
        $this->assertSame(0.0, $order->dueTotal());
    }

    public function test_approving_an_already_approved_enrollment_is_a_no_op(): void
    {
        $enrollment = $this->enrollment();
        $service = app(PaymentReviewService::class);

        $service->approve($enrollment);
        $second = $service->approve($enrollment->fresh());

        $this->assertFalse($second['changed']);
        $this->assertSame(1, $this->canonicalOrder($enrollment)->payments()->count());
    }

    public function test_rejecting_an_enrollment_marks_the_claim_and_mirrors_the_reason(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);

        $result = app(PaymentReviewService::class)->reject($enrollment, 'Transaction id not found.');

        $this->assertTrue($result['changed']);
        $legacy = $enrollment->fresh();
        $this->assertSame('rejected', $legacy->payment_status);
        $this->assertSame('cancelled', $legacy->enrollment_status);
        $this->assertSame('Transaction id not found.', $legacy->rejection_reason);

        $order = $this->canonicalOrder($enrollment);
        $this->assertSame('cancelled', $order->order_status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'rejected',
            'review_note' => 'Transaction id not found.',
        ]);
    }

    public function test_rejecting_an_enrollment_twice_is_a_no_op(): void
    {
        $enrollment = $this->enrollment();
        $service = app(PaymentReviewService::class);

        $first = $service->reject($enrollment, 'Reason A');
        $second = $service->reject($enrollment->fresh(), 'Reason B');

        $this->assertTrue($first['changed']);
        $this->assertFalse($second['changed']);
        $this->assertSame('Reason A', $enrollment->fresh()->rejection_reason);
    }

    public function test_requesting_correction_on_an_enrollment_flags_the_claim(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);

        $result = app(PaymentReviewService::class)->requestCorrection($enrollment, 'Send the correct screenshot.');

        $this->assertTrue($result['changed']);
        $legacy = $enrollment->fresh();
        $this->assertSame('needs_attention', $legacy->payment_status);
        $this->assertSame('Send the correct screenshot.', $legacy->attention_reason);

        $order = $this->canonicalOrder($enrollment);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'needs_attention',
            'review_note' => 'Send the correct screenshot.',
        ]);
    }

    public function test_requesting_correction_twice_is_a_no_op(): void
    {
        $enrollment = $this->enrollment();
        $service = app(PaymentReviewService::class);

        $first = $service->requestCorrection($enrollment, 'Action one.');
        $second = $service->requestCorrection($enrollment->fresh(), 'Action two.');

        $this->assertTrue($first['changed']);
        $this->assertFalse($second['changed']);
        $this->assertSame('Action one.', $enrollment->fresh()->attention_reason);
    }

    public function test_resubmitting_an_enrollment_updates_the_payment_and_returns_to_pending(): void
    {
        $enrollment = $this->enrollment();
        app(OrderMaterializer::class)->materialize($enrollment);
        app(PaymentReviewService::class)->requestCorrection($enrollment, 'Wrong number.');

        $changed = app(PaymentReviewService::class)->resubmit($enrollment->fresh(), [
            'payment_method' => 'nagad',
            'transaction_id' => 'NAGAD1122334455',
            'sender_number' => '01812345678',
        ]);

        $this->assertTrue($changed);

        $legacy = $enrollment->fresh();
        $this->assertSame('pending', $legacy->payment_status);
        $this->assertSame('nagad', $legacy->payment_method);
        $this->assertSame('NAGAD1122334455', $legacy->transaction_id);
        $this->assertNull($legacy->attention_reason);

        $payment = $this->canonicalOrder($enrollment)->payments()->first();
        $this->assertSame('pending', $payment->status);
        $this->assertSame('nagad', $payment->method);
        $this->assertSame('NAGAD1122334455', $payment->trx_reference);
        $this->assertNull($payment->review_note);
    }

    public function test_approve_materializes_canonical_records_when_they_are_missing(): void
    {
        $enrollment = $this->enrollment();

        // No materializer/backfill has run for this legacy-only row.
        $this->assertDatabaseCount('orders', 0);

        $result = app(PaymentReviewService::class)->approve($enrollment);

        $this->assertTrue($result['changed']);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame('paid', $enrollment->fresh()->payment_status);

        $order = $this->canonicalOrder($enrollment);
        $this->assertSame('in_progress', $order->order_status);
        $this->assertSame('paid', $order->payments()->first()->status);
    }

    public function test_approval_confirmation_email_is_sent_exactly_once_for_an_enrollment(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->template('course_enrollment_confirmation', 'Course unlocked: {course_title}', '<p>Hi {student_name} — {course_title}</p>');

        $enrollment = $this->enrollment();
        $service = app(PaymentReviewService::class);

        Queue::fake([SendEmailJob::class]);

        $first = $service->approve($enrollment);
        $second = $service->approve($enrollment->fresh());

        $this->assertTrue($first['changed']);
        $this->assertTrue($first['emailed']);
        $this->assertFalse($second['changed']);
        $this->assertTrue($second['emailed']); // marker already claimed → no resend

        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);

        $order = $this->canonicalOrder($enrollment);
        $this->assertNotNull($order->outcome_email_sent_at);
        $this->assertNotNull($enrollment->fresh()->confirmation_email_sent_at);
    }

    public function test_rejection_email_is_sent_exactly_once_for_an_enrollment(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->template('payment_verification_failed', 'Order {order_number} rejected', '<p>{student_name}: {rejection_reason}</p>');

        $enrollment = $this->enrollment();
        $service = app(PaymentReviewService::class);

        Queue::fake([SendEmailJob::class]);

        $first = $service->reject($enrollment, 'Wrong amount.');
        $second = $service->reject($enrollment->fresh(), 'Wrong amount again.');

        $this->assertTrue($first['changed']);
        $this->assertFalse($second['changed']);

        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
        $this->assertNotNull($this->canonicalOrder($enrollment)->rejection_email_sent_at);
    }

    public function test_correction_email_is_sent_exactly_once_for_an_enrollment(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->template('payment_information_needs_attention', 'Correction needed', '<p>{student_name}: {required_action}</p>');

        $enrollment = $this->enrollment();
        $service = app(PaymentReviewService::class);

        Queue::fake([SendEmailJob::class]);

        $first = $service->requestCorrection($enrollment, 'Resend screenshot.');
        $second = $service->requestCorrection($enrollment->fresh(), 'Resend screenshot.');

        $this->assertTrue($first['changed']);
        $this->assertFalse($second['changed']);

        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
        $this->assertNotNull($this->canonicalOrder($enrollment)->attention_email_sent_at);
    }

    public function test_approving_a_pending_digital_order_completes_the_order_and_mirrors_approval(): void
    {
        $digitalOrder = $this->digitalOrder();
        app(OrderMaterializer::class)->materialize($digitalOrder);

        $result = app(PaymentReviewService::class)->approve($digitalOrder);

        $this->assertTrue($result['changed']);
        $this->assertSame('approved', $digitalOrder->fresh()->status);

        $order = $this->canonicalOrder($digitalOrder);
        $this->assertSame('completed', $order->order_status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => null,
            'status' => 'paid',
        ]);
    }

    public function test_approving_an_already_approved_digital_order_is_a_no_op(): void
    {
        $digitalOrder = $this->digitalOrder();
        $service = app(PaymentReviewService::class);

        $service->approve($digitalOrder);
        $second = $service->approve($digitalOrder->fresh());

        $this->assertFalse($second['changed']);
        $this->assertSame(1, $this->canonicalOrder($digitalOrder)->payments()->count());
    }

    public function test_digital_approval_email_is_sent_exactly_once(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->template('product_approved', '{product_title} approved', '<p>Hi {student_name} — {download_link}</p>');

        $digitalOrder = $this->digitalOrder();
        $service = app(PaymentReviewService::class);

        Queue::fake([SendEmailJob::class]);

        $service->approve($digitalOrder);
        $service->approve($digitalOrder->fresh());

        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
        $this->assertNotNull($digitalOrder->fresh()->approval_email_sent_at);
        $this->assertNotNull($this->canonicalOrder($digitalOrder)->outcome_email_sent_at);
    }

    public function test_rejecting_and_requesting_correction_on_a_digital_order_mirrors_legacy_state(): void
    {
        $digitalOrder = $this->digitalOrder();
        $service = app(PaymentReviewService::class);

        $rejected = $service->reject($digitalOrder, 'Trx invalid.');
        $this->assertTrue($rejected['changed']);
        $this->assertSame('rejected', $digitalOrder->fresh()->status);
        $this->assertSame('cancelled', $this->canonicalOrder($digitalOrder)->order_status);

        $again = $service->reject($digitalOrder->fresh(), 'Another reason.');
        $this->assertFalse($again['changed']);

        // A rejected digital order is no longer eligible for correction.
        $correction = $service->requestCorrection($digitalOrder->fresh(), 'Send screenshot.');
        $this->assertFalse($correction['changed']);
    }

    public function test_digital_order_correction_and_resubmission_round_trip(): void
    {
        $digitalOrder = $this->digitalOrder(['trx_id' => 'TRX111222333']);
        app(OrderMaterializer::class)->materialize($digitalOrder);

        $service = app(PaymentReviewService::class);
        $service->requestCorrection($digitalOrder, 'Wrong transaction id.');

        $this->assertSame('pending', $digitalOrder->fresh()->status);
        $this->assertNotNull($digitalOrder->fresh()->attention_reason);

        $changed = $service->resubmit($digitalOrder->fresh(), ['trx_id' => 'TRX999888777']);

        $this->assertTrue($changed);
        $this->assertNull($digitalOrder->fresh()->attention_reason);
        $this->assertSame('TRX999888777', $digitalOrder->fresh()->trx_id);

        $payment = $this->canonicalOrder($digitalOrder)->payments()->first();
        $this->assertSame('pending', $payment->status);
        $this->assertSame('TRX999888777', $payment->trx_reference);

        $second = $service->resubmit($digitalOrder->fresh(), ['trx_id' => 'TRX000111222']);
        $this->assertFalse($second);
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

    protected function digitalOrder(array $overrides = []): DigitalOrder
    {
        return DigitalOrder::create(array_merge([
            'product_id' => $this->product()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => 'TRX987654321',
            'amount' => 800,
            'status' => 'pending',
        ], $overrides));
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

    protected function template(string $key, string $subject, string $body): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'key' => $key,
            'subject' => $subject,
            'body' => $body,
        ]);
    }

    protected function canonicalOrder(Enrollment|DigitalOrder $sale): Order
    {
        return Order::where('legacy_source', $sale instanceof Enrollment ? 'enrollment' : 'digital_order')
            ->where('legacy_id', $sale->id)
            ->firstOrFail();
    }
}
