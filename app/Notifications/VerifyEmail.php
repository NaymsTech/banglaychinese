<?php

namespace App\Notifications;

use App\Services\EmailService;
use App\Services\SettingsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Custom email-verification notification sent through the queued EmailService.
 *
 * The verify link is Laravel's temporary signed route, so the id/hash
 * mechanism and its expiry are unchanged from the framework default.
 */
class VerifyEmail extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['email-service'];
    }

    /**
     * Render the email verification email and hand it to the queued
     * EmailService. The subject/body come from the active EmailTemplate row
     * keyed 'email_verification' when one exists and is fully resolvable,
     * otherwise the bundled default view is used and the fallback is logged.
     *
     * @return array{to: string, subject: string, html: string, template_key: string, recipient_name: ?string}
     */
    public function toEmailService(object $notifiable): array
    {
        $verifyUrl = $this->verificationUrl($notifiable);

        $appName = (string) SettingsService::get('site_name', 'Banglay Chinese');
        $expireMinutes = (int) config('auth.verification.expire', 60);

        $rendered = app(EmailService::class)->renderTemplateForSystemEmail(
            templateKey: 'email_verification',
            variables: [
                'name' => $notifiable->name ?? '',
                'appName' => $appName,
                'url' => $verifyUrl,
                'expireMinutes' => $expireMinutes,
            ],
            fallbackSubject: 'Verify your '.$appName.' email',
            fallbackView: 'emails.auth.verify-email',
            viewData: [
                'url' => $verifyUrl,
                'name' => $notifiable->name ?? null,
                'appName' => $appName,
                'expireMinutes' => $expireMinutes,
            ],
        );

        return [
            'to' => $notifiable->getEmailForVerification(),
            'subject' => $rendered['subject'],
            'html' => $rendered['html'],
            'template_key' => 'email_verification',
            'recipient_name' => $notifiable->name ?? null,
        ];
    }

    /**
     * Build the temporary signed verification URL.
     */
    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );
    }
}
