<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Services\EmailService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SendEmailJobTest extends TestCase
{
    use RefreshDatabase;

    private function queuedLog(array $overrides = []): EmailLog
    {
        return EmailLog::factory()->create(array_merge([
            'provider_id' => null,
            'status' => EmailLog::STATUS_QUEUED,
            'queued_at' => now(),
            'sending_at' => null,
            'sent_at' => null,
            'failed_at' => null,
            'attempt_count' => 0,
            'error_message' => null,
            'message_id' => null,
        ], $overrides));
    }

    private function deliveryMock(): EmailService
    {
        return Mockery::mock(EmailService::class, [app('mail.manager')])->makePartial();
    }

    private function jobFor(EmailLog $log): SendEmailJob
    {
        return new SendEmailJob(
            $log->id,
            'student@example.com',
            'Your course is ready',
            '<p>Hello</p>',
            null,
            null,
            null,
            null,
        );
    }

    public function test_job_walks_the_log_through_queued_sending_and_sent(): void
    {
        $provider = EmailProvider::factory()->create();
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->once();

        $this->jobFor($log)->handle($delivery);

        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'provider_id' => $provider->id,
            'status' => EmailLog::STATUS_SENT,
            'error_message' => null,
        ]);

        $fresh = $log->fresh();

        $this->assertNotNull($fresh->queued_at);
        $this->assertNotNull($fresh->sending_at);
        $this->assertNotNull($fresh->sent_at);
        $this->assertNull($fresh->failed_at);
        $this->assertSame(1, $fresh->attempt_count);
        $this->assertSame(1, $provider->fresh()->sent_today);
    }

    public function test_job_stores_the_provider_message_id_on_success(): void
    {
        $provider = EmailProvider::factory()->create();
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->andReturn('provider-message-id-123@brevo');

        $this->jobFor($log)->handle($delivery);

        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'status' => EmailLog::STATUS_SENT,
            'message_id' => 'provider-message-id-123@brevo',
        ]);
    }

    public function test_job_falls_back_to_the_next_provider_when_the_first_fails(): void
    {
        $first = EmailProvider::factory()->create(['priority' => 1]);
        $second = EmailProvider::factory()->create(['priority' => 2]);
        $log = $this->queuedLog();

        $attempts = 0;

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->andReturnUsing(function () use (&$attempts) {
            $attempts++;

            if ($attempts === 1) {
                throw new Exception('Connection refused');
            }

            return null;
        });

        $this->jobFor($log)->handle($delivery);

        $this->assertSame(2, $attempts);
        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'provider_id' => $second->id,
            'status' => EmailLog::STATUS_SENT,
            'error_message' => null,
        ]);
        $this->assertSame(2, $log->fresh()->attempt_count);
        $this->assertNotNull($log->fresh()->sending_at);
        $this->assertSame(0, $first->fresh()->sent_today);
        $this->assertSame(1, $second->fresh()->sent_today);
    }

    public function test_job_marks_the_log_failed_when_every_provider_fails(): void
    {
        $provider = EmailProvider::factory()->create();
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->andThrow(new Exception('Connection refused'));

        $this->jobFor($log)->handle($delivery);

        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'provider_id' => $provider->id,
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'Connection refused',
        ]);

        $fresh = $log->fresh();

        $this->assertNotNull($fresh->failed_at);
        $this->assertNull($fresh->sent_at);
        $this->assertSame(1, $fresh->attempt_count);
        $this->assertSame(0, $provider->fresh()->sent_today);
    }

    public function test_job_skips_a_provider_that_reached_its_daily_limit(): void
    {
        $limited = EmailProvider::factory()->create(['priority' => 1, 'daily_limit' => 1, 'sent_today' => 1]);
        $backup = EmailProvider::factory()->create(['priority' => 2]);
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->once();

        $this->jobFor($log)->handle($delivery);

        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'provider_id' => $backup->id,
            'status' => EmailLog::STATUS_SENT,
        ]);
        $this->assertSame(1, $limited->fresh()->sent_today);
        $this->assertSame(1, $backup->fresh()->sent_today);
    }

    public function test_job_resets_the_daily_counter_when_the_date_changes(): void
    {
        $provider = EmailProvider::factory()->create([
            'daily_limit' => 10,
            'sent_today' => 9,
            'last_reset' => today()->subDay(),
        ]);
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->once();

        $this->jobFor($log)->handle($delivery);

        $this->assertDatabaseHas('email_logs', ['id' => $log->id, 'status' => EmailLog::STATUS_SENT]);
        $this->assertSame(1, $provider->fresh()->sent_today);
        $this->assertTrue($provider->fresh()->last_reset->isToday());
    }

    public function test_job_marks_the_log_failed_when_no_provider_is_active(): void
    {
        EmailProvider::factory()->create(['is_active' => false]);
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldNotReceive('sendViaProvider');

        $this->jobFor($log)->handle($delivery);

        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'No email providers configured',
        ]);
    }

    public function test_job_marks_the_log_failed_when_every_active_provider_is_over_the_limit(): void
    {
        $provider = EmailProvider::factory()->create(['daily_limit' => 1, 'sent_today' => 1]);
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldNotReceive('sendViaProvider');

        $this->jobFor($log)->handle($delivery);

        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'Every active provider has reached its daily send limit.',
        ]);
        $this->assertSame(1, $provider->fresh()->sent_today);
    }

    public function test_job_returns_silently_when_the_log_row_no_longer_exists(): void
    {
        EmailProvider::factory()->create();

        $delivery = $this->deliveryMock();
        $delivery->shouldNotReceive('sendViaProvider');

        $job = new SendEmailJob(999, 'student@example.com', 'Subject', '<p>Body</p>');
        $job->handle($delivery);

        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_job_prefers_the_highest_priority_active_provider(): void
    {
        EmailProvider::factory()->create(['is_active' => false, 'priority' => 1]);
        $first = EmailProvider::factory()->create(['priority' => 2]);
        $second = EmailProvider::factory()->create(['priority' => 3]);
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->once();

        $this->jobFor($log)->handle($delivery);

        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'provider_id' => $first->id,
            'status' => EmailLog::STATUS_SENT,
        ]);
        $this->assertSame(1, $first->fresh()->sent_today);
        $this->assertSame(0, $second->fresh()->sent_today);
    }

    public function test_a_provider_never_exceeds_its_daily_limit_across_racing_jobs(): void
    {
        $provider = EmailProvider::factory()->create(['daily_limit' => 1]);
        $first = $this->queuedLog();
        $second = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->once();

        $this->jobFor($first)->handle($delivery);
        $this->jobFor($second)->handle($delivery);

        $this->assertSame(EmailLog::STATUS_SENT, $first->fresh()->status);
        $this->assertSame(EmailLog::STATUS_FAILED, $second->fresh()->status);
        $this->assertSame('Every active provider has reached its daily send limit.', $second->fresh()->error_message);
        $this->assertSame(1, $provider->fresh()->sent_today);
    }

    public function test_a_failed_send_releases_the_reserved_daily_slot(): void
    {
        $provider = EmailProvider::factory()->create(['daily_limit' => 3]);
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->andThrow(new Exception('Connection refused'));

        $this->jobFor($log)->handle($delivery);

        $this->assertSame(EmailLog::STATUS_FAILED, $log->fresh()->status);
        $this->assertSame(0, $provider->fresh()->sent_today);
    }

    public function test_job_redacts_credentials_before_storing_the_failure(): void
    {
        $provider = EmailProvider::factory()->create([
            'config' => ['password' => 'hunter2-secret-key'],
        ]);
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldReceive('sendViaProvider')->andThrow(new Exception('SMTP login rejected: hunter2-secret-key'));

        $this->jobFor($log)->handle($delivery);

        $error = $log->fresh()->error_message;

        $this->assertStringContainsString('[REDACTED]', $error);
        $this->assertStringNotContainsString('hunter2-secret-key', $error);
    }

    public function test_a_failed_job_moves_a_stuck_sending_log_to_failed(): void
    {
        $provider = EmailProvider::factory()->create([
            'config' => ['password' => 'hunter2-secret-key'],
        ]);
        $log = EmailLog::factory()->create([
            'provider_id' => $provider->id,
            'status' => EmailLog::STATUS_SENDING,
            'queued_at' => now(),
            'sending_at' => now(),
            'sent_at' => null,
            'failed_at' => null,
            'attempt_count' => 1,
        ]);

        $this->jobFor($log)->failed(new Exception('Worker crashed mid-send: hunter2-secret-key'));

        $fresh = $log->fresh();

        $this->assertSame(EmailLog::STATUS_FAILED, $fresh->status);
        $this->assertNotNull($fresh->failed_at);
        $this->assertNull($fresh->sent_at);
        $this->assertStringContainsString('[REDACTED]', $fresh->error_message);
        $this->assertStringNotContainsString('hunter2-secret-key', $fresh->error_message);
    }

    public function test_all_over_limit_failures_record_failed_at_without_an_attempt(): void
    {
        $provider = EmailProvider::factory()->create(['daily_limit' => 1, 'sent_today' => 1]);
        $log = $this->queuedLog();

        $delivery = $this->deliveryMock();
        $delivery->shouldNotReceive('sendViaProvider');

        $this->jobFor($log)->handle($delivery);

        $fresh = $log->fresh();

        $this->assertSame(EmailLog::STATUS_FAILED, $fresh->status);
        $this->assertNotNull($fresh->failed_at);
        $this->assertSame(0, $fresh->attempt_count);
        $this->assertSame(1, $provider->fresh()->sent_today);
    }

    public function test_a_retried_job_never_delivers_an_email_whose_log_is_already_sent(): void
    {
        $provider = EmailProvider::factory()->create();
        $log = EmailLog::factory()->create([
            'provider_id' => $provider->id,
            'status' => EmailLog::STATUS_SENT,
            'queued_at' => now(),
            'sending_at' => now()->subMinutes(1),
            'sent_at' => now(),
            'failed_at' => null,
            'attempt_count' => 1,
            'error_message' => null,
        ]);

        $delivery = $this->deliveryMock();
        $delivery->shouldNotReceive('sendViaProvider');

        $this->jobFor($log)->handle($delivery);

        $fresh = $log->fresh();

        $this->assertSame(EmailLog::STATUS_SENT, $fresh->status);
        $this->assertSame(1, $fresh->attempt_count);
        $this->assertSame(0, $provider->fresh()->sent_today);
    }
}
