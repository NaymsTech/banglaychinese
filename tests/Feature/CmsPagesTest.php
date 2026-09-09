<?php

namespace Tests\Feature;

use App\Filament\Pages\AboutPageCms;
use App\Filament\Pages\Settings;
use App\Filament\Pages\StudyInChinaCms;
use App\Models\AboutSection;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CmsPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_settings_page_renders(): void
    {
        $this->actingAs($this->admin())
            ->get(Settings::getUrl())
            ->assertOk();
    }

    public function test_settings_can_be_saved(): void
    {
        Setting::create(['key' => 'site_name', 'value' => 'Old Name']);

        Livewire::actingAs($this->admin())
            ->test(Settings::class)
            ->set('data.site_name', 'Banglay Chinese')
            ->set('data.whatsapp_number', '8618223249514')
            ->set('data.facebook_pixel_id', '123456789')
            ->call('save')
            ->assertNotified();

        $this->assertSame('Banglay Chinese', Setting::where('key', 'site_name')->value('value'));
        $this->assertSame('8618223249514', Setting::where('key', 'whatsapp_number')->value('value'));
        $this->assertSame('123456789', Setting::where('key', 'facebook_pixel_id')->value('value'));
    }

    public function test_logo_and_favicon_are_rendered_from_settings(): void
    {
        // Empty settings fall back to the bundled brand assets.
        SettingsService::set('site_logo', '');
        SettingsService::set('site_favicon', '');

        $this->get('/')
            ->assertOk()
            ->assertSee('assets/logo-full.jpeg')
            ->assertSee('assets/logo.jpeg');

        // Uploaded images are rendered from the public disk instead.
        SettingsService::set('site_logo', 'settings/logo.png');
        SettingsService::set('site_favicon', 'settings/favicon.png');

        $this->get('/')
            ->assertOk()
            ->assertSee('storage/settings/logo.png')
            ->assertSee('storage/settings/favicon.png');
    }

    public function test_about_cms_page_saves_text_and_list_rows(): void
    {
        $heading = AboutSection::create([
            'key' => 'hero_heading',
            'value' => 'Original heading',
            'type' => 'string',
            'group' => 'hero',
            'label' => 'Hero Heading',
            'sort_order' => 1,
        ]);

        $cards = AboutSection::create([
            'key' => 'hero_highlights',
            'value' => json_encode([['text' => 'Old']]),
            'type' => 'json',
            'group' => 'hero',
            'label' => 'Hero Highlights',
            'sort_order' => 2,
        ]);

        $this->actingAs($this->admin())
            ->get(AboutPageCms::getUrl())
            ->assertOk();

        Livewire::actingAs($this->admin())
            ->test(AboutPageCms::class)
            ->set("data.v_{$heading->id}", 'Updated heading')
            ->set("data.v_{$cards->id}", [
                ['icon' => '🇨🇳', 'text' => 'Studying in China since 2017'],
                ['icon' => '💼', 'text' => 'Professional Interpreter'],
            ])
            ->call('save')
            ->assertNotified('About page content saved successfully.');

        $this->assertSame('Updated heading', $heading->fresh()->value);
        $this->assertSame(
            [
                ['icon' => '🇨🇳', 'text' => 'Studying in China since 2017'],
                ['icon' => '💼', 'text' => 'Professional Interpreter'],
            ],
            json_decode($cards->fresh()->value, true)
        );
    }

    public function test_about_prose_fields_render_html_and_legacy_plain_text(): void
    {
        AboutSection::create([
            'key' => 'story_content',
            'value' => '<p>Rich <strong>story</strong> from the CMS.</p>',
            'type' => 'text',
            'group' => 'story',
            'label' => 'Story Content',
            'sort_order' => 1,
        ]);

        AboutSection::create([
            'key' => 'vision_content',
            'value' => "Paragraph one.\n\nParagraph two.",
            'type' => 'text',
            'group' => 'vision',
            'label' => 'Vision Content',
            'sort_order' => 1,
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('<strong>story</strong>', false)
            ->assertDontSee('&lt;strong&gt;', false)
            ->assertSee('<p>Paragraph one.</p>', false)
            ->assertSee('<p>Paragraph two.</p>', false);
    }

    public function test_study_in_china_cms_page_saves_settings_and_lists(): void
    {
        $this->actingAs($this->admin())
            ->get(StudyInChinaCms::getUrl())
            ->assertOk();

        Livewire::actingAs($this->admin())
            ->test(StudyInChinaCms::class)
            ->set('data.sic_hero_title', 'চীনে পড়াশোনার নতুন শিরোনাম')
            ->set('data.sic_faq_items', [
                ['question' => 'আমি কীভাবে শুরু করব?', 'answer' => '<p><strong>প্রথম ধাপ</strong> consultation ফর্ম জমা দেওয়া।</p>'],
            ])
            ->call('save')
            ->assertNotified('Study in China content saved successfully.');

        $this->assertSame('চীনে পড়াশোনার নতুন শিরোনাম', Setting::where('key', 'sic_hero_title')->value('value'));
        $this->assertSame(
            [
                ['question' => 'আমি কীভাবে শুরু করব?', 'answer' => '<p><strong>প্রথম ধাপ</strong> consultation ফর্ম জমা দেওয়া।</p>'],
            ],
            json_decode(Setting::where('key', 'sic_faq_items')->value('value'), true)
        );
    }

    public function test_study_in_china_page_renders_cms_defaults_and_overrides(): void
    {
        // Defaults render before anything is saved.
        $this->get('/study-in-china')
            ->assertOk()
            ->assertSee('চীনে পড়াশোনার স্বপ্নকে একটি পরিষ্কার পরিকল্পনায় পরিণত করুন');

        // Saved settings override the defaults, and RichEditor answers render as HTML.
        Setting::updateOrCreate(['key' => 'sic_hero_title'], ['value' => 'চীনে পড়াশোনার সিএমএস শিরোনাম']);
        Setting::updateOrCreate(['key' => 'sic_faq_items'], ['value' => json_encode([
            ['question' => 'পেজ কি এখন ডাইনামিক?', 'answer' => '<p>হ্যাঁ, <strong>CMS</strong> থেকে আসে।</p>'],
        ])]);

        $this->get('/study-in-china')
            ->assertOk()
            ->assertSee('চীনে পড়াশোনার সিএমএস শিরোনাম')
            ->assertSee('পেজ কি এখন ডাইনামিক?')
            ->assertSee('<strong>CMS</strong>', false)
            ->assertDontSee('&lt;strong&gt;', false);
    }
}
