<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\User;
use App\Services\EmailService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class EmailLogsProcessNowActionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    private function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'is_admin' => false,
        ]);
    }

    private function log(string $status): EmailLog
    {
        return EmailLog::factory()->create([
            'recipient_email' => 'student@example.com',
            'subject' => 'Your course is ready',
            'body' => '<p>Hello</p>',
            'status' => $status,
        ]);
    }

    private function useDatabaseQueue(): void
    {
        config()->set('queue.default', 'database');
    }

    private function queueEmails(int $count): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        $service = app(EmailService::class);

        for ($i = 1; $i <= $count; $i++) {
            $service->send("student{$i}@example.com", "Subject {$i}", '<p>Hello</p>');
        }
    }

    private function deliveryDouble(): EmailService
    {
        $service = Mockery::mock(EmailService::class, [app('mail.manager')])->makePartial();

        app()->instance(EmailService::class, $service);

        return $service;
    }

    public function test_process_now_is_visible_for_a_queued_email(): void
    {
        $queued = $this->log(EmailLog::STATUS_QUEUED);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->assertTableActionVisible('processNow', $queued);
    }

    public function test_process_now_is_not_visible_for_a_sent_email(): void
    {
        $sent = $this->log(EmailLog::STATUS_SENT);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->assertTableActionHidden('processNow', $sent);
    }

    public function test_process_now_is_not_visible_for_a_sending_email(): void
    {
        $sending = $this->log(EmailLog::STATUS_SENDING);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->assertTableActionHidden('processNow', $sending);
    }

    public function test_a_failed_email_keeps_resend_instead_of_process_now(): void
    {
        $failed = $this->log(EmailLog::STATUS_FAILED);

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->assertTableActionHidden('processNow', $failed)
            ->assertTableActionVisible('resend', $failed);
    }

    public function test_process_now_delivers_only_the_selected_email(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(2);

        $selected = EmailLog::query()->orderBy('id')->first();
        $other = EmailLog::query()->orderBy('id')->skip(1)->first();
        $this->assertDatabaseCount('jobs', 2);

        $delivery = $this->deliveryDouble();
        $delivery->shouldReceive('sendViaProvider')->once()->andReturn('message-id@brevo');
        // The Filament row action must never queue or deliver email itself —
        // only the selected queued SendEmailJob may reach the provider.
        $delivery->shouldNotReceive('send', 'sendTemplate', 'resend', 'testConnection');

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->callAction(TestAction::make('processNow')->table($selected))
            ->assertNotified('Email processed successfully');

        $this->assertSame(EmailLog::STATUS_SENT, $selected->fresh()->status);
        $this->assertSame(EmailLog::STATUS_QUEUED, $other->fresh()->status);
        $this->assertDatabaseCount('jobs', 1);

        $remaining = DB::table('jobs')->first();
        $this->assertStringContainsString('s:10:\"emailLogId\";i:'.(int) $other->getKey().';', $remaining->payload);
    }

    public function test_process_now_does_not_claim_a_job_a_worker_is_already_processing(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(1);

        $log = EmailLog::query()->firstOrFail();
        DB::table('jobs')->update([
            'reserved_at' => now()->getTimestamp(),
            'attempts' => 1,
        ]);

        $delivery = $this->deliveryDouble();
        $delivery->shouldNotReceive('sendViaProvider');

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->callAction(TestAction::make('processNow')->table($log))
            ->assertNotified('Email already being processed');

        $this->assertSame(EmailLog::STATUS_QUEUED, $log->fresh()->status);
        $this->assertDatabaseCount('jobs', 1);
    }

    public function test_process_now_reports_safely_when_the_queued_job_is_no_longer_available(): void
    {
        $this->useDatabaseQueue();

        // A queued log whose job row is gone (worker already took it, or the
        // queue was drained) must never trigger a second delivery.
        $log = $this->log(EmailLog::STATUS_QUEUED);

        $delivery = $this->deliveryDouble();
        $delivery->shouldNotReceive('sendViaProvider');

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->callAction(TestAction::make('processNow')->table($log))
            ->assertNotified('Email already being processed');

        $this->assertSame(EmailLog::STATUS_QUEUED, $log->fresh()->status);
    }

    public function test_a_non_admin_user_cannot_access_the_page_that_hosts_the_action(): void
    {
        $this->actingAs($this->student())
            ->get(ListEmailLogs::getUrl())
            ->assertForbidden();
    }
}
