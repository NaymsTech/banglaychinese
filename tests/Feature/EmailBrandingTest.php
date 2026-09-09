<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\EmailBranding;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use App\Services\SettingsService;
use App\Support\EmailShell;
use Database\Seeders\EmailSystemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailBrandingTest extends TestCase
{
    use RefreshDatabase;

    private function service(): EmailService
    {
        return app(EmailService::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Keep the settings-backed fallbacks deterministic no matter what an
        // earlier test class left in the (per-process) array cache.
        SettingsService::set('site_name', 'Banglay Chinese');
        SettingsService::set('contact_email', 'info@banglaychinese.com');
        SettingsService::set('site_logo', '');
        config(['app.url' => 'https://banglaychinese.com']);
    }

    public function test_email_system_seeder_creates_the_default_branding_row(): void
    {
        $this->seed(EmailSystemSeeder::class);

        $row = EmailBranding::currentRow();

        $this->assertNotNull($row);
        $this->assertSame(EmailBranding::DEFAULT_PRIMARY_COLOR, $row->primary_color);
        $this->assertSame(EmailBranding::DEFAULT_ACCENT_COLOR, $row->accent_color);
        $this->assertSame(EmailBranding::DEFAULT_BODY_TEXT_COLOR, $row->body_text_color);
        $this->assertSame(EmailBranding::DEFAULT_MUTED_TEXT_COLOR, $row->muted_text_color);
        $this->assertTrue($row->is_active);
    }

    public function test_branding_values_can_be_updated_and_retrieved(): void
    {
        EmailBranding::ensureExists()->update([
            'primary_color' => '#1D4ED8',
            'accent_color' => '#0F172A',
            'tagline' => 'Chinese from Dhaka to Beijing',
            'contact_phone' => '01300000000',
            'footer_text' => 'Made with care in Bangladesh',
            'is_active' => true,
        ]);

        $row = EmailBranding::currentRow();

        $this->assertSame('#1D4ED8', $row->primary_color);
        $this->assertSame('#0F172A', $row->accent_color);
        $this->assertSame('Chinese from Dhaka to Beijing', $row->tagline);
        $this->assertSame('01300000000', $row->contact_phone);
        $this->assertSame('Made with care in Bangladesh', $row->footer_text);
    }

    public function test_shell_renders_built_in_defaults_before_branding_is_configured(): void
    {
        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Learn Chinese in Bangla • Study in China', $html);
        $this->assertStringContainsString('background-color:#007A3D', $html);
        $this->assertStringContainsString('https://banglaychinese.com/assets/logo-full.png', $html);
        $this->assertStringContainsString('info@banglaychinese.com', $html);
        $this->assertStringContainsString('© '.date('Y').' Banglay Chinese. All rights reserved.', $html);
        $this->assertStringNotContainsString('#0f5132', $html);
    }

    public function test_changing_primary_color_changes_the_rendered_email(): void
    {
        EmailBranding::ensureExists()->update(['primary_color' => '#B91C1C']);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('#B91C1C', $html);
        $this->assertStringNotContainsString('#007A3D', $html);
    }

    public function test_changing_tagline_changes_the_rendered_email(): void
    {
        EmailBranding::ensureExists()->update(['tagline' => 'Learn Mandarin from Bangladesh']);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('Learn Mandarin from Bangladesh', $html);
        $this->assertStringNotContainsString('Learn Chinese in Bangla • Study in China', $html);
    }

    public function test_changing_app_name_and_copyright_changes_the_rendered_email(): void
    {
        EmailBranding::ensureExists()->update([
            'app_name' => 'Banglay Chinese Academy',
            'copyright_text' => '© Banglay Chinese Academy — All rights reserved.',
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('Banglay Chinese Academy', $html);
        $this->assertStringContainsString('© Banglay Chinese Academy — All rights reserved.', $html);
    }

    public function test_changing_footer_text_changes_the_rendered_email(): void
    {
        EmailBranding::ensureExists()->update(['footer_text' => 'Study with confidence — 加油!']);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('Study with confidence — 加油!', $html);

        EmailBranding::currentRow()->update(['footer_text' => '']);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringNotContainsString('Study with confidence', $html);
    }

    public function test_changing_contact_information_changes_the_rendered_email(): void
    {
        EmailBranding::ensureExists()->update([
            'contact_email' => 'care@banglaychinese.com',
            'contact_phone' => '01300000000',
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('care@banglaychinese.com', $html);
        $this->assertStringContainsString('01300000000', $html);
        $this->assertStringNotContainsString('info@banglaychinese.com', $html);
    }

    public function test_changing_website_url_changes_the_logo_and_footer_links(): void
    {
        EmailBranding::ensureExists()->update(['website_url' => 'https://learn.banglaychinese.com']);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('href="https://learn.banglaychinese.com"', $html);
        $this->assertStringNotContainsString('href="https://banglaychinese.com"', $html);
    }

    public function test_changing_the_logo_changes_the_rendered_image_url(): void
    {
        EmailBranding::ensureExists()->update(['logo' => 'email-branding/header.png']);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString(
            'src="https://banglaychinese.com/storage/email-branding/header.png"',
            $html,
        );
        $this->assertStringNotContainsString('logo-full.png', $html);
        $this->assertSame(
            'https://banglaychinese.com/storage/email-branding/header.png',
            EmailShell::logoUrl('email-branding/header.png'),
        );
    }

    public function test_disabled_branding_falls_back_to_the_built_in_defaults(): void
    {
        EmailBranding::ensureExists()->update([
            'primary_color' => '#B91C1C',
            'tagline' => 'Temporary custom tagline',
            'is_active' => false,
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('background-color:#007A3D', $html);
        $this->assertStringContainsString('Learn Chinese in Bangla • Study in China', $html);
        $this->assertStringNotContainsString('#B91C1C', $html);
        $this->assertStringNotContainsString('Temporary custom tagline', $html);
    }

    public function test_every_seeded_template_inherits_the_current_branding(): void
    {
        $this->seed(EmailSystemSeeder::class);

        EmailBranding::ensureExists()->update([
            'tagline' => 'Learning Chinese from Bangladesh',
            'primary_color' => '#B91C1C',
            'footer_text' => 'Shared footer line for all templates',
            'contact_phone' => '01300000000',
        ]);

        $templates = EmailTemplate::query()->orderBy('id')->get();

        $this->assertCount(11, $templates);

        foreach ($templates as $template) {
            $html = $this->service()->renderForDelivery((string) $template->body);

            // The shell branding is present around the authored body…
            $this->assertStringStartsWith('<!DOCTYPE html>', $html, "[{$template->key}] must render through the branded shell");
            $this->assertStringContainsString('Learning Chinese from Bangladesh', $html, "[{$template->key}] must show the current tagline");
            $this->assertStringContainsString('#B91C1C', $html, "[{$template->key}] must show the current primary color");
            $this->assertStringContainsString('Shared footer line for all templates', $html, "[{$template->key}] must show the current footer text");
            $this->assertStringContainsString('01300000000', $html, "[{$template->key}] must show the current contact phone");
            $this->assertStringNotContainsString('Learn Chinese in Bangla • Study in China', $html, "[{$template->key}] must not carry the default tagline anymore");

            // …while the template content itself stays an untouched fragment.
            $this->assertFalse(EmailShell::isCompleteEmail((string) $template->body), "[{$template->key}] body must stay a content fragment");
            $this->assertStringNotContainsString('<html', (string) $template->body, "[{$template->key}] must not embed a standalone HTML document");
            $this->assertStringNotContainsString('#0f5132', (string) $template->body, "[{$template->key}] must not carry the legacy green");
        }
    }

    public function test_placeholders_still_resolve_and_cta_links_survive_delivery_decoration(): void
    {
        $this->seed(EmailSystemSeeder::class);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->sendTemplate('product_approved', 'student@example.com', [
            'student_name' => 'Rahim Uddin',
            'product_title' => 'HSK 1 E-Book',
            'download_link' => 'https://example.com/dl/hsk1',
        ]);

        $this->assertTrue($result['success']);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            $this->assertStringContainsString('Rahim Uddin', $job->htmlContent);
            $this->assertStringContainsString('HSK 1 E-Book', $job->htmlContent);
            $this->assertStringContainsString('href="https://example.com/dl/hsk1"', $job->htmlContent);
            $this->assertStringNotContainsString('{', $job->htmlContent);
            $this->assertStringNotContainsString('}', $job->htmlContent);
            $this->assertStringNotContainsString('<html', $job->htmlContent);
            $this->assertStringNotContainsString('#0f5132', $job->htmlContent);

            return true;
        });

        // The canonical delivery path still decorates the resolved fragment.
        $html = $this->service()->renderForDelivery(
            '<p>Download: <a href="https://example.com/dl/hsk1" style="color:#007A3D;">hsk1</a></p>',
        );

        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('href="https://example.com/dl/hsk1"', $html);
    }

    public function test_no_svg_email_logo_is_introduced(): void
    {
        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('assets/logo-full.png', $html);
        $this->assertStringNotContainsString('.svg', $html);
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringEndsWith('logo-full.png', EmailShell::logoUrl());
    }

    public function test_customized_template_bodies_are_never_overwritten_by_the_seeder(): void
    {
        $this->seed(EmailSystemSeeder::class);

        EmailTemplate::where('key', 'payment_reminder')->update(['body' => 'CUSTOMIZED REMINDER BODY']);
        EmailTemplate::where('key', 'application_received')->update(['body' => 'CUSTOMIZED APPLICATION BODY']);

        // Re-running the seeder (and creating the branding row) must not touch them.
        $this->seed(EmailSystemSeeder::class);

        $this->assertSame(
            'CUSTOMIZED REMINDER BODY',
            EmailTemplate::where('key', 'payment_reminder')->value('body'),
        );
        $this->assertSame(
            'CUSTOMIZED APPLICATION BODY',
            EmailTemplate::where('key', 'application_received')->value('body'),
        );
    }

    public function test_whatsapp_number_normalization_rules(): void
    {
        // "+" and formatting characters are dropped, the country code is kept.
        $this->assertSame('8801712345678', EmailBranding::internationalWhatsAppNumber('+880', '1712 345 678'));
        $this->assertSame('8801712345678', EmailBranding::internationalWhatsAppNumber('+880', '(1712) 345-678'));
        $this->assertSame('8618223249514', EmailBranding::internationalWhatsAppNumber('+86', '18223249514'));

        // The country code is never duplicated when already present.
        $this->assertSame('8801712345678', EmailBranding::internationalWhatsAppNumber('880', '8801712345678'));

        // An empty country code with an international number is safe.
        $this->assertSame('8618223249514', EmailBranding::internationalWhatsAppNumber('', '8618223249514'));

        // An empty number never produces a number/link.
        $this->assertSame('', EmailBranding::internationalWhatsAppNumber('+880', ''));
        $this->assertSame('', EmailBranding::internationalWhatsAppNumber('', ''));
    }

    public function test_whatsapp_link_is_generated_from_country_code_and_number(): void
    {
        EmailBranding::ensureExists()->update([
            'whatsapp_country_code' => '+880',
            'whatsapp_number' => '1712 345 678',
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('href="https://wa.me/8801712345678"', $html);
        $this->assertStringContainsString('>WhatsApp</a>', $html);
    }

    public function test_whatsapp_link_never_duplicates_the_country_code(): void
    {
        EmailBranding::ensureExists()->update([
            'whatsapp_country_code' => '880',
            'whatsapp_number' => '8801712345678',
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('href="https://wa.me/8801712345678"', $html);
        $this->assertSame(1, substr_count($html, 'https://wa.me/8801712345678'));
    }

    public function test_empty_whatsapp_values_never_render_a_link(): void
    {
        SettingsService::set('whatsapp_number', '');

        EmailBranding::ensureExists()->update([
            'whatsapp_country_code' => '+880',
            'whatsapp_number' => '',
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringNotContainsString('wa.me', $html);
        $this->assertStringNotContainsString('>WhatsApp</a>', $html);

        EmailBranding::currentRow()->update([
            'whatsapp_country_code' => '',
            'whatsapp_number' => '',
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringNotContainsString('wa.me', $html);
    }

    public function test_configured_social_links_appear_unchanged_in_the_delivered_email(): void
    {
        EmailBranding::ensureExists()->update([
            'facebook_url' => 'https://www.facebook.com/banglaychinese',
            'instagram_url' => 'https://www.instagram.com/banglaychinese',
            'youtube_url' => 'https://www.youtube.com/@banglaychinese',
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('href="https://www.facebook.com/banglaychinese"', $html);
        $this->assertStringContainsString('>Facebook</a>', $html);
        $this->assertStringContainsString('href="https://www.instagram.com/banglaychinese"', $html);
        $this->assertStringContainsString('>Instagram</a>', $html);
        $this->assertStringContainsString('href="https://www.youtube.com/@banglaychinese"', $html);
        $this->assertStringContainsString('>YouTube</a>', $html);
        $this->assertStringNotContainsString('&amp;', $html);
    }

    public function test_blank_social_urls_are_not_rendered(): void
    {
        SettingsService::set('whatsapp_number', '');
        SettingsService::set('facebook_url', '');
        SettingsService::set('instagram_url', '');
        SettingsService::set('youtube_url', '');

        EmailBranding::ensureExists()->update([
            'facebook_url' => '',
            'instagram_url' => '',
            'youtube_url' => '',
        ]);

        $html = $this->service()->renderForDelivery('<p>Hello world</p>');

        $this->assertStringContainsString('>Website</a>', $html);
        $this->assertStringNotContainsString('>WhatsApp</a>', $html);
        $this->assertStringNotContainsString('>Facebook</a>', $html);
        $this->assertStringNotContainsString('>Instagram</a>', $html);
        $this->assertStringNotContainsString('>YouTube</a>', $html);
    }

    public function test_social_links_are_inherited_by_every_seeded_template(): void
    {
        $this->seed(EmailSystemSeeder::class);

        EmailBranding::ensureExists()->update([
            'whatsapp_country_code' => '+880',
            'whatsapp_number' => '1712 345 678',
            'facebook_url' => 'https://www.facebook.com/banglaychinese',
            'instagram_url' => 'https://www.instagram.com/banglaychinese',
            'youtube_url' => 'https://www.youtube.com/@banglaychinese',
        ]);

        $templates = EmailTemplate::query()->orderBy('id')->get();

        $this->assertCount(11, $templates);

        foreach ($templates as $template) {
            $html = $this->service()->renderForDelivery((string) $template->body);

            $this->assertStringContainsString('https://wa.me/8801712345678', $html, "[{$template->key}] must carry the WhatsApp link");
            $this->assertStringContainsString('https://www.facebook.com/banglaychinese', $html, "[{$template->key}] must carry the Facebook link");
            $this->assertStringContainsString('https://www.instagram.com/banglaychinese', $html, "[{$template->key}] must carry the Instagram link");
            $this->assertStringContainsString('https://www.youtube.com/@banglaychinese', $html, "[{$template->key}] must carry the YouTube link");
        }
    }
}
