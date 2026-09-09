<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard.index', absolute: false));
    }

    public function test_registration_queues_exactly_one_verification_email_and_no_welcome_yet(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        Queue::fake([SendEmailJob::class]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'verify@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.index', absolute: false));
        $this->assertAuthenticated();

        $user = User::where('email', 'verify@example.com')->firstOrFail();

        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseMissing('email_logs', ['template_key' => 'welcome_email']);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'email_verification',
            'recipient_email' => 'verify@example.com',
            'status' => EmailLog::STATUS_QUEUED,
        ]);
        Queue::assertPushed(SendEmailJob::class, 1);
    }
}
