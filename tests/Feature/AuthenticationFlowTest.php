<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_new_design_with_register_and_password_links(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome Back to')
            ->assertSee('Sign in to your account')
            ->assertSee('Create a new account')
            ->assertSee(route('register'))
            ->assertSee('Forgot your password?')
            ->assertSee(route('password.request'))
            ->assertSee('Remember me for 30 days')
            ->assertSee('Back to Home');
    }

    public function test_invalid_credentials_show_error_alert_on_login_page(): void
    {
        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertSee('Unable to sign in');
    }

    public function test_register_route_is_available_to_guests(): void
    {
        $this->get(route('register'))
            ->assertOk();
    }

    public function test_register_page_renders_new_design_with_phone_field(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Welcome to')
            ->assertSee('Create your account')
            ->assertSee('Already have an account?')
            ->assertSee(route('login'))
            ->assertSee('Phone Number')
            ->assertSee('password_confirmation')
            ->assertSee('Create Account')
            ->assertSee(route('pages.show', 'terms-and-conditions'));
    }

    public function test_registration_creates_user_and_stores_optional_phone(): void
    {
        $this->post(route('register'), [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'phone' => '+8801712345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'student@example.com',
            'phone' => '+8801712345678',
        ]);
    }

    public function test_navbar_has_single_login_button_for_guests(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        // Scope to the header only — the footer intentionally still links to /register.
        $header = substr($html, strpos($html, '<header'), strpos($html, '</header>') - strpos($html, '<header'));

        $this->assertStringContainsString('>Login</a>', $header);
        $this->assertStringNotContainsString('Register', $header);
    }

    public function test_footer_menu_shows_register_link_for_guests_only(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('Lost Password')
            ->assertSee('href="'.route('password.request').'"', false);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('href="'.route('register').'"', false)
            ->assertDontSee('href="'.route('password.request').'"', false)
            ->assertSee('href="'.route('dashboard.index').'"', false);
    }
}
