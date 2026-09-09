<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\ScholarshipApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScholarshipEmailFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'phone' => '01712345678',
            'highest_qualification' => 'HSC',
            'desired_program' => 'Chinese Language Program',
            'target_intake' => 'September 2027',
            'statement_of_purpose' => 'I want to study in China to pursue a degree in Computer Science. This is my dream and I am fully dedicated to achieving it through hard work and perseverance.',
        ], $overrides);
    }

    protected function applicationTemplate(): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'key' => 'application_received',
            'subject' => 'Application received for {desired_program}',
            'body' => '<p>Thank you, {student_name}, we received your {desired_program} application.</p>',
            'variables' => ['student_name', 'desired_program'],
        ]);
    }

    public function test_a_successful_application_queues_the_acknowledgement(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->applicationTemplate();

        Queue::fake([SendEmailJob::class]);

        $response = $this->from(route('study-in-china.consultation'))
            ->post(route('study-in-china.apply'), $this->validPayload());

        $response
            ->assertRedirect(route('study-in-china.consultation'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('scholarship_applications', [
            'email' => 'rahim@example.com',
            'desired_program' => 'Chinese Language Program',
        ]);

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'application_received',
            'recipient_email' => 'rahim@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Application received for Chinese Language Program',
            'status' => EmailLog::STATUS_QUEUED,
            'attempt_count' => 0,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->to === 'rahim@example.com'
                && $job->templateKey === 'application_received'
                && $job->htmlContent === '<p>Thank you, Rahim Uddin, we received your Chinese Language Program application.</p>';
        });
    }

    public function test_the_acknowledgement_is_sent_to_the_applicant(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->applicationTemplate();

        Queue::fake([SendEmailJob::class]);

        $this->post(route('study-in-china.apply'), $this->validPayload(['email' => 'applicant@example.com']));

        $this->assertDatabaseHas('email_logs', ['recipient_email' => 'applicant@example.com']);
        $this->assertDatabaseMissing('email_logs', ['recipient_email' => 'rahim@example.com']);
    }

    public function test_no_email_is_queued_when_the_database_write_fails(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->applicationTemplate();

        Queue::fake([SendEmailJob::class]);

        ScholarshipApplication::creating(function (): never {
            throw new \RuntimeException('database unavailable');
        });

        $this->post(route('study-in-china.apply'), $this->validPayload())
            ->assertStatus(500);

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_a_missing_template_never_breaks_the_submission(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        Queue::fake([SendEmailJob::class]);

        $this->post(route('study-in-china.apply'), $this->validPayload())
            ->assertRedirect(route('study-in-china.consultation'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }
}
