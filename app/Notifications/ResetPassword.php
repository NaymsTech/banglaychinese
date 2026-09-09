<?php

namespace App\Notifications;

use App\Services\EmailService;
use App\Services\SettingsService;
use Illuminate\Notifications\Notification;

/**
 * Custom password-reset notification sent through the queued EmailService.
 *
 * Laravel's secure broker still creates and validates the token; this class
 * only renders and delivers the reset link that the broker hands out.
 */
class ResetPassword extends Notification
{
    /**
     * The password reset token.
     */
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

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
     * Render the password reset email and hand it to the queued EmailService.
     * The subject/body come from the active EmailTemplate row keyed
     * 'password_reset' when one exists and is fully resolvable, otherwise the
     * bundled default view is used and the fallback is logged.
     *
     * @return array{to: string, subject: string, html: string, template_key: string, recipient_name: ?string}
     */
    public function toEmailService(object $notifiable): array
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $appName = (string) SettingsService::get('site_name', 'Banglay Chinese');
        $expireMinutes = (int) config('auth.passwords.users.expire', 60);

        $rendered = app(EmailService::class)->renderTemplateForSystemEmail(
            templateKey: 'password_reset',
            variables: [
                'name' => $notifiable->name ?? '',
                'appName' => $appName,
                'url' => $resetUrl,
                'expireMinutes' => $expireMinutes,
            ],
            fallbackSubject: 'Reset your '.$appName.' password',
            fallbackView: 'emails.auth.password-reset',
            viewData: [
                'url' => $resetUrl,
                'name' => $notifiable->name ?? null,
                'appName' => $appName,
                'expireMinutes' => $expireMinutes,
            ],
        );

        return [
            'to' => $notifiable->getEmailForPasswordReset(),
            'subject' => $rendered['subject'],
            'html' => $rendered['html'],
            'template_key' => 'password_reset',
            'recipient_name' => $notifiable->name ?? null,
        ];
    }
}
