<?php

namespace Tests\Feature;

use App\Filament\Resources\DigitalOrders\Pages\ListDigitalOrders;
use App\Filament\Resources\Enrollments\Pages\EditEnrollment;
use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Jobs\SendEmailJob;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Product;
use App\Models\User;
use App\Services\EnrollmentApprovalService;
use App\Services\PaymentReviewService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class OrderPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_admin' => true]);
    }

    protected function provider(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
    }

    protected function template(string $key, array $variables): EmailTemplate
    {
        $body = collect($variables)
            ->map(fn (string $variable): string => "<p>{$variable}: {".$variable.'}</p>')
            ->implode('');

        return EmailTemplate::factory()->create([
            'key' => $key,
            'subject' => 'Notification for {student_name}',
            'body' => $body,
            'variables' => $variables,
        ]);
    }

    protected function course(): Course
    {
        return Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'price' => 9500,
            'is_published' => true,
        ]);
    }

    protected function product(): Product
    {
        return Product::create([
            'title' => 'HSK 1 E-Book',
            'slug' => 'hsk-1-ebook',
            'price' => 299,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Books',
            'is_published' => true,
        ]);
    }

    protected function pendingEnrollment(Course $course, ?User $user = null): Enrollment
    {
        $user ??= User::factory()->create();

        return Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 9500,
            'amount_paid' => 0,
            'amount_due' => 9500,
            'payment_status' => Enrollment::PAYMENT_STATUS_PENDING,
            'enrollment_status' => 'pending',
        ]);
    }

    protected function pendingOrder(): DigitalOrder
    {
        return DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => '9JQ2A3B4C5',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);
    }

    public function test_course_order_creation_queues_exactly_one_pending_email(): void
    {
        $this->provider();
        $this->template('order_received_payment_pending', ['student_name', 'order_number', 'product_title', 'amount', 'order_url']);
        $course = $this->course();
        $user = User::factory()->create();

        Queue::fake([SendEmailJob::class]);

        $this->actingAs($user)
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'bkash',
                'transaction_id' => 'TRX12345678',
                'sender_number' => '01712345678',
            ])
            ->assertRedirect(route('checkout.confirmation'));

        $enrollment = Enrollment::where('course_id', $course->id)->firstOrFail();

        $this->assertSame('pending', $enrollment->payment_status);
        $this->assertSame('pending', $enrollment->enrollment_status);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'order_received_payment_pending',
            'recipient_email' => $user->email,
            'status' => EmailLog::STATUS_QUEUED,
        ]);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_digital_order_creation_queues_exactly_one_pending_email(): void
    {
        $this->provider();
        $this->template('order_received_payment_pending', ['student_name', 'order_number', 'product_title', 'amount', 'order_url']);
        $product = $this->product();

        Queue::fake([SendEmailJob::class]);

        $this->post(route('shop.checkout.store', $product->slug), [
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => '9JQ2A3B4C5',
        ])->assertRedirect(route('shop.thank-you'));

        $this->assertSame('pending', DigitalOrder::firstOrFail()->status);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'order_received_payment_pending',
            'recipient_email' => 'rahim@example.com',
            'status' => EmailLog::STATUS_QUEUED,
        ]);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_course_rejection_stores_the_reason_and_sends_one_rejection_email(): void
    {
        $this->provider();
        $this->template('payment_verification_failed', ['student_name', 'order_number', 'product_title', 'amount', 'rejection_reason', 'support_url']);

        $enrollment = $this->pendingEnrollment($this->course());

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callAction(
                TestAction::make('reject')->table($enrollment),
                ['rejection_reason' => 'Transaction ID not found'],
            );

        $fresh = $enrollment->fresh();

        $this->assertSame('rejected', $fresh->payment_status);
        $this->assertSame('cancelled', $fresh->enrollment_status);
        $this->assertSame('Transaction ID not found', $fresh->rejection_reason);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'payment_verification_failed',
            'recipient_email' => 'rahim@example.com',
            'status' => EmailLog::STATUS_QUEUED,
        ]);
        Queue::assertPushed(SendEmailJob::class, 1);

        // A repeated rejection is a no-op (guard + hidden action).
        $again = app(PaymentReviewService::class)->reject($enrollment->fresh(), 'again');
        $this->assertSame(['changed' => false, 'emailed' => false], $again);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_digital_rejection_stores_reason_and_reapproval_sends_a_new_approval_email(): void
    {
        $this->provider();
        $this->template('product_approved', ['student_name', 'product_title', 'download_link']);
        $this->template('payment_verification_failed', ['student_name', 'order_number', 'product_title', 'amount', 'rejection_reason', 'support_url']);

        $order = $this->pendingOrder();

        Queue::fake([SendEmailJob::class]);

        app(PaymentReviewService::class)->approve($order);
        $this->assertDatabaseCount('email_logs', 1);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->callAction(
                TestAction::make('reject')->table($order->fresh()),
                ['rejection_reason' => 'Invalid sender number'],
            );

        $fresh = $order->fresh();

        $this->assertSame(DigitalOrder::STATUS_REJECTED, $fresh->status);
        $this->assertSame('Invalid sender number', $fresh->rejection_reason);
        $this->assertNull($fresh->approval_email_sent_at);
        $this->assertDatabaseCount('email_logs', 2);

        // Re-approving a rejected order sends a brand-new approval email.
        app(PaymentReviewService::class)->approve($fresh);

        $this->assertSame(DigitalOrder::STATUS_APPROVED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->approval_email_sent_at);
        $this->assertSame(2, EmailLog::query()->where('template_key', 'product_approved')->count());
        Queue::assertPushed(SendEmailJob::class, 3);
    }

    public function test_needs_correction_emails_and_resubmission_returns_to_pending(): void
    {
        $this->provider();
        $this->template('payment_information_needs_attention', ['student_name', 'order_number', 'product_title', 'required_action', 'order_url']);
        $this->template('order_received_payment_pending', ['student_name', 'order_number', 'product_title', 'amount', 'order_url']);

        $user = User::factory()->create();
        $enrollment = $this->pendingEnrollment($this->course(), $user);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callAction(
                TestAction::make('requestCorrection')->table($enrollment),
                ['action_required' => 'Please send the correct transaction screenshot.'],
            );

        $fresh = $enrollment->fresh();

        $this->assertSame('needs_attention', $fresh->payment_status);
        $this->assertSame('pending', $fresh->enrollment_status);
        $this->assertSame('Please send the correct transaction screenshot.', $fresh->attention_reason);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'payment_information_needs_attention',
            'recipient_email' => 'rahim@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.payments.enrollment.edit', $enrollment))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('dashboard.payments.enrollment.resubmit', $enrollment), [
                'payment_method' => 'nagad',
                'transaction_id' => 'NAGAD987654',
                'sender_number' => '01812345678',
            ])
            ->assertRedirect(route('dashboard.index'));

        $resubmitted = $enrollment->fresh();

        $this->assertSame('pending', $resubmitted->payment_status);
        $this->assertSame('pending', $resubmitted->enrollment_status);
        $this->assertNull($resubmitted->attention_reason);
        $this->assertSame('NAGAD987654', $resubmitted->transaction_id);
    }

    public function test_digital_needs_correction_resubmission_returns_to_pending(): void
    {
        $this->provider();
        $this->template('payment_information_needs_attention', ['student_name', 'order_number', 'product_title', 'required_action', 'order_url']);

        $order = $this->pendingOrder();

        Queue::fake([SendEmailJob::class]);

        app(PaymentReviewService::class)->requestCorrection($order, 'Wrong trx id');

        $fresh = $order->fresh();
        $this->assertSame(DigitalOrder::STATUS_PENDING, $fresh->status);
        $this->assertSame('Wrong trx id', $fresh->attention_reason);

        $user = User::factory()->create(['email' => 'rahim@example.com']);
        $this->actingAs($user)
            ->get(route('dashboard.payments.order.edit', $order))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('dashboard.payments.order.resubmit', $order), [
                'trx_id' => 'CORRECT12345',
            ])
            ->assertRedirect(route('dashboard.index'));

        $resubmitted = $order->fresh();

        $this->assertSame(DigitalOrder::STATUS_PENDING, $resubmitted->status);
        $this->assertSame('CORRECT12345', $resubmitted->trx_id);
        $this->assertNull($resubmitted->attention_reason);
    }

    public function test_unpaid_enrollment_cannot_be_completed_or_unlock_access(): void
    {
        $enrollment = $this->pendingEnrollment($this->course());

        $this->assertFalse(app(EnrollmentApprovalService::class)->complete($enrollment));

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->assertTableActionHidden('markCompleted', $enrollment);

        $this->assertSame('pending', $enrollment->fresh()->enrollment_status);
    }

    public function test_generic_edit_cannot_silently_set_in_progress(): void
    {
        $enrollment = $this->pendingEnrollment($this->course());

        Livewire::actingAs($this->admin())
            ->test(EditEnrollment::class, ['record' => $enrollment->getKey()])
            ->fillForm([
                'payment_status' => 'paid',
                'enrollment_status' => 'in_progress',
            ])
            ->call('save');

        $fresh = $enrollment->fresh();

        // Status transitions are locked on the generic edit form: neither the
        // payment nor the enrollment state may change outside the approval
        // pipeline, so no access was silently granted.
        $this->assertSame('pending', $fresh->payment_status);
        $this->assertSame('pending', $fresh->enrollment_status);
        $this->assertNull($fresh->paid_at);
        $this->assertNull($fresh->confirmation_email_sent_at);
    }

    public function test_email_failure_does_not_permanently_consume_the_approval_marker(): void
    {
        // No provider/template: the state approves but no email can queue,
        // and no marker is left behind.
        $enrollment = $this->pendingEnrollment($this->course());

        Queue::fake([SendEmailJob::class]);

        $first = app(PaymentReviewService::class)->approve($enrollment);

        $this->assertSame('paid', $enrollment->fresh()->payment_status);
        $this->assertFalse($first['emailed']);
        $this->assertNull($enrollment->fresh()->confirmation_email_sent_at);
        Queue::assertNothingPushed();

        // Fix the pipeline; re-approving sends the missing confirmation.
        $this->provider();
        $this->template('course_enrollment_confirmation', ['student_name', 'course_title']);

        $second = app(PaymentReviewService::class)->approve($enrollment->fresh());

        $this->assertSame(['changed' => false, 'emailed' => true], $second);
        $this->assertNotNull($enrollment->fresh()->confirmation_email_sent_at);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_course_approval_is_exactly_once_and_bulk_is_one_per_enrollment(): void
    {
        $this->provider();
        $this->template('course_enrollment_confirmation', ['student_name', 'course_title']);

        $course = $this->course();
        $first = $this->pendingEnrollment($course, User::factory()->create(['email' => 'one@example.com']));
        $second = $this->pendingEnrollment($course, User::factory()->create(['email' => 'two@example.com']));

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('markPaid', $first)
            ->callTableBulkAction('bulkMarkPaid', [$second->id]);

        $this->assertDatabaseCount('email_logs', 2);
        $this->assertSame(2, EmailLog::query()->where('template_key', 'course_enrollment_confirmation')->count());
        Queue::assertPushed(SendEmailJob::class, 2);

        // Re-approval of the first enrollment never duplicates.
        $again = app(PaymentReviewService::class)->approve($first->fresh());
        $this->assertSame(['changed' => false, 'emailed' => true], $again);
        Queue::assertPushed(SendEmailJob::class, 2);
    }
}
