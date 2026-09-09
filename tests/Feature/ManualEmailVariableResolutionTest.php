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

class ManualEmailVariableResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function template(array $overrides = []): EmailTemplate
    {
        return EmailTemplate::factory()->create(array_merge([
            'key' => 'campaign_'.fake()->unique()->numberBetween(1, 999999),
            'is_active' => true,
        ], $overrides));
    }

    protected function subscribedLead(string $email, ?string $name): Lead
    {
        return Lead::factory()->create([
            'email' => $email,
            'name' => $name,
            'is_subscribed' => true,
        ]);
    }

    public function test_bulk_recipients_each_get_their_own_rendered_name(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template([
            'key' => 'lead_hello',
            'subject' => 'Hi {student_name}',
            'body' => '<p>Welcome {student_name}!</p>',
            'variables' => ['student_name'],
        ]);

        $this->subscribedLead('anna@example.com', 'Anna Smith');
        $this->subscribedLead('bruno@example.com', 'Bruno Jones');

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm(['template_id' => (string) $template->id])
            ->fillForm(['target_audience' => 'all'])
            ->call('send')
            ->assertNotified('Emails Sent Successfully!');

        $this->assertDatabaseCount('email_logs', 2);

        $byRecipient = EmailLog::query()
            ->where('template_key', 'lead_hello')
            ->get()
            ->keyBy('recipient_email');

        $this->assertSame('Hi Anna Smith', $byRecipient['anna@example.com']->subject);
        $this->assertSame('<p>Welcome Anna Smith!</p>', $byRecipient['anna@example.com']->body);
        $this->assertSame('Hi Bruno Jones', $byRecipient['bruno@example.com']->subject);
        $this->assertSame('<p>Welcome Bruno Jones!</p>', $byRecipient['bruno@example.com']->body);
        $this->assertNotSame($byRecipient['anna@example.com']->body, $byRecipient['bruno@example.com']->body);

        Queue::assertPushed(SendEmailJob::class, 2);
    }

    public function test_a_typed_name_is_never_shared_across_bulk_recipients(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template([
            'key' => 'lead_hello',
            'subject' => 'Hi {student_name}',
            'body' => '<p>Welcome {student_name}!</p>',
            'variables' => ['student_name'],
        ]);

        $this->subscribedLead('anna@example.com', 'Anna Smith');

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => (string) $template->id,
                'target_audience' => 'all',
                'variables' => ['student_name' => 'WRONG SHARED NAME'],
            ])
            ->call('send')
            ->assertNotified('Emails Sent Successfully!');

        $log = EmailLog::where('recipient_email', 'anna@example.com')->firstOrFail();

        $this->assertStringContainsString('Anna Smith', $log->body);
        $this->assertStringNotContainsString('WRONG SHARED NAME', $log->body);
    }

    public function test_a_missing_recipient_name_aborts_the_whole_batch_before_queueing(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template([
            'key' => 'lead_hello',
            'subject' => 'Hi {student_name}',
            'body' => '<p>Welcome {student_name}!</p>',
            'variables' => ['student_name'],
        ]);

        $this->subscribedLead('ok@example.com', 'Ok Recipient');
        $this->subscribedLead('noname@example.com', '');

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => (string) $template->id,
                'target_audience' => 'all',
            ])
            ->call('send')
            ->assertNotified('Recipient Data Incomplete');

        // Nothing was queued — no partial batch for recipient #1.
        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_campaign_variables_typed_once_apply_identically_to_every_recipient(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template([
            'key' => 'course_campaign',
            'subject' => '{student_name}, try {product_title}',
            'body' => '<p>{student_name}, this campaign is about {product_title}.</p>',
            'variables' => ['student_name', 'product_title'],
        ]);

        $this->subscribedLead('anna@example.com', 'Anna Smith');
        $this->subscribedLead('bruno@example.com', 'Bruno Jones');

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => (string) $template->id,
                'target_audience' => 'all',
                'variables' => ['product_title' => 'HSK 1 Bundle'],
            ])
            ->call('send')
            ->assertNotified('Emails Sent Successfully!');

        $bodies = EmailLog::where('template_key', 'course_campaign')
            ->pluck('body', 'recipient_email');

        $this->assertStringContainsString('Anna Smith, this campaign is about HSK 1 Bundle.', $bodies['anna@example.com']);
        $this->assertStringContainsString('Bruno Jones, this campaign is about HSK 1 Bundle.', $bodies['bruno@example.com']);
    }

    public function test_an_undeclared_placeholder_is_rejected_before_any_email_is_queued(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template([
            'key' => 'bad_placeholder',
            'subject' => 'Start {course_start_date}',
            'body' => '<p>Your course starts {course_start_date}.</p>',
            'variables' => [],
        ]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => (string) $template->id,
                'target_audience' => 'single',
                'recipient_email' => 'student@example.com',
            ])
            ->call('send')
            ->assertNotified('Unresolved Placeholder')
            ->assertNotNotified('Emails Sent Successfully!');

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_settings_backed_variables_still_resolve_automatically(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $template = $this->template([
            'key' => 'settings_ok',
            'subject' => 'Payment options',
            'body' => '<p>Contact {contact_email} or pay via bKash {bkash_number}.</p>',
            'variables' => [],
        ]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(SendManualEmail::class)
            ->fillForm([
                'template_id' => (string) $template->id,
                'target_audience' => 'single',
                'recipient_email' => 'student@example.com',
            ])
            ->call('send')
            ->assertNotified('Emails Sent Successfully!');

        $log = EmailLog::where('recipient_email', 'student@example.com')->firstOrFail();

        $this->assertStringContainsString('info@banglaychinese.com', $log->body);
        $this->assertStringNotContainsString('{contact_email}', $log->body);
        $this->assertStringNotContainsString('{bkash_number}', $log->body);
    }

    public function test_all_shipped_templates_render_without_leftover_placeholders(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        $definitions = [
            ['key' => 'product_approved', 'variables' => ['student_name', 'product_title', 'download_link'],
                'subject' => 'Hi {student_name}, {product_title} is ready', 'body' => '<p>Get it at {download_link}</p>'],
            ['key' => 'payment_reminder', 'variables' => ['student_name', 'course_title', 'amount_due', 'due_date'],
                'subject' => 'Payment reminder: {course_title}',
                'body' => '<p>Hi {student_name}, pay {amount_due} by {due_date} or use bKash {bkash_number} / {contact_email}</p>'],
            ['key' => 'course_enrollment_confirmation', 'variables' => ['student_name', 'course_title'],
                'subject' => 'Welcome to {course_title}', 'body' => '<p>Hi {student_name}</p>'],
            ['key' => 'welcome_email', 'variables' => ['student_name'], 'subject' => 'Welcome {student_name}', 'body' => '<p>Hi {student_name}</p>'],
            ['key' => 'application_received', 'variables' => ['student_name', 'desired_program'],
                'subject' => 'Application for {desired_program}', 'body' => '<p>Hi {student_name}</p>'],
            ['key' => 'contact_inquiry_received', 'variables' => ['student_name'], 'subject' => 'Thanks {student_name}', 'body' => '<p>Hi {student_name}</p>'],
        ];

        $values = [
            'student_name' => 'Rahim Uddin',
            'product_title' => 'HSK 1',
            'download_link' => 'https://example.com/dl',
            'course_title' => 'HSK 1 Crash Course',
            'amount_due' => '4,500.00',
            'due_date' => '10 Sep 2026',
            'desired_program' => 'Computer Science',
        ];

        Queue::fake([SendEmailJob::class]);

        foreach ($definitions as $index => $definition) {
            $template = $this->template($definition);

            Livewire::actingAs($this->admin())
                ->test(SendManualEmail::class)
                ->fillForm([
                    'template_id' => (string) $template->id,
                    'target_audience' => 'single',
                    'recipient_email' => "student{$index}@example.com",
                    'variables' => collect($definition['variables'])
                        ->mapWithKeys(fn (string $variable): array => [$variable => $values[$variable] ?? 'value'])
                        ->all(),
                ])
                ->call('send')
                ->assertNotified('Emails Sent Successfully!');
        }

        $this->assertDatabaseCount('email_logs', count($definitions));

        foreach (EmailLog::all() as $log) {
            $this->assertStringNotContainsString('{', $log->subject);
            $this->assertStringNotContainsString('{', $log->body);
        }
    }
}
