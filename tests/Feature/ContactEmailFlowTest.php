<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\ContactMessage;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContactEmailFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'email' => 'rahim@example.com',
            'topic' => 'General Inquiry',
            'message' => 'I would like to know more about your courses.',
        ], $overrides);
    }

    protected function acknowledgementTemplate(): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'key' => 'contact_inquiry_received',
            'subject' => 'Thanks {student_name}, we got your message',
            'body' => '<p>Hi {student_name}, we will reply within 24 hours.</p>',
            'variables' => ['student_name'],
        ]);
    }

    public function test_a_successful_contact_submission_queues_the_acknowledgement(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->acknowledgementTemplate();

        Queue::fake([SendEmailJob::class]);

        $response = $this->from('/contact')
            ->post(route('contact.send'), $this->validPayload());

        $response
            ->assertRedirect('/contact')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'rahim@example.com',
            'name' => 'Rahim Uddin',
        ]);

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'contact_inquiry_received',
            'recipient_email' => 'rahim@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Thanks Rahim Uddin, we got your message',
            'status' => EmailLog::STATUS_QUEUED,
            'attempt_count' => 0,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->to === 'rahim@example.com'
                && $job->templateKey === 'contact_inquiry_received'
                && $job->htmlContent === '<p>Hi Rahim Uddin, we will reply within 24 hours.</p>';
        });
    }

    public function test_the_acknowledgement_is_sent_to_the_person_who_submitted_the_form(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->acknowledgementTemplate();

        Queue::fake([SendEmailJob::class]);

        $this->from('/contact')
            ->post(route('contact.send'), $this->validPayload(['email' => 'someone-else@example.com']));

        $this->assertDatabaseHas('email_logs', ['recipient_email' => 'someone-else@example.com']);
        $this->assertDatabaseMissing('email_logs', ['recipient_email' => 'rahim@example.com']);
    }

    public function test_no_email_is_queued_when_the_database_write_fails(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->acknowledgementTemplate();

        Queue::fake([SendEmailJob::class]);

        ContactMessage::creating(function (): never {
            throw new \RuntimeException('database unavailable');
        });

        $this->from('/contact')
            ->post(route('contact.send'), $this->validPayload())
            ->assertStatus(500);

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_a_missing_template_never_breaks_the_submission(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        Queue::fake([SendEmailJob::class]);

        $this->from('/contact')
            ->post(route('contact.send'), $this->validPayload())
            ->assertRedirect('/contact')
            ->assertSessionHas('success');

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }
}
