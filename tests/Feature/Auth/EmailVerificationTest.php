<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function unverifiedUser(array $overrides = []): User
    {
        return User::factory()->unverified()->create($overrides);
    }

    protected function verificationUrl(User $user, string $hash, \DateTimeInterface $expiresAt): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            $expiresAt,
            ['id' => $user->id, 'hash' => $hash],
        );
    }

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = $this->unverifiedUser();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = $this->unverifiedUser();

        Event::fake([Verified::class]);

        $verificationUrl = $this->verificationUrl($user, sha1($user->email), now()->addMinutes(60));

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('dashboard.index', absolute: false).'?verified=1');
        Event::assertDispatched(Verified::class, fn (Verified $event): bool => $event->user->is($user));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_an_invalid_hash(): void
    {
        $user = $this->unverifiedUser();

        $verificationUrl = $this->verificationUrl($user, sha1('wrong-email'), now()->addMinutes(60));

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertStatus(403);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_an_expired_link(): void
    {
        $user = $this->unverifiedUser();

        $verificationUrl = $this->verificationUrl($user, sha1($user->email), now()->subMinutes(5));

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertStatus(403);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_can_request_a_resend_of_the_verification_email(): void
    {
        Notification::fake();

        $user = $this->unverifiedUser(['email' => 'student@example.com']);

        $response = $this->actingAs($user)
            ->from('/verify-email')
            ->post('/email/verification-notification');

        $response
            ->assertRedirect('/verify-email')
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_verification_is_rate_limited(): void
    {
        $user = $this->unverifiedUser();

        $this->actingAs($user);

        for ($i = 0; $i < 6; $i++) {
            $this->post('/email/verification-notification');
        }

        // This app renders web throttle exceptions as a redirect carrying a
        // session error (see bootstrap/app.php), so the 7th attempt inside
        // the minute is refused rather than sending another email.
        $this->post('/email/verification-notification')
            ->assertSessionHasErrors('throttle');
    }

    public function test_unverified_users_are_redirected_away_from_verified_only_areas(): void
    {
        $user = $this->unverifiedUser();

        $this->actingAs($user)
            ->get(route('dashboard.index', absolute: false))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_users_can_access_their_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard.index', absolute: false))
            ->assertOk();
    }

    public function test_welcome_email_is_queued_once_after_first_verification(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        EmailTemplate::factory()->create([
            'key' => 'welcome_email',
            'subject' => 'Welcome {student_name}',
            'body' => '<p>Welcome aboard, {student_name}!</p>',
            'variables' => ['student_name'],
        ]);

        $user = $this->unverifiedUser(['email' => 'welcome@example.com', 'name' => 'Rahim Uddin']);

        Queue::fake([SendEmailJob::class]);

        $verificationUrl = $this->verificationUrl($user, sha1($user->email), now()->addMinutes(60));

        $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'welcome_email',
            'recipient_email' => 'welcome@example.com',
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        // Visiting the link again must not queue a second welcome email.
        $this->actingAs($user)->get($verificationUrl);

        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_changing_the_email_address_requires_re_verification(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $user = User::factory()->create();

        Queue::fake([SendEmailJob::class]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => 'new-address@example.com',
            ])
            ->assertRedirect('/profile');

        $fresh = $user->fresh();

        $this->assertSame('new-address@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'email_verification',
            'recipient_email' => 'new-address@example.com',
            'status' => EmailLog::STATUS_QUEUED,
        ]);
        Queue::assertPushed(SendEmailJob::class, 1);
    }
}
