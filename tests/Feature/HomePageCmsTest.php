<?php

namespace Tests\Feature;

use App\Filament\Pages\HomePageCms;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HomePageCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_public_homepage_renders_defaults_when_no_settings_exist(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Explore Live Courses')
            ->assertSee('Our Popular Courses')
            ->assertSee('Take the First Step Towards Your Future');
    }

    public function test_admin_can_open_the_home_page_cms(): void
    {
        $this->actingAs($this->admin())
            ->get(HomePageCms::getUrl())
            ->assertOk();
    }

    public function test_editing_home_page_updates_public_homepage(): void
    {
        $this->actingAs($this->admin())
            ->get(HomePageCms::getUrl())
            ->assertOk();

        Livewire::actingAs($this->admin())
            ->test(HomePageCms::class)
            ->set('data.hero_badge', 'New Winter Batch Open')
            ->set('data.hero_title', "Learn Chinese Directly From China\nYour Future Starts Here")
            ->set('data.final_heading', 'Start Your China Dream Now')
            ->call('save')
            ->assertNotified('Home page content saved successfully.');

        $this->assertSame('New Winter Batch Open', Setting::where('key', 'hero_badge')->value('value'));
        $this->assertSame('Start Your China Dream Now', Setting::where('key', 'final_heading')->value('value'));

        $this->get('/')
            ->assertOk()
            ->assertSee('New Winter Batch Open')
            ->assertSee('Start Your China Dream Now');
    }

    public function test_rich_text_fields_are_rendered_as_html_on_the_homepage(): void
    {
        Setting::updateOrCreate(
            ['key' => 'founder_bio'],
            ['value' => '<p><strong>দশ বছরের অভিজ্ঞতা</strong> নিয়ে আমরা এগিয়ে চলি।</p>']
        );
        Setting::updateOrCreate(
            ['key' => 'award_description'],
            ['value' => '<p>চীনা ভাষা প্রতিযোগিতায় <em>চূড়ান্ত প্রতিযোগী</em>।</p>']
        );

        $this->get('/')
            ->assertOk()
            ->assertSee('<strong>দশ বছরের অভিজ্ঞতা</strong>', false)
            ->assertSee('<em>চূড়ান্ত প্রতিযোগী</em>', false)
            ->assertDontSee('&lt;strong&gt;', false);
    }

    public function test_award_image_falls_back_to_the_default_photo_when_unset(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('photo-1541829070764', false);
    }

    public function test_saved_award_image_is_rendered_on_the_homepage(): void
    {
        Setting::updateOrCreate(
            ['key' => 'award_image'],
            ['value' => 'home/awards/certificate.jpg']
        );

        $this->get('/')
            ->assertOk()
            ->assertSee(asset('storage/home/awards/certificate.jpg'), false);
    }

    public function test_admin_can_upload_an_award_photo_in_the_cms(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->admin())
            ->test(HomePageCms::class)
            ->set('data.award_image', UploadedFile::fake()->image('award.jpg'))
            ->call('save')
            ->assertNotified('Home page content saved successfully.');

        $path = Setting::where('key', 'award_image')->value('value');
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);

        $this->get('/')
            ->assertOk()
            ->assertSee(asset('storage/'.$path), false);
    }
}
