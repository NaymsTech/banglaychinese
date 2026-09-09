<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageEmailBranding;
use App\Models\EmailBranding;
use App\Models\User;
use App\Services\EmailService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class EmailBrandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SettingsService::set('site_logo', '');
        config(['app.url' => 'https://banglaychinese.com']);
    }

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_open_the_email_branding_page(): void
    {
        $this->actingAs($this->admin())
            ->get(ManageEmailBranding::getUrl())
            ->assertOk()
            ->assertSee('Email branding');
    }

    public function test_opening_the_page_creates_the_default_branding_row_and_shows_it(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->assertFormSet([
                'primary_color' => EmailBranding::DEFAULT_PRIMARY_COLOR,
                'accent_color' => EmailBranding::DEFAULT_ACCENT_COLOR,
                'body_text_color' => EmailBranding::DEFAULT_BODY_TEXT_COLOR,
                'muted_text_color' => EmailBranding::DEFAULT_MUTED_TEXT_COLOR,
                'is_active' => true,
            ]);

        $this->assertNotNull(EmailBranding::currentRow());
    }

    public function test_preview_iframe_receives_real_html_not_a_double_escaped_raw_dump(): void
    {
        // The full email document is embedded as the srcdoc attribute value,
        // escaped exactly once by Blade. A double escape (e.g. {{ e(...) }})
        // would make the iframe show the literal markup instead of rendering.
        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->assertSee('srcdoc="&lt;!DOCTYPE html&gt;', false)
            ->assertDontSee('&amp;lt;!DOCTYPE', false)
            ->assertSee('Sample CTA Button', false);
    }

    public function test_unsaved_primary_color_and_tagline_appear_in_the_preview_without_saving(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->set('data.primary_color', '#123456')
            ->set('data.tagline', 'Unsaved tagline shows instantly')
            ->assertSee('#123456', false)
            ->assertSee('Unsaved tagline shows instantly', false);

        // Nothing was persisted yet.
        $row = EmailBranding::currentRow();
        $this->assertSame(EmailBranding::DEFAULT_PRIMARY_COLOR, $row->primary_color);
        $this->assertSame('', $row->tagline);
    }

    public function test_unsaved_footer_text_appears_in_the_preview_without_saving(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->set('data.footer_text', 'Unsaved footer message')
            ->assertSee('Unsaved footer message', false);

        $this->assertSame('', EmailBranding::currentRow()->footer_text);
    }

    public function test_admin_can_save_branding_values(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->fillForm([
                'app_name' => 'Banglay Chinese Academy',
                'tagline' => 'Learn Mandarin from Bangladesh',
                'primary_color' => '#B91C1C',
                'accent_color' => '#1E1B4B',
                'body_text_color' => '#0F172A',
                'muted_text_color' => '#475569',
                'footer_text' => 'Made with care in Bangladesh',
                'contact_email' => 'care@banglaychinese.com',
                'contact_phone' => '01300000000',
                'whatsapp_country_code' => '+880',
                'whatsapp_number' => '1712 345 678',
                'facebook_url' => 'https://www.facebook.com/banglaychinese',
                'instagram_url' => 'https://www.instagram.com/banglaychinese',
                'youtube_url' => 'https://www.youtube.com/@banglaychinese',
                'website_url' => 'https://learn.banglaychinese.com',
                'copyright_text' => '© Banglay Chinese Academy',
                'is_active' => true,
            ])
            ->call('save')
            ->assertNotified('Email branding saved.')
            ->assertHasNoFormErrors();

        $row = EmailBranding::currentRow();

        $this->assertNotNull($row);
        $this->assertSame('Banglay Chinese Academy', $row->app_name);
        $this->assertSame('#B91C1C', $row->primary_color);
        $this->assertSame('care@banglaychinese.com', $row->contact_email);
        $this->assertSame('01300000000', $row->contact_phone);
        $this->assertSame('+880', $row->whatsapp_country_code);
        $this->assertSame('1712 345 678', $row->whatsapp_number);
        $this->assertSame('https://www.facebook.com/banglaychinese', $row->facebook_url);
        $this->assertSame('https://www.instagram.com/banglaychinese', $row->instagram_url);
        $this->assertSame('https://www.youtube.com/@banglaychinese', $row->youtube_url);
        $this->assertSame('Made with care in Bangladesh', $row->footer_text);
        $this->assertTrue($row->is_active);

        // The saved branding drives the real delivery path immediately.
        $html = app(EmailService::class)->renderForDelivery('<p>Hello</p>');
        $this->assertStringContainsString('#B91C1C', $html);
        $this->assertStringContainsString('Learn Mandarin from Bangladesh', $html);
        $this->assertStringContainsString('care@banglaychinese.com', $html);
        $this->assertStringContainsString('https://wa.me/8801712345678', $html);
        $this->assertStringContainsString('https://www.facebook.com/banglaychinese', $html);
        $this->assertStringContainsString('https://www.instagram.com/banglaychinese', $html);
        $this->assertStringContainsString('https://www.youtube.com/@banglaychinese', $html);
    }

    public function test_preview_shows_saved_branding_after_save_and_after_reload(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->set('data.primary_color', '#0EA5E9')
            ->set('data.tagline', 'Persisted tagline preview')
            ->call('save')
            ->assertNotified('Email branding saved.')
            ->assertSee('#0EA5E9', false)
            ->assertSee('Persisted tagline preview', false);

        // A brand-new page session (reload) starts from the saved row.
        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->assertFormSet(['primary_color' => '#0EA5E9'])
            ->assertSee('#0EA5E9', false)
            ->assertSee('Persisted tagline preview', false);

        $this->assertSame('#0EA5E9', EmailBranding::currentRow()->primary_color);
        $this->assertSame('Persisted tagline preview', EmailBranding::currentRow()->tagline);
    }

    public function test_admin_can_upload_a_png_email_logo(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->set('data.logo', UploadedFile::fake()->image('email-logo.png'))
            ->call('save')
            ->assertNotified('Email branding saved.')
            ->assertHasNoFormErrors();

        $path = EmailBranding::currentRow()->logo;

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);

        // The rendered email uses an absolute /storage/ URL for the logo.
        $html = app(EmailService::class)->renderForDelivery('<p>Hello</p>');
        $this->assertStringContainsString(
            'src="https://banglaychinese.com/storage/'.$path.'"',
            $html,
        );
    }

    public function test_preview_html_uses_the_canonical_delivery_seam_with_form_overrides(): void
    {
        $service = Mockery::mock(EmailService::class, [app('mail.manager')])->makePartial();
        $service->shouldReceive('renderForDelivery')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::on(static fn (array $data): bool => ($data['primaryColor'] ?? null) === '#123456'),
            )
            ->andReturn('CANONICAL-DELIVERY-DOC');

        $this->app->instance(EmailService::class, $service);

        try {
            $html = ManageEmailBranding::previewHtml(['primary_color' => '#123456']);
        } finally {
            $this->app->forgetInstance(EmailService::class);
        }

        $this->assertSame('CANONICAL-DELIVERY-DOC', $html);
    }

    public function test_preview_renders_the_canonical_email_shell_with_the_form_values(): void
    {
        $html = ManageEmailBranding::previewHtml([
            'primary_color' => '#B91C1C',
            'tagline' => 'Preview tagline',
            'contact_email' => 'preview@banglaychinese.com',
            'contact_phone' => '01300000000',
            'is_active' => true,
        ]);

        // Full branded document from the exact shell delivery path.
        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Preview tagline', $html);
        $this->assertStringContainsString('#B91C1C', $html);
        $this->assertStringContainsString('Sample CTA Button', $html);
        $this->assertStringContainsString('preview@banglaychinese.com', $html);
        $this->assertStringContainsString('01300000000', $html);

        // It really is the emails.layouts.branded shell (mso conditional +
        // email-safe container), not a separate preview design.
        $this->assertStringContainsString('<!--[if mso]>', $html);
        $this->assertStringContainsString('max-width:600px', $html);

        // The bundled fallback logo is rendered with an absolute URL.
        $expected = ManageEmailBranding::previewAssetOrigin().'/assets/logo-full.png';
        $this->assertMatchesRegularExpression('~src="https?://[^"]+/assets/logo-full\.png"~', $html);
        $this->assertStringContainsString('src="'.$expected.'"', $html);

        $this->assertStringNotContainsString('#007A3D', $html);
    }

    public function test_preview_resolves_an_existing_uploaded_logo_to_an_absolute_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('email-branding/header.png', 'fake-png');

        $html = ManageEmailBranding::previewHtml([
            'logo' => 'email-branding/header.png',
            'primary_color' => '#123456',
        ]);

        $this->assertMatchesRegularExpression(
            '~src="https?://[^"]+/storage/email-branding/header\.png"~',
            $html,
        );
        $this->assertStringNotContainsString('logo-full.png', $html);
    }

    public function test_preview_falls_back_to_the_bundled_logo_for_a_missing_file(): void
    {
        Storage::fake('public');

        $html = ManageEmailBranding::previewHtml(['logo' => 'email-branding/not-there.png']);

        $this->assertMatchesRegularExpression(
            '~src="https?://[^"]+/assets/logo-full\.png"~',
            $html,
        );
    }

    public function test_preview_html_includes_configured_whatsapp_and_social_links(): void
    {
        $html = ManageEmailBranding::previewHtml([
            'whatsapp_country_code' => '+880',
            'whatsapp_number' => '1712 345 678',
            'facebook_url' => 'https://www.facebook.com/banglaychinese',
            'instagram_url' => 'https://www.instagram.com/banglaychinese',
            'youtube_url' => 'https://www.youtube.com/@banglaychinese',
            'is_active' => true,
        ]);

        $this->assertStringContainsString('https://wa.me/8801712345678', $html);
        $this->assertStringContainsString('href="https://www.facebook.com/banglaychinese"', $html);
        $this->assertStringContainsString('href="https://www.instagram.com/banglaychinese"', $html);
        $this->assertStringContainsString('href="https://www.youtube.com/@banglaychinese"', $html);
    }

    public function test_unsaved_whatsapp_and_social_changes_appear_in_the_preview_without_saving(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->set('data.whatsapp_country_code', '+880')
            ->set('data.whatsapp_number', '1712 345 678')
            ->set('data.facebook_url', 'https://www.facebook.com/unsaved-brand')
            ->assertSee('https://wa.me/8801712345678', false)
            ->assertSee('https://www.facebook.com/unsaved-brand', false);

        // Nothing persisted yet.
        $row = EmailBranding::currentRow();
        $this->assertSame('', $row->whatsapp_country_code);
        $this->assertSame('', $row->whatsapp_number);
        $this->assertSame('', $row->facebook_url);
    }

    public function test_clearing_a_social_url_removes_it_from_the_preview(): void
    {
        EmailBranding::ensureExists()->update([
            'whatsapp_country_code' => '+880',
            'whatsapp_number' => '1712 345 678',
            'facebook_url' => 'https://www.facebook.com/saved-brand',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->assertSee('https://wa.me/8801712345678', false)
            ->assertSee('https://www.facebook.com/saved-brand', false)
            ->set('data.whatsapp_number', '')
            ->set('data.facebook_url', '')
            ->assertDontSee('https://wa.me/8801712345678', false)
            ->assertDontSee('https://www.facebook.com/saved-brand', false);
    }

    public function test_social_urls_must_be_https(): void
    {
        EmailBranding::ensureExists()->update(['facebook_url' => 'https://www.facebook.com/original']);

        Livewire::actingAs($this->admin())
            ->test(ManageEmailBranding::class)
            ->set('data.facebook_url', 'http://facebook.com/insecure')
            ->call('save')
            ->assertHasFormErrors(['facebook_url']);

        $this->assertSame(
            'https://www.facebook.com/original',
            EmailBranding::currentRow()->facebook_url,
        );
    }
}
