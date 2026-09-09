<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FreeResource;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Support\WhatsAppNumber;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Stage 6A guards: production seeding stays clean, local demo data is
 * local-only, confirmed contact values are seeded, and the WhatsApp display
 * formatting is consistent and settings-driven.
 */
class ProductionSeedingGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeding_creates_no_demo_user_or_demo_resources(): void
    {
        $this->runSeederAs('production', function (): void {
            $this->artisan('db:seed', ['--force' => true])->assertSuccessful();
        });

        // No fake/demo accounts, orders, or dummy resources.
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        $this->assertSame(0, FreeResource::count());

        // The essential production data IS present: settings, the secure admin
        // account, and the email system bootstrap.
        $this->assertDatabaseHas('users', ['email' => 'admin@banglaychinese.com', 'is_admin' => true]);
        $this->assertDatabaseHas('settings', ['key' => 'bkash_number', 'value' => '01774148708']);
        $this->assertDatabaseHas('email_providers', ['name' => 'Brevo SMTP']);
    }

    public function test_admin_user_seeder_throws_when_admin_password_is_missing_in_production(): void
    {
        $previous = getenv('ADMIN_PASSWORD');
        putenv('ADMIN_PASSWORD');
        unset($_ENV['ADMIN_PASSWORD'], $_SERVER['ADMIN_PASSWORD']);

        $this->app->instance('env', 'production');

        try {
            try {
                $this->app->make(AdminUserSeeder::class)->run();

                $this->fail('Expected a RuntimeException when ADMIN_PASSWORD is missing in production.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('ADMIN_PASSWORD must be set', $exception->getMessage());
            }
        } finally {
            if ($previous === false) {
                putenv('ADMIN_PASSWORD');
                unset($_ENV['ADMIN_PASSWORD'], $_SERVER['ADMIN_PASSWORD']);
            } else {
                putenv("ADMIN_PASSWORD={$previous}");
                $_ENV['ADMIN_PASSWORD'] = $previous;
                $_SERVER['ADMIN_PASSWORD'] = $previous;
            }

            $this->app->instance('env', 'testing');
        }
    }

    public function test_local_seeding_may_still_create_local_only_demo_data(): void
    {
        // First seed as non-local so the local-only extras are clearly absent.
        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        $this->assertSame(0, FreeResource::count());

        $this->runSeederAs('local', function (): void {
            $this->artisan('db:seed', ['--force' => true])->assertSuccessful();
        });

        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'name' => 'Test User']);
        $this->assertGreaterThan(0, FreeResource::count());
    }

    public function test_confirmed_contact_values_are_seeded(): void
    {
        $this->seed(SettingsSeeder::class);

        $this->assertSame('01774148708', Setting::where('key', 'bkash_number')->value('value'));
        $this->assertSame('01774148708', Setting::where('key', 'nagad_number')->value('value'));
        $this->assertSame('8618223249514', Setting::where('key', 'whatsapp_number')->value('value'));
        $this->assertSame('8618223249514', Setting::where('key', 'study_in_china_phone')->value('value'));
        $this->assertSame('8618223249514', Setting::where('key', 'study_in_china_whatsapp')->value('value'));
        $this->assertSame('Chongqing, China', Setting::where('key', 'physical_address')->value('value'));
        $this->assertSame('Chongqing, China', Setting::where('key', 'study_in_china_office')->value('value'));

        // Placeholders must no longer be seeded anywhere.
        $this->assertDatabaseMissing('settings', ['value' => '01700000000']);
        $this->assertDatabaseMissing('settings', ['value' => '01300000000']);
        $this->assertDatabaseMissing('settings', ['value' => 'Beijing, China']);
    }

    public function test_whatsapp_display_formatter_produces_the_confirmed_format(): void
    {
        $this->assertSame('+86 182-2324-9514', WhatsAppNumber::display('8618223249514'));
        $this->assertSame('+86 182-2324-9514', WhatsAppNumber::display('+86 182-2324-9514'));
        $this->assertSame('8618223249514', WhatsAppNumber::normalize('+86 182-2324-9514'));

        // Bangladeshi numbers (used for service/manual contacts) stay readable.
        $this->assertSame('+880 1774-148708', WhatsAppNumber::display('01774148708'));
    }

    public function test_blog_whatsapp_cta_uses_the_settings_driven_number(): void
    {
        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'General',
            'slug' => 'general-'.Str::lower(Str::random(6)),
        ]);

        $post = Post::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post-'.Str::lower(Str::random(6)),
            'category_id' => $category->id,
            'user_id' => $author->id,
            'content' => '<p>Blog content used to render the WhatsApp CTA.</p>',
            'excerpt' => 'Blog excerpt.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        // A distinct test number proves the CTA reads the setting, not a
        // hard-coded value in the Blade file.
        Setting::updateOrCreate(['key' => 'whatsapp_number'], ['value' => '8618999999999']);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('https://wa.me/8618999999999', false)
            ->assertDontSee('https://wa.me/8618223249514', false);
    }

    /**
     * Run a closure with the application environment temporarily overridden.
     */
    protected function runSeederAs(string $environment, \Closure $callback): void
    {
        $this->app->instance('env', $environment);

        $previous = getenv('ADMIN_PASSWORD');
        putenv('ADMIN_PASSWORD=test-admin-password');
        $_ENV['ADMIN_PASSWORD'] = 'test-admin-password';
        $_SERVER['ADMIN_PASSWORD'] = 'test-admin-password';

        try {
            $callback();
        } finally {
            if ($previous === false) {
                putenv('ADMIN_PASSWORD');
                unset($_ENV['ADMIN_PASSWORD'], $_SERVER['ADMIN_PASSWORD']);
            } else {
                putenv("ADMIN_PASSWORD={$previous}");
                $_ENV['ADMIN_PASSWORD'] = $previous;
                $_SERVER['ADMIN_PASSWORD'] = $previous;
            }

            $this->app->instance('env', 'testing');
        }
    }
}
