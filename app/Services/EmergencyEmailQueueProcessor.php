<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use Illuminate\Contracts\Queue\Factory as QueueManager;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Illuminate\Queue\WorkerOptions;
use InvalidArgumentException;
use Throwable;

/**
 * Emergency, admin-triggered fallback that manually drains pending queued
 * email jobs when the normal queue worker is unavailable.
 *
 * This is deliberately NOT a second email-delivery path. Jobs are reserved
 * with the database queue's native reservation semantics (row locking and
 * the reserved_at lifecycle) and then run through Laravel's own queue
 * Worker, exactly like queue:work does. SendEmailJob therefore still
 * performs the actual provider delivery, so provider selection/failover,
 * daily-limit gating, retry handling, duplicate protection and the EmailLog
 * lifecycle are all preserved unchanged.
 *
 * Normal:
 *     server queue worker → SendEmailJob → Brevo
 * Emergency (global):
 *     Filament action → bounded Laravel queue drain → SendEmailJob → Brevo
 * Emergency (single email):
 *     Filament row action → safely reserve the selected queued job → SendEmailJob → Brevo
 */
class EmergencyEmailQueueProcessor
{
    public function __construct(
        private readonly QueueManager $queue,
    ) {}

    /**
     * Synchronously process up to the configured maximum number of pending
     * jobs from the default database queue. Each pop atomically reserves the
     * next available job (never one a running worker has already reserved),
     * so a concurrent normal worker and this emergency drain cannot
     * double-process a job.
     *
     * @return array{attempted: int, succeeded: int, failed: int, released: int}
     */
    public function drainPendingEmails(?int $maxJobs = null, ?int $maxRunSeconds = null): array
    {
        $connection = $this->databaseQueue();
        $connectionName = (string) config('queue.default');
        $queueName = $this->queueName($connectionName);
        $options = $this->workerOptions($connectionName);

        $maxJobs = $maxJobs ?? (int) config('queue.emergency.max_jobs_per_run', 10);
        $budget = $maxRunSeconds ?? (int) config('queue.emergency.max_run_seconds', 50);

        $attempted = 0;
        $succeeded = 0;
        $failed = 0;
        $released = 0;
        $deadline = microtime(true) + max($budget, 1);

        // queue:work normally stores permanently failed jobs on a JobFailed
        // event; that listener lives on the CLI worker, so register the same
        // queue.failer storage here for the duration of the drain.
        $events = app('events');

        $events->listen(JobFailed::class, static function (JobFailed $event): void {
            app('queue.failer')->log(
                $event->connectionName,
                $event->job->getQueue(),
                $event->job->getRawBody(),
                $event->exception,
            );
        });

        try {
            while ($attempted < $maxJobs && microtime(true) < $deadline) {
                try {
                    $job = $connection->pop($queueName);
                } catch (Throwable $exception) {
                    // pop() already failed any corrupt row it found; anything
                    // else (e.g. a lost DB connection) must stop the drain.
                    report($exception);

                    break;
                }

                if ($job === null) {
                    break; // No pending, ready-to-run jobs remain.
                }

                $attempted++;

                $outcome = $this->processReservedJob($job, $options);

                if ($outcome === 'succeeded') {
                    $succeeded++;
                } elseif ($outcome === 'failed') {
                    $failed++;
                } elseif ($outcome === 'released') {
                    $released++;
                } else {
                    break; // Unexpected infrastructure error — stop the drain.
                }
            }
        } finally {
            $events->forget(JobFailed::class);
        }

        return [
            'attempted' => $attempted,
            'succeeded' => $succeeded,
            'failed' => $failed,
            'released' => $released,
        ];
    }

    /**
     * Process ONLY the queued database job that belongs to the given email
     * log, if one is still available.
     *
     * The job is found without executing anything: its serialized SendEmailJob
     * payload embeds the log's primary key (emailLogId), so the matching
     * row(s) are located with a keyed payload marker, and the payload type is
     * confirmed from the JSON commandName before anything runs.
     *
     * A single atomic guarded UPDATE performs the reservation exactly like
     * DatabaseQueue::pop(): it claims the row only when it is unreserved and
     * ready (or its reservation has expired past retry_after), so a job a
     * normal worker is currently holding is never claimed a second time. The
     * reserved row is then handed to Laravel's own queue Worker, keeping the
     * exact delete/release/fail semantics of queue:work.
     *
     * @return array{claimed: bool, outcome?: 'succeeded'|'failed'|'released'}
     */
    public function processQueuedEmail(EmailLog $emailLog): array
    {
        $fresh = $emailLog->fresh();

        if ($fresh === null || $fresh->status !== EmailLog::STATUS_QUEUED) {
            return ['claimed' => false];
        }

        $connection = $this->databaseQueue();
        $connectionName = (string) config('queue.default');
        $queueName = $this->queueName($connectionName);
        $table = $this->tableName($connectionName);
        $db = $connection->getDatabase();
        $options = $this->workerOptions($connectionName);

        // Only SendEmailJob payloads carry this property, so the serialized
        // property marker (JSON-escaped, as stored in the payload column)
        // uniquely identifies the jobs queued for this log.
        $marker = 's:10:\"emailLogId\";i:'.(int) $fresh->getKey().';';

        $candidateIds = $db->table($table)
            ->where('queue', $queueName)
            ->where('payload', 'like', '%'.$marker.'%')
            ->orderBy('id')
            ->pluck('id');

        $now = now()->getTimestamp();
        $expiredAt = now()->subSeconds((int) config("queue.connections.{$connectionName}.retry_after", 90))->getTimestamp();

        foreach ($candidateIds as $candidateId) {
            $row = $db->table($table)->where('id', $candidateId)->first();

            if ($row === null) {
                continue;
            }

            $payload = json_decode($row->payload, true);

            if (($payload['data']['commandName'] ?? null) !== SendEmailJob::class) {
                continue;
            }

            // Atomic compare-and-set reservation: the availability guard is
            // part of the UPDATE, so two racing claims can never both win.
            $reserved = $db->table($table)
                ->where('id', $candidateId)
                ->where('queue', $queueName)
                ->where(function ($query) use ($now, $expiredAt): void {
                    $query
                        ->where(fn ($available) => $available
                            ->whereNull('reserved_at')
                            ->where('available_at', '<=', $now))
                        ->orWhere('reserved_at', '<=', $expiredAt);
                })
                ->update([
                    'reserved_at' => $now,
                    'attempts' => $db->raw('attempts + 1'),
                ]);

            if ($reserved !== 1) {
                continue; // A normal worker claimed it first.
            }

            $claimedRow = $db->table($table)->where('id', $candidateId)->first();

            $job = new DatabaseJob(
                app(),
                $connection,
                new DatabaseJobRecord((object) $claimedRow),
                $connectionName,
                $queueName,
            );

            return [
                'claimed' => true,
                'outcome' => $this->processReservedJob($job, $options),
            ];
        }

        return ['claimed' => false];
    }

    /**
     * Run one already-reserved job through Laravel's queue worker and report
     * how it ended, mirroring how queue:work treats the job.
     *
     * @return 'succeeded'|'failed'|'released'|'error'
     */
    protected function processReservedJob(Job $job, WorkerOptions $options): string
    {
        try {
            app('queue.worker')->process((string) config('queue.default'), $job, $options);

            return 'succeeded';
        } catch (Throwable $exception) {
            // Laravel's worker releases the job (scheduled retry) or marks it
            // failed (moved to failed_jobs, EmailLog set to failed) before
            // rethrowing. Distinguish those expected outcomes from an
            // unexpected infrastructure error.
            if ($job->isReleased()) {
                return 'released';
            }

            if ($job->hasFailed() || $job->isDeleted()) {
                return 'failed';
            }

            $job->release(0);
            report($exception);

            return 'error';
        }
    }

    protected function databaseQueue(): DatabaseQueue
    {
        $connectionName = (string) config('queue.default');
        $connection = $this->queue->connection($connectionName);

        if (! $connection instanceof DatabaseQueue) {
            throw new InvalidArgumentException('Emergency email processing requires the database queue connection.');
        }

        return $connection;
    }

    protected function queueName(string $connectionName): string
    {
        return (string) config("queue.connections.{$connectionName}.queue", 'default');
    }

    protected function tableName(string $connectionName): string
    {
        return (string) config("queue.connections.{$connectionName}.table", 'jobs');
    }

    protected function workerOptions(string $connectionName): WorkerOptions
    {
        return new WorkerOptions(
            name: 'emergency-email-drain',
            backoff: 0,
            memory: 128,
            timeout: (int) config("queue.connections.{$connectionName}.retry_after", 90),
            sleep: 0,
            // 0 keeps the per-job $tries property (SendEmailJob retries up to
            // 3 times) authoritative, mirroring the default worker behaviour.
            maxTries: 0,
            force: true,
            stopWhenEmpty: false,
            maxJobs: 0,
            maxTime: 0,
        );
    }
}
