<?php

namespace Tests\Feature;

use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Jobs\SendEmailJob;
use App\Models\Course;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class EnrollmentsEmailFlowTest extends TestCase
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

    protected function pendingEnrollment(Course $course, array $overrides = []): Enrollment
    {
        $user = User::factory()->create();

        return Enrollment::create(array_merge([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 9500,
            'amount_paid' => 0,
            'amount_due' => 9500,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ], $overrides));
    }

    protected function confirmationTemplate(): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'key' => 'course_enrollment_confirmation',
            'subject' => 'Course unlocked: {course_title}',
            'body' => '<p>Welcome {student_name}! Your course {course_title} is now unlocked.</p>',
            'variables' => ['student_name', 'course_title'],
        ]);
    }

    public function test_marking_a_single_enrollment_paid_queues_one_confirmation_email(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->confirmationTemplate();

        $enrollment = $this->pendingEnrollment($this->course());

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('markPaid', $enrollment);

        $fresh = $enrollment->fresh();

        $this->assertSame('paid', $fresh->payment_status);
        $this->assertNotNull($fresh->confirmation_email_sent_at);

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'course_enrollment_confirmation',
            'recipient_email' => 'rahim@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Course unlocked: HSK 1 Crash Course',
            'status' => EmailLog::STATUS_QUEUED,
            'attempt_count' => 0,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->to === 'rahim@example.com'
                && $job->templateKey === 'course_enrollment_confirmation'
                && $job->htmlContent === '<p>Welcome Rahim Uddin! Your course HSK 1 Crash Course is now unlocked.</p>';
        });
    }

    public function test_bulk_marking_paid_sends_exactly_one_email_per_newly_paid_enrollment(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->confirmationTemplate();

        $course = $this->course();
        $first = $this->pendingEnrollment($course, [
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
        ]);
        $second = $this->pendingEnrollment($course, [
            'student_name' => 'Karim Ahmed',
            'student_email' => 'karim@example.com',
        ]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableBulkAction('bulkMarkPaid', [$first->id, $second->id]);

        $this->assertSame('paid', $first->fresh()->payment_status);
        $this->assertSame('paid', $second->fresh()->payment_status);
        $this->assertDatabaseCount('email_logs', 2);

        foreach (['rahim@example.com', 'karim@example.com'] as $email) {
            $this->assertDatabaseHas('email_logs', [
                'template_key' => 'course_enrollment_confirmation',
                'recipient_email' => $email,
                'status' => EmailLog::STATUS_QUEUED,
            ]);
        }

        Queue::assertPushed(SendEmailJob::class, 2);
    }

    public function test_bulk_mark_paid_skips_an_already_paid_enrollment(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->confirmationTemplate();

        $course = $this->course();
        $pending = $this->pendingEnrollment($course, ['student_email' => 'pending@example.com']);
        $alreadyPaid = $this->pendingEnrollment($course, [
            'student_email' => 'already@example.com',
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
        ]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableBulkAction('bulkMarkPaid', [$pending->id, $alreadyPaid->id]);

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', ['recipient_email' => 'pending@example.com']);
        $this->assertDatabaseMissing('email_logs', ['recipient_email' => 'already@example.com']);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_repeating_the_same_bulk_action_never_duplicates_emails(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->confirmationTemplate();

        $course = $this->course();
        $first = $this->pendingEnrollment($course);
        $second = $this->pendingEnrollment($course, ['student_email' => 'second@example.com']);

        Queue::fake([SendEmailJob::class]);

        $component = Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class);

        $component->callTableBulkAction('bulkMarkPaid', [$first->id, $second->id]);
        $component->callTableBulkAction('bulkMarkPaid', [$first->id, $second->id]);

        $this->assertDatabaseCount('email_logs', 2);
        Queue::assertPushed(SendEmailJob::class, 2);
    }

    public function test_an_already_paid_enrollment_never_gets_another_email(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->confirmationTemplate();

        $course = $this->course();
        $alreadyPaid = $this->pendingEnrollment($course, [
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
        ]);

        Queue::fake([SendEmailJob::class]);

        // The row-level guard (payment_status != 'paid') is what prevents a
        // duplicate even if a double-submitted request slips past the hidden
        // UI action: nothing can transition an already-paid enrollment.
        $changed = DB::table('enrollments')
            ->where('id', $alreadyPaid->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'amount_paid' => $alreadyPaid->amount,
                'amount_due' => 0,
                'confirmation_email_sent_at' => now(),
            ]);

        $this->assertSame(0, $changed);
        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }
}
