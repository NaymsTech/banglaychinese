<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class EmailLogsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_view_the_read_only_log_list(): void
    {
        $provider = EmailProvider::factory()->create(['name' => 'Brevo']);

        EmailLog::factory()->create([
            'provider_id' => $provider->id,
            'recipient_email' => 'student@example.com',
            'subject' => 'Your course is ready',
            'status' => EmailLog::STATUS_SENT,
        ]);

        $this->actingAs($this->admin())
            ->get(ListEmailLogs::getUrl())
            ->assertOk()
            ->assertSee('student@example.com')
            ->assertSee('Your course is ready')
            ->assertSee('Brevo');
    }

    public function test_admin_can_filter_logs_by_status(): void
    {
        $sent = EmailLog::factory()->create([
            'recipient_email' => 'sent@example.com',
            'status' => EmailLog::STATUS_SENT,
        ]);
        $failed = EmailLog::factory()->create([
            'recipient_email' => 'failed@example.com',
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'Connection refused',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->filterTable('status', EmailLog::STATUS_SENT)
            ->assertCanSeeTableRecords([$sent])
            ->assertCanNotSeeTableRecords([$failed]);
    }

    public function test_admin_can_filter_logs_by_provider(): void
    {
        $brevo = EmailProvider::factory()->create(['name' => 'Brevo']);
        $sendgrid = EmailProvider::factory()->create(['name' => 'SendGrid']);

        $brevoLog = EmailLog::factory()->create([
            'provider_id' => $brevo->id,
            'recipient_email' => 'brevo@example.com',
        ]);
        $sendgridLog = EmailLog::factory()->create([
            'provider_id' => $sendgrid->id,
            'recipient_email' => 'sendgrid@example.com',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->filterTable('provider', (string) $brevo->id)
            ->assertCanSeeTableRecords([$brevoLog])
            ->assertCanNotSeeTableRecords([$sendgridLog]);
    }

    public function test_admin_can_filter_logs_by_template_key(): void
    {
        $approved = EmailLog::factory()->create([
            'recipient_email' => 'approved@example.com',
            'template_key' => 'product_approved',
        ]);
        $welcome = EmailLog::factory()->create([
            'recipient_email' => 'welcome@example.com',
            'template_key' => 'welcome_email',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->filterTable('template_key', 'product_approved')
            ->assertCanSeeTableRecords([$approved])
            ->assertCanNotSeeTableRecords([$welcome]);
    }

    public function test_admin_can_search_logs_by_recipient(): void
    {
        $match = EmailLog::factory()->create(['recipient_email' => 'student@example.com']);
        $other = EmailLog::factory()->create(['recipient_email' => 'someone-else@example.com']);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->searchTable('student@example.com')
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_admin_can_filter_logs_by_queued_date_range(): void
    {
        $inRange = EmailLog::factory()->create([
            'recipient_email' => 'recent@example.com',
            'queued_at' => now()->subDays(2),
        ]);
        $outside = EmailLog::factory()->create([
            'recipient_email' => 'old@example.com',
            'queued_at' => now()->subDays(40),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->filterTable('queued_at_range', [
                'queued_from' => now()->subDays(7)->toDateString(),
                'queued_until' => now()->toDateString(),
            ])
            ->assertCanSeeTableRecords([$inRange])
            ->assertCanNotSeeTableRecords([$outside]);
    }

    public function test_admin_can_open_the_detail_view_modal_for_a_log(): void
    {
        $log = EmailLog::factory()->create([
            'recipient_email' => 'student@example.com',
            'subject' => 'Your course is ready',
            'body' => '<p>Hello there</p>',
            'template_key' => 'product_approved',
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'Connection refused',
            'attempt_count' => 2,
            'message_id' => null,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->mountTableAction('view', $log);

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertSame(2, $log->fresh()->attempt_count);
    }

    public function test_admin_can_resend_a_failed_log_as_a_new_queued_attempt(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        $failed = EmailLog::factory()->create([
            'recipient_email' => 'student@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Your download is ready',
            'body' => '<p>Download it here: https://example.com/dl</p>',
            'template_key' => 'product_approved',
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'Connection refused',
            'attempt_count' => 2,
        ]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->callAction(TestAction::make('resend')->table($failed))
            ->assertNotified('Resend queued');

        $this->assertDatabaseCount('email_logs', 2);

        $original = $failed->fresh();
        $this->assertSame(EmailLog::STATUS_FAILED, $original->status);
        $this->assertSame('Connection refused', $original->error_message);
        $this->assertSame(2, $original->attempt_count);

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'student@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Your download is ready',
            'body' => '<p>Download it here: https://example.com/dl</p>',
            'template_key' => 'product_approved',
            'status' => EmailLog::STATUS_QUEUED,
            'attempt_count' => 0,
            'error_message' => null,
        ]);

        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_a_successful_email_cannot_be_resent_from_the_log_list(): void
    {
        EmailLog::factory()->create([
            'recipient_email' => 'student@example.com',
            'body' => '<p>Sent fine</p>',
            'status' => EmailLog::STATUS_SENT,
        ]);

        // The resend action is only rendered for failed logs with a stored
        // body, so a successful email offers no way to duplicate it.
        $this->actingAs($this->admin())
            ->get(ListEmailLogs::getUrl())
            ->assertOk()
            ->assertDontSee('Resend');
    }
}
