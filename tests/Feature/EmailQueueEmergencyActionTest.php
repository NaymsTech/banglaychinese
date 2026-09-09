<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class EmailQueueEmergencyActionTest extends TestCase
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

    /**
     * Point the default queue at the real database driver so the admin action
     * drains jobs from the jobs table exactly like production.
     */
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

    public function test_admin_sees_the_process_pending_emails_action_on_the_email_logs_page(): void
    {
        $this->actingAs($this->admin())
            ->get(ListEmailLogs::getUrl())
            ->assertOk()
            ->assertSee('Process Pending Emails');
    }

    public function test_a_non_admin_user_cannot_reach_the_page_that_hosts_the_action(): void
    {
        $this->actingAs($this->student())
            ->get(ListEmailLogs::getUrl())
            ->assertForbidden();
    }

    public function test_admin_can_process_pending_emails_from_the_page(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(2);
        $this->assertDatabaseCount('jobs', 2);

        $delivery = $this->deliveryDouble();
        $delivery->shouldReceive('sendViaProvider')->times(2)->andReturn('message-id@brevo');
        // The Filament layer must not queue or deliver email itself — delivery
        // happens only through the queued SendEmailJob.
        $delivery->shouldNotReceive('send', 'sendTemplate', 'resend', 'testConnection');

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->callAction('processPendingEmails')
            ->assertNotified('Processed 2 pending email jobs');

        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(2, EmailLog::query()->where('status', EmailLog::STATUS_SENT)->count());
    }

    public function test_action_shows_a_notice_when_there_are_no_pending_jobs(): void
    {
        $this->useDatabaseQueue();

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->callAction('processPendingEmails')
            ->assertNotified('No pending email jobs');

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_action_only_processes_the_configured_maximum_per_click(): void
    {
        $this->useDatabaseQueue();
        config()->set('queue.emergency.max_jobs_per_run', 2);
        $this->queueEmails(5);
        $this->assertDatabaseCount('jobs', 5);

        $this->deliveryDouble()
            ->shouldReceive('sendViaProvider')
            ->times(2)
            ->andReturnNull();

        Livewire::actingAs($this->admin())
            ->test(ListEmailLogs::class)
            ->callAction('processPendingEmails')
            ->assertNotified('Processed 2 pending email jobs');

        $this->assertDatabaseCount('jobs', 3);
        $this->assertSame(2, EmailLog::query()->where('status', EmailLog::STATUS_SENT)->count());
        $this->assertSame(3, EmailLog::query()->where('status', EmailLog::STATUS_QUEUED)->count());
    }
}
