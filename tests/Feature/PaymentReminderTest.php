<?php

namespace Tests\Feature;

use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Jobs\SendEmailJob;
use App\Models\Course;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\OrderMaterializer;
use App\Services\PaymentReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function course(string $title = 'HSK 1 Crash Course'): Course
    {
        return Course::create([
            'title' => $title,
            'slug' => str()->slug($title),
            'price' => 9500,
            'is_published' => true,
        ]);
    }

    /**
     * Create an Enrollment with canonical Order/Payment rows. Default state:
     * partially paid (5000 of 9500) and in_progress — canonically eligible.
     */
    protected function enrollmentWithCanonical(array $overrides = []): Enrollment
    {
        $enrollment = Enrollment::create(array_merge([
            'course_id' => $this->course()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 9500,
            'amount_paid' => 5000,
            'amount_due' => 4500,
            'payment_status' => 'partially_paid',
            'enrollment_status' => 'in_progress',
        ], $overrides));

        app(OrderMaterializer::class)->materialize($enrollment);

        return $enrollment;
    }

    protected function reminderTemplate(): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'key' => PaymentReminderService::TEMPLATE_KEY,
            'subject' => 'Payment reminder: {course_title}',
            'body' => '<p>Hi {student_name}, please pay {amount_due} by {due_date}.</p>',
            'variables' => ['student_name', 'course_title', 'amount_due', 'due_date'],
        ]);
    }

    protected function eligiblePartial(): Enrollment
    {
        return $this->enrollmentWithCanonical();
    }

    // -------------------------------------------------------------
    // Canonical eligibility matrix
    // -------------------------------------------------------------

    public function test_pending_full_payment_is_eligible(): void
    {
        $this->providerAndTemplate();
        $enrollment = $this->enrollmentWithCanonical([
            'amount_paid' => 0,
            'amount_due' => 9500,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertNotNull($enrollment->fresh()->payment_reminder_sent_at);
    }

    public function test_partial_payment_is_eligible(): void
    {
        $this->providerAndTemplate();
        $enrollment = $this->eligiblePartial();

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertNotNull($enrollment->fresh()->payment_reminder_sent_at);
    }

    public function test_fully_paid_is_not_eligible(): void
    {
        $this->providerAndTemplate();
        $this->enrollmentWithCanonical([
            'amount_paid' => 9500,
            'amount_due' => 0,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_rejected_is_not_eligible(): void
    {
        $this->providerAndTemplate();
        $this->enrollmentWithCanonical([
            'payment_status' => 'rejected',
            'enrollment_status' => 'cancelled',
            'rejection_reason' => 'Not found',
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_needs_attention_is_not_eligible(): void
    {
        $this->providerAndTemplate();
        $this->enrollmentWithCanonical([
            'payment_status' => 'needs_attention',
            'enrollment_status' => 'pending',
            'attention_reason' => 'Fix reference',
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_completed_course_is_not_eligible(): void
    {
        $this->providerAndTemplate();
        $this->enrollmentWithCanonical([
            'amount_paid' => 9500,
            'amount_due' => 0,
            'payment_status' => 'paid',
            'enrollment_status' => 'completed',
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_cancelled_is_not_eligible(): void
    {
        $this->providerAndTemplate();
        $this->enrollmentWithCanonical([
            'payment_status' => 'partially_paid',
            'enrollment_status' => 'cancelled',
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_refunded_is_not_eligible(): void
    {
        $this->providerAndTemplate();
        $this->enrollmentWithCanonical([
            'amount_paid' => 9500,
            'amount_due' => 0,
            'payment_status' => 'refunded',
            'enrollment_status' => 'in_progress',
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_missing_canonical_order_is_not_eligible_and_never_materialized(): void
    {
        $this->providerAndTemplate();
        Enrollment::create([
            'course_id' => $this->course()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 9500,
            'amount_paid' => 5000,
            'amount_due' => 4500,
            'payment_status' => 'partially_paid',
            'enrollment_status' => 'in_progress',
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_multiple_payment_rows_use_canonical_aggregate_due(): void
    {
        $this->providerAndTemplate();
        $enrollment = $this->eligiblePartial();
        $order = Order::where('legacy_source', 'enrollment')->where('legacy_id', $enrollment->id)->firstOrFail();

        $order->payments()->delete();
        $order->payments()->create(['amount' => 2000, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => now()]);
        $order->payments()->create(['amount' => 3000, 'status' => Payment::STATUS_PAID, 'method' => 'nagad', 'paid_at' => now()]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', ['template_key' => PaymentReminderService::TEMPLATE_KEY]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return str_contains($job->htmlContent, 'pay 4,500.00');
        });
    }

    public function test_legacy_only_payment_values_cannot_make_an_ineligible_canonical_order_eligible(): void
    {
        $this->providerAndTemplate();
        $enrollment = $this->enrollmentWithCanonical([
            'amount_paid' => 9500,
            'amount_due' => 0,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
        ]);

        // Diverging legacy mirror that would have qualified under the old rule.
        DB::table('enrollments')->where('id', $enrollment->id)->update([
            'payment_status' => 'partially_paid',
            'amount_paid' => 5000,
            'amount_due' => 4500,
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    // -------------------------------------------------------------
    // Marker / exactly-once semantics (marker stays on Enrollment)
    // -------------------------------------------------------------

    public function test_a_manual_reminder_queues_exactly_one_email_and_keeps_the_marker(): void
    {
        $this->providerAndTemplate();
        $enrollment = $this->eligiblePartial();

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('sendPaymentReminder', $enrollment)
            ->assertNotified('Payment reminder queued.');

        $fresh = $enrollment->fresh();

        $this->assertNotNull($fresh->payment_reminder_sent_at);
        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);

        $dueDate = $this->canonicalOrder($enrollment)->created_at
            ->addDays(PaymentReminderService::INTERVAL_DAYS)
            ->format('j M Y');

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job) use ($dueDate): bool {
            return $job->to === 'rahim@example.com'
                && $job->templateKey === PaymentReminderService::TEMPLATE_KEY
                && $job->htmlContent === "<p>Hi Rahim Uddin, please pay 4,500.00 by {$dueDate}.</p>";
        });
    }

    public function test_repeating_the_manual_reminder_does_not_send_a_duplicate(): void
    {
        $this->providerAndTemplate();
        $enrollment = $this->eligiblePartial();

        Queue::fake([SendEmailJob::class]);

        $component = Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class);

        $component->callTableAction('sendPaymentReminder', $enrollment);
        $component->callTableAction('sendPaymentReminder', $enrollment)
            ->assertNotified('Reminder already sent recently.');

        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_a_reminder_within_the_cooldown_is_skipped(): void
    {
        $this->providerAndTemplate();
        $enrollment = $this->enrollmentWithCanonical([
            'payment_reminder_sent_at' => now()->subDays(2),
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_a_marker_older_than_seven_days_is_eligible_again(): void
    {
        $this->providerAndTemplate();
        $enrollment = $this->enrollmentWithCanonical([
            'payment_reminder_sent_at' => now()->subDays(8),
        ]);

        Queue::fake([SendEmailJob::class]);
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertNotNull($enrollment->fresh()->payment_reminder_sent_at);
    }

    public function test_repeated_scheduler_runs_are_idempotent(): void
    {
        $this->providerAndTemplate();
        $this->eligiblePartial();

        Queue::fake([SendEmailJob::class]);

        $this->artisan('payment-reminders:send')->assertSuccessful();
        $this->travel(1)->minute();
        $this->artisan('payment-reminders:send')->assertSuccessful();
        $this->travel(2)->days();
        $this->artisan('payment-reminders:send')->assertSuccessful();
        $this->travel(8)->days();
        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 2);
        Queue::assertPushed(SendEmailJob::class, 2);
    }

    public function test_a_missing_template_releases_the_marker_and_retries_later(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $enrollment = $this->eligiblePartial();

        Queue::fake([SendEmailJob::class]);

        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
        $this->assertNull($enrollment->fresh()->payment_reminder_sent_at);

        $this->reminderTemplate();

        $this->artisan('payment-reminders:send')->assertSuccessful();

        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
        $this->assertNotNull($enrollment->fresh()->payment_reminder_sent_at);
    }

    public function test_send_email_job_retries_remain_enabled(): void
    {
        $job = new SendEmailJob(1, 'student@example.com', 'Subject', '<p>Body</p>');

        $this->assertSame(3, $job->tries);
    }

    protected function canonicalOrder(Enrollment $enrollment): Order
    {
        return Order::where('legacy_source', 'enrollment')
            ->where('legacy_id', $enrollment->id)
            ->firstOrFail();
    }

    protected function providerAndTemplate(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->reminderTemplate();
    }
}
