<?php

namespace App\Notifications\Channels;

use App\Services\EmailService;
use Illuminate\Notifications\Notification;

/**
 * Delivers a notification through the application's queued EmailService so
 * auth emails get the same provider routing, EmailLog lifecycle and
 * credential handling as every other email.
 *
 * Notifications using this channel expose an `toEmailService($notifiable)`
 * method returning the rendered payload:
 *
 *     [
 *         'to' => string,
 *         'subject' => string,
 *         'html' => string,
 *         'template_key' => ?string,
 *         'recipient_name' => ?string,
 *     ]
 */
class EmailServiceChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        /** @var array{to: string, subject: string, html: string, template_key?: ?string, recipient_name?: ?string} $payload */
        $payload = $notification->toEmailService($notifiable);

        app(EmailService::class)->send(
            $payload['to'],
            $payload['subject'],
            $payload['html'],
            $payload['template_key'] ?? null,
            $payload['recipient_name'] ?? null,
        );
    }
}
