<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Services\EmailService;
use App\Services\EmergencyEmailQueueProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Mockery;
use Tests\Fixtures\AlwaysFailsJob;
use Tests\TestCase;

class EmergencyEmailQueueProcessorTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /**
     * Point the default queue at the real database driver so jobs are stored
     * in the jobs table exactly like production instead of running inline.
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

    /**
     * Swap the container's EmailService for a partial mock so a real SMTP
     * call can never happen during a drain; delivery is proven through the
     * recorded expectations instead.
     */
    private function deliveryDouble(): EmailService
    {
        $service = Mockery::mock(EmailService::class, [app('mail.manager')])->makePartial();

        app()->instance(EmailService::class, $service);

        return $service;
    }

    private function processor(): EmergencyEmailQueueProcessor
    {
        return new EmergencyEmailQueueProcessor(app('queue'));
    }

    public function test_drain_requires_the_database_queue_connection(): void
    {
        config()->set('queue.default', 'sync');

        $this->expectException(InvalidArgumentException::class);

        $this->processor()->drainPendingEmails();
    }

    public function test_pending_jobs_are_processed_through_send_email_job(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(3);
        $this->assertDatabaseCount('jobs', 3);

        $delivery = $this->deliveryDouble();
        $delivery->shouldReceive('sendViaProvider')->times(3)->andReturn('message-id@brevo');
        // The drain must never queue new emails or touch a provider directly;
        // only the queued SendEmailJob may reach the delivery method.
        $delivery->shouldNotReceive('send', 'sendTemplate', 'resend', 'testConnection');

        $result = $this->processor()->drainPendingEmails();

        $this->assertSame(['attempted' => 3, 'succeeded' => 3, 'failed' => 0, 'released' => 0], $result);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('email_logs', 3);

        EmailLog::query()->each(function (EmailLog $log): void {
            $this->assertSame(EmailLog::STATUS_SENT, $log->status);
            $this->assertNotNull($log->sent_at);
            $this->assertNull($log->failed_at);
            $this->assertSame(1, $log->attempt_count);
            $this->assertSame('message-id@brevo', $log->message_id);
        });
    }

    public function test_processing_is_bounded_by_the_configured_maximum(): void
    {
        $this->useDatabaseQueue();
        config()->set('queue.emergency.max_jobs_per_run', 2);
        $this->queueEmails(5);
        $this->assertDatabaseCount('jobs', 5);

        $this->deliveryDouble()
            ->shouldReceive('sendViaProvider')
            ->times(2)
            ->andReturnNull();

        $result = $this->processor()->drainPendingEmails();

        $this->assertSame(2, $result['attempted']);
        $this->assertSame(2, $result['succeeded']);
        $this->assertDatabaseCount('jobs', 3);
        $this->assertDatabaseCount('email_logs', 5);
        $this->assertSame(2, EmailLog::query()->where('status', EmailLog::STATUS_SENT)->count());
        $this->assertSame(3, EmailLog::query()->where('status', EmailLog::STATUS_QUEUED)->count());
    }

    public function test_no_pending_jobs_returns_a_safe_empty_result(): void
    {
        $this->useDatabaseQueue();

        $result = $this->processor()->drainPendingEmails();

        $this->assertSame(['attempted' => 0, 'succeeded' => 0, 'failed' => 0, 'released' => 0], $result);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_a_job_reserved_by_a_concurrent_worker_is_not_processed_again(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(2);

        // Simulate the normal worker currently holding the first job.
        $first = DB::table('jobs')->orderBy('id')->first();
        DB::table('jobs')->where('id', $first->id)->update([
            'reserved_at' => now()->getTimestamp(),
            'attempts' => 1,
        ]);

        $this->deliveryDouble()
            ->shouldReceive('sendViaProvider')
            ->once()
            ->andReturnNull();

        $result = $this->processor()->drainPendingEmails();

        // Only the unreserved job may run; the reserved one is left untouched.
        $this->assertSame(1, $result['attempted']);
        $this->assertSame(1, $result['succeeded']);
        $this->assertDatabaseCount('jobs', 1);

        $remaining = DB::table('jobs')->first();
        $this->assertSame($first->id, $remaining->id);
        $this->assertNotNull($remaining->reserved_at);
        $this->assertSame(1, $remaining->attempts);

        $sent = EmailLog::query()->where('status', EmailLog::STATUS_SENT)->get();
        $queued = EmailLog::query()->where('status', EmailLog::STATUS_QUEUED)->get();

        $this->assertCount(1, $sent);
        $this->assertCount(1, $queued);
    }

    public function test_an_expired_reserved_job_is_reclaimed_by_the_drain(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(1);

        // A worker that died mid-job leaves the reservation behind; once it is
        // older than retry_after the job is eligible again, exactly as the
        // normal worker treats it.
        DB::table('jobs')->update([
            'reserved_at' => now()->subSeconds(config('queue.connections.database.retry_after') + 90)->getTimestamp(),
            'attempts' => 1,
        ]);

        $this->deliveryDouble()
            ->shouldReceive('sendViaProvider')
            ->once()
            ->andReturnNull();

        $result = $this->processor()->drainPendingEmails();

        $this->assertSame(1, $result['attempted']);
        $this->assertSame(1, $result['succeeded']);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(EmailLog::STATUS_SENT, EmailLog::query()->firstOrFail()->status);
    }

    public function test_a_failing_job_is_released_for_retry_when_attempts_remain(): void
    {
        $this->useDatabaseQueue();

        dispatch(new AlwaysFailsJob);
        $this->assertDatabaseCount('jobs', 1);

        $result = $this->processor()->drainPendingEmails(maxJobs: 1);

        $this->assertSame(['attempted' => 1, 'succeeded' => 0, 'failed' => 0, 'released' => 1], $result);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('failed_jobs', 0);

        $released = DB::table('jobs')->first();
        $this->assertNull($released->reserved_at);
        $this->assertSame(1, $released->attempts);
    }

    public function test_a_job_that_exceeds_its_max_attempts_is_marked_failed(): void
    {
        $this->useDatabaseQueue();

        $job = new AlwaysFailsJob;
        $job->tries = 1;
        dispatch($job);

        $result = $this->processor()->drainPendingEmails(maxJobs: 1);

        $this->assertSame(['attempted' => 1, 'succeeded' => 0, 'failed' => 1, 'released' => 0], $result);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 1);

        $failed = DB::table('failed_jobs')->first();
        $this->assertSame('database', $failed->connection);
        $this->assertSame(AlwaysFailsJob::class, json_decode($failed->payload, true)['displayName']);
        $this->assertStringContainsString('Simulated delivery failure.', $failed->exception);
    }

    public function test_a_single_queued_email_can_be_processed_without_touching_other_jobs(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(2);

        $first = EmailLog::query()->orderBy('id')->first();
        $second = EmailLog::query()->orderBy('id')->skip(1)->first();

        $this->deliveryDouble()
            ->shouldReceive('sendViaProvider')
            ->once()
            ->andReturn('message-id@brevo');

        $result = $this->processor()->processQueuedEmail($first);

        $this->assertSame(['claimed' => true, 'outcome' => 'succeeded'], $result);
        $this->assertSame(EmailLog::STATUS_SENT, $first->fresh()->status);
        $this->assertSame(EmailLog::STATUS_QUEUED, $second->fresh()->status);
        $this->assertDatabaseCount('jobs', 1);

        // The remaining job must belong to the untouched second email.
        $remaining = DB::table('jobs')->first();
        $marker = 's:10:\"emailLogId\";i:'.(int) $second->getKey().';';
        $this->assertStringContainsString($marker, $remaining->payload);
        $this->assertStringNotContainsString('s:10:\"emailLogId\";i:'.(int) $first->getKey().';', $remaining->payload);
    }

    public function test_a_single_email_with_no_matching_job_is_not_claimed(): void
    {
        $this->useDatabaseQueue();

        $log = EmailLog::factory()->create([
            'status' => EmailLog::STATUS_QUEUED,
            'queued_at' => now(),
            'attempt_count' => 0,
        ]);

        $delivery = $this->deliveryDouble();
        $delivery->shouldNotReceive('sendViaProvider');

        $result = $this->processor()->processQueuedEmail($log);

        $this->assertSame(['claimed' => false], $result);
        $this->assertSame(EmailLog::STATUS_QUEUED, $log->fresh()->status);
    }

    public function test_a_job_reserved_by_a_concurrent_worker_cannot_be_claimed_twice(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(1);

        $log = EmailLog::query()->firstOrFail();

        // The normal worker holds this job right now.
        DB::table('jobs')->update([
            'reserved_at' => now()->getTimestamp(),
            'attempts' => 1,
        ]);

        $delivery = $this->deliveryDouble();
        $delivery->shouldNotReceive('sendViaProvider');

        $result = $this->processor()->processQueuedEmail($log);

        $this->assertSame(['claimed' => false], $result);
        $this->assertSame(EmailLog::STATUS_QUEUED, $log->fresh()->status);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertNotNull(DB::table('jobs')->first()->reserved_at);
    }

    public function test_an_expired_reservation_for_the_selected_email_is_reclaimed(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(1);

        $log = EmailLog::query()->firstOrFail();

        DB::table('jobs')->update([
            'reserved_at' => now()->subSeconds(config('queue.connections.database.retry_after') + 90)->getTimestamp(),
            'attempts' => 1,
        ]);

        $this->deliveryDouble()
            ->shouldReceive('sendViaProvider')
            ->once()
            ->andReturnNull();

        $result = $this->processor()->processQueuedEmail($log);

        $this->assertSame(['claimed' => true, 'outcome' => 'succeeded'], $result);
        $this->assertSame(EmailLog::STATUS_SENT, $log->fresh()->status);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_an_already_processed_email_cannot_be_processed_again(): void
    {
        $this->useDatabaseQueue();
        $this->queueEmails(1);

        $log = EmailLog::query()->firstOrFail();

        $this->deliveryDouble()
            ->shouldReceive('sendViaProvider')
            ->once()
            ->andReturnNull();

        $this->processor()->processQueuedEmail($log);

        $this->assertSame(EmailLog::STATUS_SENT, $log->fresh()->status);
        $this->assertDatabaseCount('jobs', 0);

        $delivery = $this->deliveryDouble();
        $delivery->shouldNotReceive('sendViaProvider');

        $second = $this->processor()->processQueuedEmail($log);

        $this->assertSame(['claimed' => false], $second);
        $this->assertDatabaseCount('jobs', 0);
    }
}
