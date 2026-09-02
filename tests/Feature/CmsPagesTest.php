<?php

namespace Tests\Feature;

use App\Filament\Pages\AboutPageCms;
use App\Filament\Pages\Settings;
use App\Filament\Pages\StudyInChinaCms;
use App\Models\AboutSection;
use App\Models\Setting;
use App\Models\StudyInChinaSection;
use App\Models\User;
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

    public function test_about_cms_page_saves_text_and_rejects_invalid_json(): void
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

        // Invalid JSON anywhere must abort the whole save (nothing persisted).
        Livewire::actingAs($this->admin())
            ->test(AboutPageCms::class)
            ->set("data.v_{$heading->id}", 'Updated heading')
            ->set("data.v_{$cards->id}", '{ not valid json')
            ->call('save')
            ->assertNotified();

        $this->assertSame('Original heading', $heading->fresh()->value);

        // Valid JSON + text saves.
        Livewire::actingAs($this->admin())
            ->test(AboutPageCms::class)
            ->set("data.v_{$heading->id}", 'Updated heading')
            ->set("data.v_{$cards->id}", json_encode([['text' => 'New'], ['text' => 'Another']]))
            ->call('save')
            ->assertNotified('About page content saved successfully.');

        $this->assertSame('Updated heading', $heading->fresh()->value);
        $this->assertSame(
            [['text' => 'New'], ['text' => 'Another']],
            json_decode($cards->fresh()->value, true)
        );
    }

    public function test_study_in_china_cms_page_saves(): void
    {
        $row = StudyInChinaSection::create([
            'key' => 'hero_subtitle',
            'value' => 'Old subtitle',
            'type' => 'longtext',
            'group' => 'hero',
            'label' => 'Section Subtitle',
            'sort_order' => 2,
        ]);

        $this->actingAs($this->admin())
            ->get(StudyInChinaCms::getUrl())
            ->assertOk();

        Livewire::actingAs($this->admin())
            ->test(StudyInChinaCms::class)
            ->set("data.v_{$row->id}", 'চীনে পড়াশোনা করুন নতুন উপায়ে')
            ->set("data.o_{$row->id}", 5)
            ->call('save')
            ->assertNotified('Study in China content saved successfully.');

        $row->refresh();

        $this->assertSame('চীনে পড়াশোনা করুন নতুন উপায়ে', $row->value);
        $this->assertSame(5, $row->sort_order);
    }
}
