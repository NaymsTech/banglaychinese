<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Support\EmailShell;
use App\Support\EmailTemplatePlaceholders;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Throwable;

class EmailService
{
    public function __construct(private readonly MailManager $mailManager) {}

    /**
     * Queue an email for delivery through the highest-priority active
     * provider. Provider selection, daily-limit gating and failover run
     * inside the SendEmailJob worker, so this returns as soon as the email
     * has been accepted onto the queue.
     *
     * When $sync is true the job is executed synchronously instead and the
     * final delivery result is returned — used only by tests and the
     * provider test-connection flow.
     *
     * @return array{success: bool, queued?: bool, log_id?: int, provider?: string, error?: string}
     */
    public function send(
        string $to,
        string $subject,
        string $htmlContent,
        ?string $templateKey = null,
        ?string $recipientName = null,
        ?string $fromAddress = null,
        ?string $fromName = null,
        bool $sync = false,
    ): array {
        if (! EmailProvider::query()->where('is_active', true)->exists()) {
            Log::error('No active email providers configured.');

            return ['success' => false, 'error' => 'No email providers configured'];
        }

        $log = EmailLog::create([
            'provider_id' => null,
            'template_key' => $templateKey,
            'recipient_email' => $to,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'body' => $htmlContent,
            'status' => EmailLog::STATUS_QUEUED,
            'queued_at' => now(),
            'attempt_count' => 0,
        ]);

        if ($sync) {
            SendEmailJob::dispatchSync(
                $log->id,
                $to,
                $subject,
                $htmlContent,
                $templateKey,
                $recipientName,
                $fromAddress,
                $fromName,
            );

            return $this->resultFromLog($log);
        }

        SendEmailJob::dispatch(
            $log->id,
            $to,
            $subject,
            $htmlContent,
            $templateKey,
            $recipientName,
            $fromAddress,
            $fromName,
        );

        return ['success' => true, 'queued' => true, 'log_id' => $log->id];
    }

    /**
     * Send an email built from a database template, replacing every
     * {variable} placeholder with the given value.
     *
     * Settings-backed variables are merged in automatically and can be
     * overridden by the caller: {bkash_number}, {nagad_number},
     * {bank_details}, {whatsapp_number} and {contact_email}.
     *
     * @return array{success: bool, queued?: bool, log_id?: int, provider?: string, error?: string}
     *
     * @throws ModelNotFoundException when the template key is unknown or inactive
     */
    public function sendTemplate(
        string $templateKey,
        string $to,
        array $variables = [],
        ?string $recipientName = null,
        bool $sync = false,
    ): array {
        $template = EmailTemplate::query()
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->firstOrFail();

        // Never queue an email with unresolved {placeholders}: if the template
        // content references a token the caller did not supply and that is not
        // resolved from the site settings, fail before any log row is written.
        $used = EmailTemplatePlaceholders::referenced($template->subject, $template->body);
        $unresolvable = EmailTemplatePlaceholders::unresolvable($used, array_merge(array_keys($variables), $this->settingsVariableKeys()));

        if ($unresolvable !== []) {
            $missing = implode(', ', array_map(fn (string $variable): string => '{'.$variable.'}', $unresolvable));

            throw new \InvalidArgumentException("Email template [{$templateKey}] is missing a value for required placeholder(s): {$missing}.");
        }

        $allVariables = array_merge($this->settingsVariables(), $variables);

        $subject = $this->replaceVariables($template->subject, $allVariables);
        $body = $this->replaceVariables($template->body, $allVariables);

        $fromAddress = filled($template->from_address) ? $template->from_address : null;
        $fromName = filled($template->from_name) ? $template->from_name : null;

        return $this->send($to, $subject, $body, $templateKey, $recipientName, $fromAddress, $fromName, $sync);
    }

    /**
     * Variables resolved from the site settings on every send, so templates
     * can reference {bkash_number}, {nagad_number}, {bank_details},
     * {whatsapp_number} and {contact_email} without the caller supplying them.
     *
     * @return array<string, mixed>
     */
    public function settingsVariables(): array
    {
        return [
            'bkash_number' => SettingsService::get('bkash_number', 'Not set'),
            'nagad_number' => SettingsService::get('nagad_number', 'Not set'),
            'bank_details' => SettingsService::get('bank_details', 'Not set'),
            'whatsapp_number' => SettingsService::get('whatsapp_number', 'Not set'),
            'contact_email' => SettingsService::get('contact_email', 'info@banglaychinese.com'),
        ];
    }

    /**
     * The keys EmailService resolves from the site settings on every send.
     *
     * @return array<int, string>
     */
    public function settingsVariableKeys(): array
    {
        return EmailTemplatePlaceholders::SETTINGS_BACKED_VARIABLES;
    }

    /**
     * Render one of the two system-critical auth templates (verification or
     * password reset) from its EmailTemplate row when one is active and fully
     * resolvable, otherwise fall back to the bundled Blade default.
     *
     * This is the ONLY place an email falls back to bundled content, and it is
     * deliberate: the two auth flows must never stop working because an admin
     * deactivated/deleted a template, and the fallback is loud in the logs.
     * All other template sends stay strict (see sendTemplate).
     *
     * @param  array<string, mixed>  $variables
     * @param  array<string, mixed>  $viewData
     * @return array{subject: string, html: string, source: 'template'|'fallback'}
     */
    public function renderTemplateForSystemEmail(
        string $templateKey,
        array $variables,
        string $fallbackSubject,
        string $fallbackView,
        array $viewData,
    ): array {
        $template = EmailTemplate::query()
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->first();

        $values = array_merge($this->settingsVariables(), $variables);

        if ($template !== null) {
            $used = EmailTemplatePlaceholders::referenced($template->subject, $template->body);
            $unresolvable = EmailTemplatePlaceholders::unresolvable($used, array_keys($values));

            if ($unresolvable === []) {
                $rendered = $this->renderTemplate($template, $values);

                return ['subject' => $rendered['subject'], 'html' => $rendered['body'], 'source' => 'template'];
            }

            Log::critical("Email template [{$templateKey}] has unresolvable placeholders (".implode(', ', $unresolvable).') — falling back to the bundled default.');
        }

        Log::critical("Email template [{$templateKey}] is missing or inactive — falling back to the bundled default for [{$fallbackView}].");

        return [
            'subject' => $fallbackSubject,
            'html' => view($fallbackView, $viewData)->render(),
            'source' => 'fallback',
        ];
    }

    /**
     * Render a template's subject and body with the given values without
     * sending anything — used by the admin preview.
     *
     * @return array{subject: string, body: string}
     */
    public function renderTemplate(EmailTemplate $template, array $variables): array
    {
        return [
            'subject' => $this->replaceVariables($template->subject, $variables),
            'body' => $this->replaceVariables($template->body, $variables),
        ];
    }

    /**
     * Wrap raw email content in the branded Banglay Chinese shell.
     *
     * This is the presentation seam of the whole email system: database
     * template bodies are content fragments, so they are decorated here with
     * the shared layout just before a message is composed for a provider. The
     * queued payload and the EmailLog row keep the raw authored content, and
     * complete HTML documents (auth Blade fallbacks, legacy full-document
     * template rows, already-wrapped resends) pass through untouched so a
     * message is never nested inside a second <html> document.
     *
     * $data carries per-render overrides for any shell variable (see
     * EmailShell::brandingVariables). Delivery passes none, so the current
     * EmailBranding configuration applies; the Filament branding preview
     * passes the unsaved form state so it previews through this exact seam.
     *
     * @param  array<string, mixed>  $data
     */
    public function renderForDelivery(string $htmlContent, array $data = []): string
    {
        if (EmailShell::isCompleteEmail($htmlContent)) {
            return $htmlContent;
        }

        return EmailShell::render($htmlContent, $data);
    }

    /**
     * Send a test email synchronously through the given provider to verify
     * its credentials. Deliberately bypasses the queue and the email log.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(EmailProvider $provider, ?string $to = null): array
    {
        try {
            $to ??= auth()->check() ? auth()->user()->email : config('mail.from.address');

            $body = '<h2>Test Email from Banglay Chinese</h2>'
                .'<p>Your email configuration is working correctly!</p>'
                .'<p>Provider: '.$provider->name.'</p>'
                .'<p>Time: '.now()->toDateTimeString().'</p>';

            $this->sendViaProvider($provider, $to, 'Test Email - Banglay Chinese', $body);

            return ['success' => true, 'message' => "Test email sent to {$to}"];
        } catch (Throwable $exception) {
            $message = $provider->redactSecrets($exception->getMessage() ?? 'The test email could not be sent.');

            return ['success' => false, 'message' => $message];
        }
    }

    /**
     * Re-queue a failed email as a brand-new delivery attempt.
     *
     * The original log row is left untouched for the audit trail; a fresh
     * row is created and queued from the stored payload, so a successful
     * email can never be duplicated by accident.
     *
     * @return array{success: bool, queued?: bool, log_id?: int, error?: string}
     */
    public function resend(EmailLog $log): array
    {
        if (! $log->canBeResent()) {
            return ['success' => false, 'error' => 'Only failed emails with a stored body can be resent.'];
        }

        return $this->send(
            $log->recipient_email,
            $log->subject,
            $log->body,
            $log->template_key,
            $log->recipient_name,
            $log->from_address,
            $log->from_name,
        );
    }

    /**
     * Deliver one email through one provider using a transport built
     * exclusively from that provider's stored configuration.
     *
     * No global mail configuration is mutated, so a send can never leak a
     * provider's credentials into the rest of the process. Throws on
     * delivery failure so the caller can decide how to react.
     *
     * @return string|null the provider-assigned message ID, when available
     */
    public function sendViaProvider(
        EmailProvider $provider,
        string $to,
        string $subject,
        string $htmlContent,
        ?string $recipientName = null,
        ?string $fromAddress = null,
        ?string $fromName = null,
    ): ?string {
        $config = $provider->config;

        $mailer = new Mailer(
            'email-provider-'.$provider->id,
            app('view'),
            $this->transportFor($provider),
            app('events'),
        );

        // Fragments are decorated with the branded shell at this final point;
        // see renderForDelivery().
        $sentMessage = $mailer->html($this->renderForDelivery($htmlContent), function (Message $message) use ($config, $to, $subject, $recipientName, $fromAddress, $fromName) {
            $message->to($to, $recipientName)
                ->subject($subject)
                ->from(
                    $fromAddress ?: ($config['from_address'] ?? config('mail.from.address')),
                    $fromName ?: ($config['from_name'] ?? config('mail.from.name')),
                );
        });

        return $sentMessage?->getMessageId();
    }

    /**
     * Replace {variable} placeholders in a template part with their values.
     */
    protected function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{'.$key.'}', $value, $content);
        }

        return $content;
    }

    /**
     * Build a transport instance for the given provider from its stored
     * configuration, never from the application's mail configuration.
     */
    protected function transportFor(EmailProvider $provider): TransportInterface
    {
        return $this->mailManager->createSymfonyTransport($this->transportConfigFor($provider));
    }

    /**
     * Map a provider's stored configuration onto a mailer config array that
     * the mail manager can turn into a transport.
     *
     * Only drivers whose transport is actually available are supported;
     * anything else fails loudly so a stale provider record can never be
     * silently sent over the wrong transport.
     *
     * @return array<string, mixed>
     *
     * @throws \InvalidArgumentException when the driver has no installed transport
     */
    protected function transportConfigFor(EmailProvider $provider): array
    {
        $config = $provider->config;

        return match ($provider->driver) {
            EmailProvider::DRIVER_SMTP => $this->smtpConfig($config),
            EmailProvider::DRIVER_SENDGRID => $this->smtpConfig([
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'username' => 'apikey',
                'password' => $config['api_key'] ?? '',
                'encryption' => 'tls',
            ]),
            default => throw new \InvalidArgumentException("Unsupported email provider driver [{$provider->driver}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function smtpConfig(array $config): array
    {
        $port = (int) ($config['port'] ?? 587);
        $encryption = $config['encryption'] ?? 'tls';

        return [
            'transport' => 'smtp',
            'scheme' => $encryption === 'ssl' || $port === 465 ? 'smtps' : 'smtp',
            'host' => $config['host'] ?? 'smtp-relay.brevo.com',
            'port' => $port,
            'username' => $config['username'] ?? '',
            'password' => $config['password'] ?? '',
            'timeout' => 30,
        ];
    }

    /**
     * Translate a freshly written log row into the caller-facing result of a
     * synchronous send.
     *
     * @return array{success: bool, provider?: string, error?: string}
     */
    protected function resultFromLog(EmailLog $log): array
    {
        $log->refresh();

        if ($log->status === EmailLog::STATUS_SENT) {
            return ['success' => true, 'provider' => $log->provider?->name];
        }

        return ['success' => false, 'error' => $log->error_message ?? 'The email could not be sent.'];
    }
}
