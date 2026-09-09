<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use App\Services\SettingsService;
use App\Support\EmailShell;
use App\Support\EmailTemplatePlaceholders;
use Database\Seeders\EmailSystemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailShellTest extends TestCase
{
    use RefreshDatabase;

    private function service(): EmailService
    {
        return app(EmailService::class);
    }

    public function test_fragment_content_is_wrapped_in_the_branded_shell(): void
    {
        $content = '<p>Welcome, Rahim Uddin! 🇨🇳 Download it here: https://example.com/hsk1</p>';

        // Social/WhatsApp footer links inherit the site settings when email
        // branding has not configured them yet.
        SettingsService::set('whatsapp_number', '8618223249514');
        SettingsService::set('facebook_url', 'https://facebook.com/banglaychinese');
        SettingsService::set('youtube_url', 'https://youtube.com/@banglaychinese');

        $html = $this->service()->renderForDelivery($content);

        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringContainsString($content, $html);
        $this->assertStringContainsString('Learn Chinese in Bangla • Study in China', $html);
        $this->assertStringContainsString('background-color:#F4F7F5', $html);
        $this->assertStringContainsString('background-color:#007A3D', $html);
        $this->assertStringContainsString('https://banglaychinese.com/assets/logo-full.png', $html);
        $this->assertStringContainsString('https://wa.me/8618223249514', $html);
        $this->assertStringContainsString('https://facebook.com/banglaychinese', $html);
        $this->assertStringContainsString('https://youtube.com/@banglaychinese', $html);
        $this->assertStringContainsString('>Website</a>', $html);
        $this->assertStringContainsString('>WhatsApp</a>', $html);
        $this->assertStringContainsString('>Facebook</a>', $html);
        $this->assertStringContainsString('>YouTube</a>', $html);
    }

    public function test_unconfigured_social_links_are_not_rendered(): void
    {
        SettingsService::set('whatsapp_number', '');
        SettingsService::set('facebook_url', '');
        SettingsService::set('instagram_url', '');
        SettingsService::set('youtube_url', '');

        $html = $this->service()->renderForDelivery('<p>Hello</p>');

        $this->assertStringContainsString('>Website</a>', $html);
        $this->assertStringNotContainsString('wa.me', $html);
        $this->assertStringNotContainsString('>WhatsApp</a>', $html);
        $this->assertStringNotContainsString('>Facebook</a>', $html);
        $this->assertStringNotContainsString('>Instagram</a>', $html);
        $this->assertStringNotContainsString('>YouTube</a>', $html);
    }

    public function test_complete_html_documents_pass_through_unchanged(): void
    {
        $document = '<!DOCTYPE html>'
            ."\n".'<html lang="en">'
            ."\n".'<head><title>Full document</title></head>'
            ."\n".'<body style="background-color:#ffffff;"><p>Already complete</p></body>'
            ."\n".'</html>';

        $this->assertSame($document, $this->service()->renderForDelivery($document));
        $this->assertTrue(EmailShell::isCompleteEmail($document));
        $this->assertFalse(EmailShell::isCompleteEmail('<div>Fragment only</div>'));
    }

    public function test_logo_url_falls_back_to_the_production_origin_off_localhost(): void
    {
        config(['app.url' => 'http://localhost']);

        $this->assertSame('https://banglaychinese.com', EmailShell::origin());
        $this->assertSame('https://banglaychinese.com/assets/logo-full.png', EmailShell::logoUrl());
    }

    public function test_logo_url_resolves_the_admin_uploaded_logo_or_an_external_url(): void
    {
        config(['app.url' => 'https://banglaychinese.com']);

        SettingsService::set('site_logo', 'uploads/brand/logo.png');

        $this->assertSame('https://banglaychinese.com/storage/uploads/brand/logo.png', EmailShell::logoUrl());

        SettingsService::set('site_logo', 'https://cdn.example.com/logo.png');

        $this->assertSame('https://cdn.example.com/logo.png', EmailShell::logoUrl());
    }

    public function test_auth_fallback_view_renders_inside_the_branded_shell(): void
    {
        $url = url(route('password.reset', ['token' => 'token-123', 'email' => 'student@example.com'], false));

        $html = view('emails.auth.password-reset', [
            'name' => 'Rahim Uddin',
            'appName' => 'Banglay Chinese',
            'url' => $url,
            'expireMinutes' => 60,
        ])->render();

        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Learn Chinese in Bangla • Study in China', $html);
        $this->assertStringContainsString('Reset Password', $html);
        $this->assertStringContainsString($url, $html);
        $this->assertStringContainsString('expire in 60 minutes', $html);
        $this->assertStringContainsString('border-radius:999px', $html);
    }

    public function test_template_send_keeps_the_raw_fragment_in_the_log_and_queued_payload(): void
    {
        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}',
            'body' => '<p>Download: {download_link}</p>',
            'variables' => ['student_name', 'download_link'],
        ]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->sendTemplate('product_approved', 'student@example.com', [
            'student_name' => 'Rahim Uddin',
            'download_link' => 'https://example.com/hsk1',
        ]);

        $this->assertTrue($result['success']);

        // The decoration is applied only at the final delivery seam, so the
        // log row and queued payload keep the authored content unchanged.
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'product_approved',
            'body' => '<p>Download: https://example.com/hsk1</p>',
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->templateKey === 'product_approved'
                && $job->htmlContent === '<p>Download: https://example.com/hsk1</p>';
        });

        $this->assertStringContainsString('<p>Download: https://example.com/hsk1</p>', $this->service()->renderForDelivery('<p>Download: https://example.com/hsk1</p>'));
    }

    public function test_every_seeded_template_body_renders_through_the_branded_shell(): void
    {
        $this->seed(EmailSystemSeeder::class);

        // Every seeded template body — including the two auth templates — is
        // now a content fragment for the branded shell.
        foreach (EmailTemplate::all() as $template) {
            $body = (string) $template->body;

            $this->assertFalse(EmailShell::isCompleteEmail($body), "[{$template->key}] should stay a content fragment for the shell");
            $this->assertStringNotContainsString('<html', $body, "[{$template->key}] must not embed a standalone HTML document");
            $this->assertStringStartsWith('<!DOCTYPE html>', $this->service()->renderForDelivery($body));
            $this->assertStringContainsString('Learn Chinese in Bangla • Study in China', $this->service()->renderForDelivery($body));
            $this->assertStringNotContainsString('#0f5132', $body, "[{$template->key}] must not carry the legacy green");
            $this->assertStringContainsString('#007A3D', $body, "[{$template->key}] should use the brand green");

            $used = EmailTemplatePlaceholders::referenced($template->subject, $body);
            $unresolvable = EmailTemplatePlaceholders::unresolvable($used, $template->variables ?? []);

            $this->assertSame([], $unresolvable, "[{$template->key}] references placeholders that are not declared: ".implode(', ', $unresolvable));
        }

        // Auth wording and intent are preserved after the visual conversion.
        $verify = (string) EmailTemplate::where('key', 'email_verification')->value('body');
        $this->assertStringContainsString('Verify Email Address', $verify);
        $this->assertStringContainsString('it unlocks your dashboard, courses and downloads', $verify);
        $this->assertStringContainsString('expire in {expireMinutes} minutes', $verify);

        $reset = (string) EmailTemplate::where('key', 'password_reset')->value('body');
        $this->assertStringContainsString('Reset Password', $reset);
        $this->assertStringContainsString('choose a new password', $reset);
        $this->assertStringContainsString('no further action is required', $reset);
    }
}
