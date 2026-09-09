<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailService;
use App\Support\EmailTemplatePlaceholders;
use Database\Seeders\EmailSystemSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class EmailTemplateSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'is_admin' => false,
        ]);
    }

    private function seedTemplates(): void
    {
        $this->seed(EmailSystemSeeder::class);
    }

    public function test_every_discovered_production_email_has_a_template_record(): void
    {
        $this->seedTemplates();

        $expectedKeys = [
            'product_approved',
            'payment_reminder',
            'course_enrollment_confirmation',
            'welcome_email',
            'application_received',
            'contact_inquiry_received',
            'email_verification',
            'password_reset',
            'order_received_payment_pending',
            'payment_verification_failed',
            'payment_information_needs_attention',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertDatabaseHas('email_templates', ['key' => $key]);
        }

        $this->assertSame(count($expectedKeys), EmailTemplate::count());
    }

    public function test_template_keys_are_unique(): void
    {
        $this->seedTemplates();

        $keys = EmailTemplate::query()->pluck('key')->all();

        $this->assertSame(count($keys), count(array_unique($keys)));
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seedTemplates();
        $countAfterFirstSeed = EmailTemplate::count();

        $this->seedTemplates();

        $this->assertSame($countAfterFirstSeed, EmailTemplate::count());
    }

    public function test_seeder_does_not_overwrite_administrator_edits(): void
    {
        EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'name' => 'Admin Branded Download Email',
            'subject' => 'Custom admin subject',
            'body' => '<p>Admin authored body</p>',
            'description' => 'Kept description',
        ]);

        $this->seedTemplates();

        $fresh = EmailTemplate::where('key', 'product_approved')->firstOrFail();

        $this->assertSame('Admin Branded Download Email', $fresh->name);
        $this->assertSame('Custom admin subject', $fresh->subject);
        $this->assertSame('<p>Admin authored body</p>', $fresh->body);
        $this->assertSame('Kept description', $fresh->description);
    }

    public function test_seeder_backfills_description_only_on_legacy_rows(): void
    {
        EmailTemplate::factory()->create([
            'key' => 'welcome_email',
            'description' => null,
        ]);

        $this->seedTemplates();

        $this->assertNotNull(EmailTemplate::where('key', 'welcome_email')->firstOrFail()->description);
    }

    public function test_template_variables_are_validated_at_send_time(): void
    {
        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}, your {product_title} is ready',
            'body' => '<p>Download: {download_link}</p>',
            'variables' => ['student_name', 'product_title', 'download_link'],
        ]);

        Queue::fake([SendEmailJob::class]);

        try {
            app(EmailService::class)->sendTemplate('product_approved', 'student@example.com', [
                'student_name' => 'Rahim',
            ]);

            $this->fail('sendTemplate should reject unresolved placeholders.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('{product_title}', $exception->getMessage());
            $this->assertStringContainsString('{download_link}', $exception->getMessage());
        }

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_unused_declared_variables_do_not_block_a_send(): void
    {
        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'simple_note',
            'subject' => 'Hello {name}',
            'body' => '<p>Welcome</p>',
            'variables' => ['name', 'optional_unused'],
        ]);

        Queue::fake([SendEmailJob::class]);

        $result = app(EmailService::class)->sendTemplate('simple_note', 'student@example.com', ['name' => 'Rahim']);

        $this->assertTrue($result['success']);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_preview_does_not_send_an_email(): void
    {
        $template = EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'variables' => ['student_name', 'download_link'],
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailTemplates::class)
            ->mountTableAction('preview', $template)
            ->assertFormFieldExists('value_student_name')
            ->assertFormFieldExists('value_download_link');

        $this->assertDatabaseCount('email_logs', 0);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_preview_sample_data_contains_no_real_recipient_information(): void
    {
        $template = EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}',
            'body' => '<p>Email {student_name} at {download_link}</p>',
            'variables' => ['student_name', 'download_link'],
        ]);

        $rendered = app(EmailService::class)->renderTemplate($template, [
            'student_name' => EmailTemplatePlaceholders::sampleValue('student_name'),
            'download_link' => EmailTemplatePlaceholders::sampleValue('download_link'),
        ]);

        $this->assertSame('Hi Demo Student', $rendered['subject']);
        $this->assertStringContainsString('Demo Student', $rendered['body']);
        $this->assertStringNotContainsString('@', EmailTemplatePlaceholders::sampleValue('download_link'));
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_test_send_action_queues_through_the_normal_email_pipeline(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        $template = EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}, your {product_title} is ready',
            'body' => '<p>Download: {download_link}</p>',
            'variables' => ['student_name', 'product_title', 'download_link'],
        ]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailTemplates::class)
            ->callAction(
                TestAction::make('testSend')->table($template),
                ['recipient_email' => 'admin@banglaychinese.com'],
            )
            ->assertNotified('Test email queued');

        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'product_approved',
            'recipient_email' => 'admin@banglaychinese.com',
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->templateKey === 'product_approved'
                && $job->to === 'admin@banglaychinese.com';
        });
    }

    public function test_inactive_template_cannot_be_used_to_send(): void
    {
        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Hi {student_name}',
            'body' => '<p>Hi {student_name}</p>',
            'variables' => ['student_name'],
            'is_active' => false,
        ]);

        Queue::fake([SendEmailJob::class]);

        try {
            app(EmailService::class)->sendTemplate('product_approved', 'student@example.com', ['student_name' => 'Rahim']);

            $this->fail('Inactive template must not send.');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }

        $this->assertDatabaseCount('email_logs', 0);
        Queue::assertNothingPushed();
    }

    public function test_verification_email_uses_the_active_template_row_when_present(): void
    {
        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'email_verification',
            'subject' => 'Custom verify {appName}',
            'body' => '<p>Hi {name}, click <a href="{url}">{url}</a> within {expireMinutes} minutes.</p>',
            'variables' => ['name', 'appName', 'url', 'expireMinutes'],
            'is_active' => true,
        ]);

        $user = User::factory()->create(['name' => 'Rahim Uddin']);

        Queue::fake([SendEmailJob::class]);

        $user->sendEmailVerificationNotification();

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->templateKey === 'email_verification'
                && $job->subject === 'Custom verify Banglay Chinese'
                && str_contains($job->htmlContent, 'Hi Rahim Uddin')
                && str_contains($job->htmlContent, 'within 60 minutes');
        });
    }

    public function test_verification_email_falls_back_to_the_bundled_default_when_the_template_is_inactive(): void
    {
        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'email_verification',
            'subject' => 'Custom verify {appName}',
            'body' => '<p>Hi {name}</p>',
            'variables' => ['name', 'appName', 'url', 'expireMinutes'],
            'is_active' => false,
        ]);

        $user = User::factory()->create(['name' => 'Rahim Uddin']);

        Queue::fake([SendEmailJob::class]);

        $user->sendEmailVerificationNotification();

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job): bool {
            return $job->templateKey === 'email_verification'
                && $job->subject === 'Verify your Banglay Chinese email'
                && str_contains($job->htmlContent, 'expire in');
        });
    }

    public function test_email_log_carries_the_template_key(): void
    {
        EmailProvider::factory()->create();

        EmailTemplate::factory()->create([
            'key' => 'welcome_email',
            'subject' => 'Welcome {student_name}',
            'body' => '<p>Hi {student_name}</p>',
            'variables' => ['student_name'],
        ]);

        Queue::fake([SendEmailJob::class]);

        app(EmailService::class)->sendTemplate('welcome_email', 'student@example.com', ['student_name' => 'Rahim']);

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'student@example.com',
            'template_key' => 'welcome_email',
        ]);
    }

    public function test_non_admin_cannot_manage_email_templates(): void
    {
        $this->actingAs($this->student())
            ->get(ListEmailTemplates::getUrl())
            ->assertForbidden();
    }

    public function test_template_content_cannot_execute_arbitrary_php(): void
    {
        EmailTemplate::factory()->create([
            'key' => 'php_probe',
            'subject' => 'Subject',
            'body' => '<p><?php echo "HACKED123"; ?></p>',
            'variables' => [],
        ]);

        $template = EmailTemplate::where('key', 'php_probe')->firstOrFail();

        $rendered = app(EmailService::class)->renderTemplate($template, []);

        $this->assertStringContainsString('<?php echo "HACKED123"; ?>', $rendered['body']);
        $this->assertStringNotContainsString('>HACKED123<', $rendered['body']);
    }

    public function test_admin_can_duplicate_a_template_as_an_inactive_copy(): void
    {
        $template = EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'name' => 'Product Download Ready',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailTemplates::class)
            ->callAction(TestAction::make('duplicate')->table($template))
            ->assertNotified('Template duplicated');

        $copy = EmailTemplate::where('key', 'product_approved-copy')->firstOrFail();

        $this->assertSame('Product Download Ready (Copy)', $copy->name);
        $this->assertFalse($copy->is_active);
        $this->assertDatabaseCount('email_templates', 2);
    }

    public function test_required_templates_cannot_be_deleted_or_deactivated(): void
    {
        $required = EmailTemplate::factory()->create([
            'key' => 'password_reset',
            'name' => 'Password Reset',
        ]);

        $regular = EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'name' => 'Product Download',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailTemplates::class)
            ->assertTableActionHidden('delete', $required)
            ->assertTableActionVisible('delete', $regular);

        Livewire::actingAs($this->admin())
            ->test(EditEmailTemplate::class, ['record' => $required->getKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($required->fresh()->is_active);
    }
}
