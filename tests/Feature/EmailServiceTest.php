<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailService;
use App\Services\SettingsService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): EmailService
    {
        return app(EmailService::class);
    }

    /**
     * Bind a partial mock of the service whose delivery seam can be stubbed,
     * and return it. Real provider/template lookups still run.
     */
    private function serviceWithStubbedDelivery(): EmailService
    {
        $service = Mockery::mock(EmailService::class, [app('mail.manager')])->makePartial();

        $this->app->instance(EmailService::class, $service);

        return $service;
    }

    public function test_send_queues_the_email_without_sending_synchronously(): void
    {
        EmailProvider::factory()->create();
        EmailProvider::factory()->create(['priority' => 2]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->send('student@example.com', 'Your course is ready', '<p>Hello</p>');

        $this->assertSame(['success' => true, 'queued' => true, 'log_id' => 1], $result);
        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'provider_id' => null,
            'recipient_email' => 'student@example.com',
            'recipient_name' => null,
            'subject' => 'Your course is ready',
            'body' => '<p>Hello</p>',
            'status' => EmailLog::STATUS_QUEUED,
            'attempt_count' => 0,
            'error_message' => null,
        ]);

        $fresh = EmailLog::firstOrFail();

        $this->assertNull($fresh->sent_at);
        $this->assertNotNull($fresh->queued_at);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->emailLogId === 1
                && $job->to === 'student@example.com'
                && $job->subject === 'Your course is ready'
                && $job->htmlContent === '<p>Hello</p>';
        });
    }

    public function test_send_returns_an_error_when_no_provider_is_active(): void
    {
        EmailProvider::factory()->create(['is_active' => false]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->send('student@example.com', 'Your course is ready', '<p>Hello</p>');

        $this->assertSame(['success' => false, 'error' => 'No email providers configured'], $result);
        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_send_template_renders_placeholders_into_the_queued_email(): void
    {
        EmailProvider::factory()->create();
        EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}, {product_title} is ready',
            'body' => '<p>Download: {download_link}</p>',
            'variables' => ['student_name', 'product_title', 'download_link'],
        ]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->sendTemplate('product_approved', 'student@example.com', [
            'student_name' => 'Rahim Uddin',
            'product_title' => 'HSK 1 Course',
            'download_link' => 'https://example.com/downloads/hsk1',
        ], 'Rahim Uddin');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'product_approved',
            'recipient_email' => 'student@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Hi Rahim Uddin, HSK 1 Course is ready',
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->templateKey === 'product_approved'
                && $job->htmlContent === '<p>Download: https://example.com/downloads/hsk1</p>'
                && $job->recipientName === 'Rahim Uddin';
        });
    }

    public function test_send_template_throws_when_the_key_is_unknown(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service()->sendTemplate('missing_key', 'student@example.com');
    }

    public function test_send_template_throws_when_the_template_is_inactive(): void
    {
        EmailTemplate::factory()->create(['key' => 'product_approved', 'is_active' => false]);

        $this->expectException(ModelNotFoundException::class);

        $this->service()->sendTemplate('product_approved', 'student@example.com');
    }

    public function test_send_template_injects_settings_backed_variables(): void
    {
        SettingsService::set('bkash_number', '01711111111');
        SettingsService::set('nagad_number', '01722222222');

        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'payment_reminder',
            'subject' => 'Payment reminder',
            'body' => '<p>bKash: {bkash_number} — Nagad: {nagad_number} — WhatsApp: {whatsapp_number} — Support: {contact_email}</p>',
            'variables' => ['course_title', 'amount_due'],
        ]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->sendTemplate('payment_reminder', 'student@example.com');

        $this->assertTrue($result['success']);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->htmlContent === '<p>bKash: 01711111111 — Nagad: 01722222222 — WhatsApp: Not set — Support: info@banglaychinese.com</p>';
        });
    }

    public function test_caller_provided_variables_override_settings_backed_ones(): void
    {
        SettingsService::set('bkash_number', '01711111111');

        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'payment_reminder',
            'subject' => 'Payment reminder',
            'body' => '<p>bKash: {bkash_number}</p>',
            'variables' => ['bkash_number'],
        ]);

        Queue::fake([SendEmailJob::class]);

        $this->service()->sendTemplate('payment_reminder', 'student@example.com', [
            'bkash_number' => '01899999999',
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->htmlContent === '<p>bKash: 01899999999</p>';
        });
    }

    public function test_send_template_reads_the_contact_email_setting(): void
    {
        SettingsService::set('contact_email', 'hello@banglaychinese.com');

        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'contact_reminder',
            'subject' => 'Reminder',
            'body' => '<p>Email us at {contact_email}</p>',
            'variables' => [],
        ]);

        Queue::fake([SendEmailJob::class]);

        $this->service()->sendTemplate('contact_reminder', 'student@example.com');

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->htmlContent === '<p>Email us at hello@banglaychinese.com</p>';
        });
    }

    public function test_render_template_returns_subject_and_body_without_sending(): void
    {
        $template = EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}, your {product_title} is ready',
            'body' => '<p>Download it here: {download_link}</p>',
        ]);

        Queue::fake([SendEmailJob::class]);

        $rendered = $this->service()->renderTemplate($template, [
            'student_name' => 'Rahim Uddin',
            'product_title' => 'HSK 1 Course',
            'download_link' => 'https://example.com/downloads/hsk1',
        ]);

        $this->assertSame('Hi Rahim Uddin, your HSK 1 Course is ready', $rendered['subject']);
        $this->assertSame('<p>Download it here: https://example.com/downloads/hsk1</p>', $rendered['body']);
        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_template_sender_is_queued_with_the_email(): void
    {
        EmailProvider::factory()->create([
            'config' => ['from_address' => 'provider@banglaychinese.com', 'from_name' => 'Provider Sender'],
        ]);

        EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Your download',
            'body' => '<p>Hi</p>',
            'from_address' => 'no-reply@banglaychinese.com',
            'from_name' => 'Banglay Chinese Downloads',
        ]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->sendTemplate('product_approved', 'student@example.com');

        $this->assertTrue($result['success']);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->fromAddress === 'no-reply@banglaychinese.com'
                && $job->fromName === 'Banglay Chinese Downloads';
        });
    }

    public function test_template_without_sender_falls_back_to_the_provider_sender_at_delivery(): void
    {
        $provider = EmailProvider::factory()->create([
            'config' => ['from_address' => 'provider@banglaychinese.com', 'from_name' => 'Provider Sender'],
        ]);

        EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Your download',
            'body' => '<p>Hi</p>',
        ]);

        Queue::fake([SendEmailJob::class]);

        $this->service()->sendTemplate('product_approved', 'student@example.com');

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job) use ($provider): bool {
            return $job->fromAddress === null
                && $job->fromName === null
                && $provider->config['from_address'] === 'provider@banglaychinese.com';
        });
    }

    public function test_send_in_sync_mode_delivers_immediately_and_reports_the_provider(): void
    {
        $provider = EmailProvider::factory()->create();

        $service = $this->serviceWithStubbedDelivery();
        $service->shouldReceive('sendViaProvider')->once();

        $result = $service->send('student@example.com', 'Your course is ready', '<p>Hello</p>', null, null, null, null, true);

        $this->assertSame(['success' => true, 'provider' => $provider->name], $result);
        $this->assertDatabaseHas('email_logs', [
            'provider_id' => $provider->id,
            'status' => EmailLog::STATUS_SENT,
            'error_message' => null,
        ]);
        $this->assertSame(1, $provider->fresh()->sent_today);
    }

    public function test_send_in_sync_mode_reports_a_failure_when_delivery_throws(): void
    {
        $provider = EmailProvider::factory()->create();

        $service = $this->serviceWithStubbedDelivery();
        $service->shouldReceive('sendViaProvider')->andThrow(new Exception('Connection refused'));

        $result = $service->send('student@example.com', 'Your course is ready', '<p>Hello</p>', null, null, null, null, true);

        $this->assertSame(['success' => false, 'error' => 'Connection refused'], $result);
        $this->assertDatabaseHas('email_logs', [
            'provider_id' => $provider->id,
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'Connection refused',
        ]);
        $this->assertSame(0, $provider->fresh()->sent_today);
    }

    public function test_test_connection_prefers_an_explicit_recipient(): void
    {
        $admin = User::factory()->create(['email' => 'admin@banglaychinese.com']);
        $provider = EmailProvider::factory()->create();

        $this->actingAs($admin);

        $service = $this->serviceWithStubbedDelivery();
        $service->shouldReceive('sendViaProvider')->once();

        $result = $service->testConnection($provider, 'operator@banglaychinese.com');

        $this->assertSame(['success' => true, 'message' => 'Test email sent to operator@banglaychinese.com'], $result);
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_test_connection_sends_to_the_authenticated_user_without_logging(): void
    {
        $admin = User::factory()->create(['email' => 'admin@banglaychinese.com']);
        $provider = EmailProvider::factory()->create();

        $this->actingAs($admin);

        $service = $this->serviceWithStubbedDelivery();
        $service->shouldReceive('sendViaProvider')->once();

        $result = $service->testConnection($provider);

        $this->assertSame(['success' => true, 'message' => 'Test email sent to admin@banglaychinese.com'], $result);
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_test_connection_reports_credentials_failure(): void
    {
        $provider = EmailProvider::factory()->create();

        $service = $this->serviceWithStubbedDelivery();
        $service->shouldReceive('sendViaProvider')->andThrow(new Exception('Invalid credentials'));

        $result = $service->testConnection($provider);

        $this->assertSame(['success' => false, 'message' => 'Invalid credentials'], $result);
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_test_connection_never_leaks_credentials_in_the_error_message(): void
    {
        $provider = EmailProvider::factory()->create([
            'config' => ['password' => 'hunter2-secret-key'],
        ]);

        $service = $this->serviceWithStubbedDelivery();
        $service->shouldReceive('sendViaProvider')->andThrow(new Exception('SMTP auth failed for hunter2-secret-key'));

        $result = $service->testConnection($provider);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('[REDACTED]', $result['message']);
        $this->assertStringNotContainsString('hunter2-secret-key', $result['message']);
    }

    public function test_resend_queues_a_fresh_attempt_and_preserves_the_original_failed_log(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        $original = EmailLog::factory()->create([
            'recipient_email' => 'student@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Your download is ready',
            'body' => '<p>Download it here: https://example.com/dl</p>',
            'template_key' => 'product_approved',
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'Connection refused',
            'attempt_count' => 2,
        ]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->resend($original);

        $this->assertTrue($result['success']);
        $this->assertDatabaseCount('email_logs', 2);

        $this->assertSame(EmailLog::STATUS_FAILED, $original->fresh()->status);
        $this->assertSame(2, $original->fresh()->attempt_count);

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'student@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Your download is ready',
            'body' => '<p>Download it here: https://example.com/dl</p>',
            'template_key' => 'product_approved',
            'from_address' => null,
            'status' => EmailLog::STATUS_QUEUED,
            'attempt_count' => 0,
            'error_message' => null,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job) use ($original): bool {
            return $job->emailLogId !== $original->id
                && $job->to === 'student@example.com'
                && $job->htmlContent === '<p>Download it here: https://example.com/dl</p>'
                && $job->templateKey === 'product_approved';
        });
    }

    public function test_resend_refuses_a_successful_log(): void
    {
        $sent = EmailLog::factory()->create([
            'recipient_email' => 'student@example.com',
            'body' => '<p>Already delivered</p>',
            'status' => EmailLog::STATUS_SENT,
        ]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->resend($sent);

        $this->assertSame(['success' => false, 'error' => 'Only failed emails with a stored body can be resent.'], $result);
        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertNothingPushed();
    }

    public function test_resend_refuses_a_failed_log_without_a_stored_body(): void
    {
        $failed = EmailLog::factory()->create([
            'recipient_email' => 'student@example.com',
            'body' => null,
            'status' => EmailLog::STATUS_FAILED,
            'error_message' => 'Connection refused',
        ]);

        Queue::fake([SendEmailJob::class]);

        $result = $this->service()->resend($failed);

        $this->assertSame(['success' => false, 'error' => 'Only failed emails with a stored body can be resent.'], $result);
        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertNothingPushed();
    }
}
