<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 6E: the production footer (layouts/app.blade.php) must render for
 * guests and signed-in users with valid, state-appropriate links.
 */
class FooterNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_renders_for_guests_with_working_internal_links(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $footer = $this->footerHtml($html);

        // Main menu links.
        $this->assertStringContainsString(route('about'), $footer);
        $this->assertStringContainsString(route('study-in-china'), $footer);
        $this->assertStringContainsString(route('contact'), $footer);
        $this->assertStringContainsString(route('posts.index'), $footer);

        // Guest-appropriate account links (login/register/recovery)…
        $this->assertStringContainsString(route('login'), $footer);
        $this->assertStringContainsString(route('register'), $footer);
        $this->assertStringContainsString(route('password.request'), $footer);
        $this->assertStringContainsString('Lost Password', $footer);

        // …and no Dashboard wall for signed-out visitors.
        $this->assertStringNotContainsString(route('dashboard.index'), $footer);

        // Legal links.
        $this->assertStringContainsString(route('pages.show', 'terms-and-conditions'), $footer);
        $this->assertStringContainsString(route('pages.show', 'privacy-policy'), $footer);
        $this->assertStringContainsString(route('pages.show', 'refund-and-returns-policy'), $footer);
        $this->assertStringContainsString(route('pages.show', 'faq'), $footer);

        // No placeholders, no stale destinations.
        $this->assertStringNotContainsString('href="#"', $footer);
        $this->assertStringNotContainsString('/scholarship', $footer);
    }

    public function test_footer_renders_for_authenticated_students_with_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();
        $footer = $this->footerHtml($html);

        $this->assertStringContainsString('Dashboard', $footer);
        $this->assertStringContainsString(route('dashboard.index'), $footer);

        // Registration/login-only links are hidden once signed in.
        $this->assertStringNotContainsString('Register', $footer);
        $this->assertStringNotContainsString(route('register'), $footer);
        $this->assertStringNotContainsString(route('login'), $footer);

        // Legal links remain.
        $this->assertStringContainsString(route('pages.show', 'privacy-policy'), $footer);
    }

    public function test_footer_whatsapp_and_email_use_the_confirmed_production_values(): void
    {
        Setting::updateOrCreate(['key' => 'whatsapp_number'], ['value' => '8618223249514']);
        Setting::updateOrCreate(['key' => 'contact_email'], ['value' => 'info@banglaychinese.com']);

        $html = $this->get('/')->assertOk()->getContent();
        $footer = $this->footerHtml($html);

        $this->assertStringContainsString('https://wa.me/8618223249514', $footer);
        $this->assertStringContainsString('+86 182-2324-9514', $footer);
        $this->assertStringNotContainsString('+86-861-8223-249514', $footer);
        $this->assertStringNotContainsString('+86 82232-49514', $footer);

        $this->assertStringContainsString('mailto:info@banglaychinese.com', $footer);
    }

    public function test_key_footer_destinations_load_for_guests(): void
    {
        $publicPages = [
            route('about'),
            route('study-in-china'),
            route('study-in-china.consultation'),
            route('contact'),
            route('courses.index'),
            route('shop.index'),
            route('free-resources.index'),
            route('posts.index'),
            route('pages.show', 'terms-and-conditions'),
            route('pages.show', 'privacy-policy'),
            route('pages.show', 'refund-and-returns-policy'),
            route('pages.show', 'faq'),
        ];

        foreach ($publicPages as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_mobile_bottom_nav_contains_working_destinations(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Chat', $html);
        $this->assertStringContainsString(route('courses.index'), $html);
        $this->assertStringContainsString(route('study-in-china'), $html);
        $this->assertStringContainsString('https://wa.me/8618223249514', $html);
    }

    protected function footerHtml(string $html): string
    {
        $start = strpos($html, '<footer');
        $end = strpos($html, '</footer>');

        if ($start === false || $end === false || $end < $start) {
            $this->fail('Footer markup not found in rendered page.');
        }

        return substr($html, $start, $end - $start);
    }
}
