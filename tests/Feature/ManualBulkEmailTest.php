<?php

namespace Tests\Feature;

use App\Filament\Pages\SendManualEmail;
use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\User;
use App\Services\EmailService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ManualBulkEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function template(): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'name' => 'Product Download Ready',
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}, your download is ready',
            'body' => '<p>Download it here: {download_link}</p>',
            'variables' => ['student_name', 'download_link'],
        ]);
    }

    public function test_a_single_manual_email_creates_one_log_and_one_queued_job(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template();

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'single',
                'recipient_email' => 'student@example.com',
                'variables' => [
                    'student_name' => 'Rahim Uddin',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertHasNoFormErrors()
            ->assertNotified('Emails Sent Successfully!');

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'product_approved',
            'recipient_email' => 'student@example.com',
            'subject' => 'Hi Rahim Uddin, your download is ready',
            'status' => EmailLog::STATUS_QUEUED,
            'attempt_count' => 0,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->to === 'student@example.com'
                && $job->templateKey === 'product_approved'
                && $job->htmlContent === '<p>Download it here: https://example.com/downloads/hsk1</p>';
        });
    }

    public function test_a_bulk_send_queues_one_independent_delivery_per_recipient(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template();

        Lead::factory()->create(['email' => 'one@example.com', 'is_subscribed' => true]);
        Lead::factory()->create(['email' => 'two@example.com', 'is_subscribed' => true]);
        Lead::factory()->create(['email' => 'optedout@example.com', 'is_subscribed' => false]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'all',
                'variables' => [
                    'student_name' => 'Student',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertNotified('Emails Sent Successfully!');

        $this->assertDatabaseCount('email_logs', 2);

        foreach (['one@example.com', 'two@example.com'] as $email) {
            $this->assertDatabaseHas('email_logs', [
                'template_key' => 'product_approved',
                'recipient_email' => $email,
                'status' => EmailLog::STATUS_QUEUED,
                'attempt_count' => 0,
            ]);
        }

        // One job per recipient — nothing was delivered synchronously and no
        // batch job containing every recipient was created.
        Queue::assertPushed(SendEmailJob::class, 2);
        $this->assertSame(2, EmailLog::where('status', EmailLog::STATUS_QUEUED)->count());
    }

    public function test_a_duplicate_submission_of_the_same_send_is_skipped(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template();

        Queue::fake([SendEmailJob::class]);

        $component = Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class);

        $form = [
            'template_id' => $template->id,
            'target_audience' => 'single',
            'recipient_email' => 'student@example.com',
            'variables' => [
                'student_name' => 'Rahim Uddin',
                'download_link' => 'https://example.com/downloads/hsk1',
            ],
        ];

        $component->fillForm($form)->call('send')->assertNotified('Emails Sent Successfully!');
        $component->fillForm($form)->call('send')->assertNotified('No New Emails Queued');

        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_a_non_admin_user_cannot_use_the_manual_email_page(): void
    {
        $user = User::factory()->create(['role' => 'student', 'is_admin' => false]);

        $this->actingAs($user)
            ->get(SendManualEmail::getUrl())
            ->assertForbidden();
    }

    public function test_an_invalid_recipient_address_is_rejected_before_any_send(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template();

        // Only an invalid lead address: it must be rejected by the send
        // guard without queueing anything.
        Lead::factory()->create(['email' => 'not-an-email', 'is_subscribed' => true]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'all',
                'variables' => [
                    'student_name' => 'Student',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertNotified('Invalid Recipient Email')
            ->assertNotNotified('Emails Sent Successfully!');

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_a_missing_or_inactive_template_aborts_before_sending(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template();

        Queue::fake([SendEmailJob::class]);

        $component = Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class);

        // Select an active template, then deactivate it before submitting —
        // the exact race a concurrent edit could produce.
        $component->fillForm([
            'template_id' => $template->id,
            'target_audience' => 'single',
            'recipient_email' => 'student@example.com',
            'variables' => [
                'student_name' => 'Rahim Uddin',
                'download_link' => 'https://example.com/downloads/hsk1',
            ],
        ]);

        $template->update(['is_active' => false]);

        // The select validates against currently-active templates, so the
        // submission is rejected with a clear, admin-visible error before
        // anything is queued.
        $component->call('send')
            ->assertHasFormErrors(['template_id'])
            ->assertNotNotified('Emails Sent Successfully!');

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_a_failure_for_one_recipient_does_not_affect_the_others(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template();

        Lead::factory()->create(['email' => 'ok@example.com', 'is_subscribed' => true]);
        Lead::factory()->create(['email' => 'fail@example.com', 'is_subscribed' => true]);

        $original = app(EmailService::class);

        $service = Mockery::mock(EmailService::class, [app('mail.manager')])->makePartial();
        $service->shouldReceive('sendTemplate')->andReturnUsing(function (...$arguments) use ($original) {
            if (($arguments[1] ?? null) === 'fail@example.com') {
                throw new Exception('boom: template rendering failed');
            }

            return $original->sendTemplate(...$arguments);
        });

        $this->app->instance(EmailService::class, $service);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'all',
                'variables' => [
                    'student_name' => 'Student',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertNotified('Partially Successful');

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'ok@example.com',
            'status' => EmailLog::STATUS_QUEUED,
        ]);
        $this->assertDatabaseMissing('email_logs', ['recipient_email' => 'fail@example.com']);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->to === 'ok@example.com';
        });
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_queue_retries_stay_enabled_for_manual_deliveries(): void
    {
        $job = new SendEmailJob(1, 'student@example.com', 'Subject', '<p>Body</p>');

        $this->assertSame(3, $job->tries);
    }
}
