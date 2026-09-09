<?php

namespace App\Listeners;

use App\Services\EmailService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Send the one-time welcome email after a user's email is first verified.
 *
 * The Verified event only fires on the unverified → verified transition, so
 * re-verifying the same address never duplicates it; after an email change
 * the new address gets its own welcome, which is the intended behaviour.
 */
class SendWelcomeEmail
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        try {
            app(EmailService::class)->sendTemplate(
                'welcome_email',
                $user->email,
                ['student_name' => $user->name ?? ''],
            );
        } catch (ModelNotFoundException) {
            Log::warning('Welcome email not sent: welcome_email template is missing or inactive.', [
                'user_id' => $user->id,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Welcome email could not be queued.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
