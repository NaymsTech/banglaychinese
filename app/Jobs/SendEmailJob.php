<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $emailLogId,
        public string $to,
        public string $subject,
        public string $htmlContent,
        public ?string $templateKey = null,
        public ?string $recipientName = null,
        public ?string $fromAddress = null,
        public ?string $fromName = null,
    ) {}

    /**
     * Deliver the email through the highest-priority active provider,
     * falling back to the next provider whenever one fails.
     *
     * The email log row is walked through the queued → sending → sent/failed
     * lifecycle inside the queue worker. A provider under a daily limit
     * reserves its slot atomically before the send, so concurrent workers
     * can never push a provider past its limit.
     */
    public function handle(EmailService $emailService): void
    {
        $log = EmailLog::find($this->emailLogId);

        if ($log === null) {
            return;
        }

        // A retried job (e.g. the worker died just after the previous attempt
        // finished) must never deliver the same email twice. Once the log row
        // reports 'sent', any further run is a no-op.
        if ($log->status === EmailLog::STATUS_SENT) {
            return;
        }

        $providers = EmailProvider::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        if ($providers->isEmpty()) {
            $this->markFailed($log, 'No email providers configured');

            return;
        }

        $lastError = null;

        foreach ($providers as $provider) {
            $this->resetDailyUsageIfNeeded($provider);

            $hasDailyLimit = $provider->daily_limit > 0;

            if ($hasDailyLimit && ! $this->reserveDailySlot($provider)) {
                $lastError ??= 'Every active provider has reached its daily send limit.';

                continue;
            }

            $log->update([
                'provider_id' => $provider->id,
                'status' => EmailLog::STATUS_SENDING,
                'sending_at' => now(),
                'attempt_count' => $log->attempt_count + 1,
            ]);

            try {
                $messageId = $emailService->sendViaProvider(
                    $provider,
                    $this->to,
                    $this->subject,
                    $this->htmlContent,
                    $this->recipientName,
                    $this->fromAddress,
                    $this->fromName,
                );

                if (! $hasDailyLimit) {
                    $provider->increment('sent_today');
                }

                $log->update([
                    'status' => EmailLog::STATUS_SENT,
                    'error_message' => null,
                    'sent_at' => now(),
                    'failed_at' => null,
                    'message_id' => $messageId,
                ]);

                return;
            } catch (Throwable $exception) {
                if ($hasDailyLimit) {
                    $this->releaseDailySlot($provider);
                }

                $lastError = $provider->redactSecrets($exception->getMessage() ?? 'The email could not be sent.');

                Log::warning('Email provider failed, falling back to the next provider.', [
                    'provider' => $provider->name,
                    'recipient' => $this->to,
                    'email_log_id' => $this->emailLogId,
                    'error' => $lastError,
                ]);
            }
        }

        $this->markFailed($log, $lastError ?? 'No active email providers could deliver the email.');
    }

    /**
     * Handle a job failure, ensuring the log row is never left in a
     * half-sent state when an unexpected error escapes handle().
     */
    public function failed(?Throwable $exception): void
    {
        $log = EmailLog::find($this->emailLogId);

        if ($log === null) {
            return;
        }

        $provider = $log->provider;
        $message = $exception?->getMessage() ?? 'The queued email could not be delivered.';

        $this->markFailed($log, $provider?->redactSecrets($message) ?? $message);
    }

    /**
     * Record the final failure state on the log row.
     */
    protected function markFailed(EmailLog $log, string $errorMessage): void
    {
        $log->update([
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => $errorMessage,
            'sent_at' => null,
            'failed_at' => now(),
        ]);

        Log::error('Email delivery failed.', [
            'recipient' => $this->to,
            'email_log_id' => $this->emailLogId,
            'error' => $errorMessage,
        ]);
    }

    /**
     * Atomically claim one of the provider's remaining daily slots.
     *
     * The conditional increment means two workers racing for the last slot
     * can never both succeed, because the UPDATE re-evaluates the
     * sent_today < daily_limit guard under the row lock.
     */
    private function reserveDailySlot(EmailProvider $provider): bool
    {
        return DB::table('email_providers')
            ->where('id', $provider->id)
            ->where('sent_today', '<', $provider->daily_limit)
            ->increment('sent_today') === 1;
    }

    /**
     * Give back a reserved slot when the send fails and a different
     * provider (or a later attempt) should still be usable.
     */
    private function releaseDailySlot(EmailProvider $provider): void
    {
        DB::table('email_providers')
            ->where('id', $provider->id)
            ->where('sent_today', '>', 0)
            ->decrement('sent_today');
    }

    private function resetDailyUsageIfNeeded(EmailProvider $provider): void
    {
        if ($provider->last_reset !== null && $provider->last_reset->isBefore(today())) {
            $provider->update(['sent_today' => 0, 'last_reset' => today()]);
        }
    }
}
