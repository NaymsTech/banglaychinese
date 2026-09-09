<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_forgot_password_page_renders_matching_design_and_back_to_login_link(): void
    {
        $this->get(route('password.request'))
            ->assertSee('Forgot your password?')
            ->assertSee('No problem. Just let us know your email address and we will email you a password reset link.')
            ->assertSee('Email Password Reset Link')
            ->assertSee(route('password.email'))
            ->assertSee(route('login'))
            ->assertSee('Back to Login');
    }

    public function test_reset_link_request_flashes_success_message(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('success');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_reset_password_page_renders_matching_design_with_token_and_email_fields(): void
    {
        $url = route('password.reset', ['token' => 'reset-token-123', 'email' => 'student@example.com']);

        $this->get($url)
            ->assertSee('Reset Your Password')
            ->assertSee('Please enter your new password below.')
            ->assertSee('Reset Password')
            ->assertSee(route('password.store'))
            ->assertSee('Back to Login')
            ->assertSee(route('login'))
            ->assertSee('type="hidden" name="token" value="reset-token-123"', false)
            ->assertSee('type="hidden" name="email" value="student@example.com"', false);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_reset_token_is_stored_hashed_never_in_plaintext(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $stored = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');

            $this->assertNotNull($stored);
            $this->assertNotSame($notification->token, $stored);
            $this->assertTrue(password_verify($notification->token, $stored));

            return true;
        });
    }

    public function test_reset_password_email_is_queued_and_logged(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $user = User::factory()->create([
            'email' => 'rahim@example.com',
            'name' => 'Rahim Uddin',
        ]);

        Queue::fake([SendEmailJob::class]);

        $this->post('/forgot-password', ['email' => 'rahim@example.com']);

        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'password_reset',
            'recipient_email' => 'rahim@example.com',
            'recipient_name' => 'Rahim Uddin',
            'status' => EmailLog::STATUS_QUEUED,
            'attempt_count' => 0,
            'error_message' => null,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->to === 'rahim@example.com'
                && $job->templateKey === 'password_reset'
                && $job->subject === 'Reset your Banglay Chinese password'
                && str_contains($job->htmlContent, 'Reset Password')
                && str_contains($job->htmlContent, 'reset-password/')
                && str_contains($job->htmlContent, 'expire in 60 minutes');
        });
    }
}
