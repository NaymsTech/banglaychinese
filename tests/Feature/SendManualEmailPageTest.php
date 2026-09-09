<?php

namespace Tests\Feature;

use App\Filament\Pages\SendManualEmail;
use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class SendManualEmailPageTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function template(): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'name' => 'Product Download Ready',
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}, your download is ready',
            'body' => '<p>Download it here: {download_link}</p>',
            'variables' => ['student_name', 'download_link'],
        ]);
    }

    public function test_admin_can_open_the_send_email_page(): void
    {
        $this->actingAs($this->admin())
            ->get(SendManualEmail::getUrl())
            ->assertOk();
    }

    public function test_send_requires_a_template_selection(): void
    {
        $this->template();

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'target_audience' => 'single',
                'recipient_email' => 'student@example.com',
            ])
            ->call('send')
            ->assertNotified('Template Required')
            ->assertNotNotified('Emails Sent Successfully!');
    }

    public function test_page_prefills_a_recipient_from_the_query_string(): void
    {
        $this->actingAs($this->admin())
            ->get(SendManualEmail::getUrl(['recipient_email' => 'lead@example.com']))
            ->assertOk()
            ->assertSee('lead@example.com');
    }

    public function test_send_to_a_single_address_queues_the_selected_template(): void
    {
        $template = $this->template();
        EmailProvider::factory()->create(['name' => 'Brevo']);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'single',
                'recipient_email' => 'student@example.com',
                'variables' => [
                    'student_name' => 'Rahim Uddin',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'student@example.com',
            'recipient_name' => null,
            'subject' => 'Hi Rahim Uddin, your download is ready',
            'template_key' => 'product_approved',
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->to === 'student@example.com'
                && $job->templateKey === 'product_approved'
                && $job->htmlContent === '<p>Download it here: https://example.com/downloads/hsk1</p>';
        });
    }

    public function test_send_requires_all_template_variables_to_have_values(): void
    {
        $template = $this->template();

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'single',
                'recipient_email' => 'student@example.com',
                'variables' => [
                    'student_name' => 'Rahim Uddin',
                    'download_link' => '',
                ],
            ])
            ->call('send')
            ->assertNotified('Missing Variables')
            ->assertNotNotified('Emails Sent Successfully!');
    }

    public function test_send_to_a_segment_only_queues_subscribed_matching_leads(): void
    {
        $template = $this->template();
        EmailProvider::factory()->create();

        Lead::factory()->create([
            'email' => 'match@example.com',
            'interest' => Lead::INTEREST_COURSES,
            'is_subscribed' => true,
        ]);
        Lead::factory()->create([
            'email' => 'unsubscribed@example.com',
            'interest' => Lead::INTEREST_COURSES,
            'is_subscribed' => false,
        ]);
        Lead::factory()->create([
            'email' => 'other@example.com',
            'interest' => Lead::INTEREST_GENERAL,
            'is_subscribed' => true,
        ]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'segment',
                'lead_interest' => Lead::INTEREST_COURSES,
                'variables' => [
                    'student_name' => 'Student',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', ['recipient_email' => 'match@example.com', 'status' => EmailLog::STATUS_QUEUED]);
        $this->assertDatabaseMissing('email_logs', ['recipient_email' => 'unsubscribed@example.com']);
        $this->assertDatabaseMissing('email_logs', ['recipient_email' => 'other@example.com']);

        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_send_to_all_subscribed_leads_queues_one_email_per_lead(): void
    {
        $template = $this->template();
        EmailProvider::factory()->create();

        Lead::factory()->create(['email' => 'one@example.com', 'is_subscribed' => true]);
        Lead::factory()->create(['email' => 'two@example.com', 'is_subscribed' => true]);
        Lead::factory()->create(['email' => 'optedout@example.com', 'is_subscribed' => false]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'all',
                'variables' => [
                    'student_name' => 'Student',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseCount('email_logs', 2);
        $this->assertSame(2, EmailLog::where('status', EmailLog::STATUS_QUEUED)->count());

        Queue::assertPushed(SendEmailJob::class, 2);
    }

    public function test_send_without_matching_recipients_shows_a_warning(): void
    {
        $template = $this->template();

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'target_audience' => 'all',
                'variables' => [
                    'student_name' => 'Student',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_selecting_a_template_via_the_dropdown_stores_state_and_sends(): void
    {
        $template = $this->template();
        EmailProvider::factory()->create(['name' => 'Brevo']);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->set('data.target_audience', 'single')
            ->set('data.recipient_email', 'student@example.com')
            ->set('data.template_id', (string) $template->id)
            ->assertFormFieldExists('variables.student_name')
            ->assertFormFieldExists('variables.download_link')
            ->set('data.variables.student_name', 'Rahim Uddin')
            ->set('data.variables.download_link', 'https://example.com/downloads/hsk1')
            ->call('send')
            ->assertNotified('Emails Sent Successfully!');

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'student@example.com',
            'subject' => 'Hi Rahim Uddin, your download is ready',
            'template_key' => 'product_approved',
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_the_default_single_mode_renders_the_recipient_input_without_interaction(): void
    {
        $html = Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->html();

        // On a bare page load the target must already be 'single', so the
        // recipient field is present and bound — otherwise the page shows no
        // recipient input until the user toggles the mode.
        $this->assertStringContainsString('wire:model="data.recipient_email"', $html);
        $this->assertStringContainsString('value="single"', $html);
        $this->assertStringContainsString('value="segment"', $html);
        $this->assertStringContainsString('value="all"', $html);
    }

    public function test_sending_works_in_the_default_single_mode_without_selecting_a_mode(): void
    {
        $template = $this->template();
        EmailProvider::factory()->create(['name' => 'Brevo']);

        Queue::fake([SendEmailJob::class]);

        // target_audience is intentionally omitted — the mount default must
        // already be 'single' for the send to reach the typed recipient.
        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => $template->id,
                'recipient_email' => 'student@example.com',
                'variables' => [
                    'student_name' => 'Rahim Uddin',
                    'download_link' => 'https://example.com/downloads/hsk1',
                ],
            ])
            ->call('send')
            ->assertNotified('Emails Sent Successfully!');

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'student@example.com',
            'status' => EmailLog::STATUS_QUEUED,
        ]);
        Queue::assertPushed(SendEmailJob::class, 1);
    }
}
