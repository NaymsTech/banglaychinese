<?php

namespace Tests\Feature;

use App\Console\Commands\SyncBrandedEmailTemplates;
use App\Models\EmailTemplate;
use App\Support\EmailTemplateLegacyBodies;
use Database\Seeders\EmailSystemSeeder;
use Exception;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\TestCase;

class SyncBrandedEmailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private function definitionsByKey(): array
    {
        return collect(EmailSystemSeeder::definitions())->keyBy('key')->all();
    }

    private function canonicalBody(string $key): string
    {
        return (string) ($this->definitionsByKey()[$key]['body'] ?? '');
    }

    private function legacyBody(string $key): string
    {
        return (string) EmailTemplateLegacyBodies::body($key);
    }

    private function createTemplate(string $key, string $body): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'name' => 'Template '.$key,
            'key' => $key,
            'category' => 'transactional',
            'subject' => 'Subject '.$key,
            'body' => $body,
            'variables' => [],
            'is_active' => true,
        ]);
    }

    /**
     * A mixed database state: 3 rows still on the legacy default, 6 already on
     * the branded body, 1 customized, and 1 target key missing entirely.
     */
    private function seedMixedRows(): void
    {
        $legacy = ['product_approved', 'payment_reminder', 'course_enrollment_confirmation'];
        $alreadyBranded = [
            'welcome_email',
            'application_received',
            'contact_inquiry_received',
            'email_verification',
            'order_received_payment_pending',
            'payment_verification_failed',
        ];
        $customized = 'payment_information_needs_attention';

        foreach ($legacy as $key) {
            $this->createTemplate($key, $this->legacyBody($key));
        }

        foreach ($alreadyBranded as $key) {
            $this->createTemplate($key, $this->canonicalBody($key));
        }

        $this->createTemplate($customized, '<p>Administrator authored body for {student_name}.</p>');
    }

    public function test_command_targets_exactly_the_canonical_template_keys(): void
    {
        $canonicalKeys = collect($this->definitionsByKey())->keys()->sort()->values()->all();

        $this->assertSame($canonicalKeys, collect(SyncBrandedEmailTemplates::TARGET_KEYS)->sort()->values()->all());
        $this->assertCount(11, SyncBrandedEmailTemplates::TARGET_KEYS);
    }

    public function test_dry_run_reports_planned_changes_and_changes_nothing(): void
    {
        $this->seedMixedRows();

        $this->artisan('email:sync-branded-templates')
            ->expectsOutputToContain('Dry run — no changes made.')
            ->expectsOutputToContain('3 eligible for update')
            ->expectsOutputToContain('6 already up to date')
            ->expectsOutputToContain('1 customized/unknown — skipped')
            ->expectsOutputToContain('1 missing (not created)')
            ->assertSuccessful();

        $this->assertSame($this->legacyBody('product_approved'), EmailTemplate::where('key', 'product_approved')->value('body'));
        $this->assertSame($this->legacyBody('payment_reminder'), EmailTemplate::where('key', 'payment_reminder')->value('body'));
        $this->assertStringContainsString('Administrator authored body', (string) EmailTemplate::where('key', 'payment_information_needs_attention')->value('body'));
        $this->assertSame(10, EmailTemplate::whereIn('key', SyncBrandedEmailTemplates::TARGET_KEYS)->count());
    }

    public function test_apply_updates_only_eligible_legacy_rows(): void
    {
        $this->seedMixedRows();

        $this->artisan('email:sync-branded-templates --apply')
            ->expectsOutputToContain('Updated 3 template bodies')
            ->assertSuccessful();

        foreach (['product_approved', 'payment_reminder', 'course_enrollment_confirmation'] as $key) {
            $this->assertSame($this->canonicalBody($key), EmailTemplate::where('key', $key)->value('body'));
        }

        foreach (['welcome_email', 'application_received', 'contact_inquiry_received', 'email_verification', 'order_received_payment_pending', 'payment_verification_failed'] as $key) {
            $this->assertSame($this->canonicalBody($key), EmailTemplate::where('key', $key)->value('body'));
        }

        $this->assertStringContainsString('Administrator authored body', (string) EmailTemplate::where('key', 'payment_information_needs_attention')->value('body'));
        $this->assertDatabaseMissing('email_templates', ['key' => 'password_reset']);
    }

    public function test_apply_on_a_fully_seeded_database_is_a_no_op(): void
    {
        $this->seed(EmailSystemSeeder::class);

        $this->artisan('email:sync-branded-templates --apply')
            ->expectsOutputToContain('Nothing to update.')
            ->assertSuccessful();

        $this->assertSame(11, EmailTemplate::count());

        foreach (EmailTemplate::all() as $template) {
            $this->assertSame($this->canonicalBody($template->key), $template->body);
        }
    }

    public function test_missing_row_is_reported_but_never_created(): void
    {
        $this->seed(EmailSystemSeeder::class);

        EmailTemplate::where('key', 'welcome_email')->delete();

        $output = new BufferedOutput;

        $exitCode = Artisan::call('email:sync-branded-templates', [], $output);

        $display = $output->fetch();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('welcome_email', $display);
        $this->assertStringContainsString('MISSING — row does not exist; not created', $display);
        $this->assertStringContainsString('1 missing (not created)', $display);

        $this->assertDatabaseMissing('email_templates', ['key' => 'welcome_email']);
    }

    public function test_invalid_canonical_definitions_fail_before_any_mutation(): void
    {
        $command = new #[Signature('email:sync-branded-templates {--dry-run} {--apply}')] class extends SyncBrandedEmailTemplates
        {
            protected function definitions(): array
            {
                $definitions = EmailSystemSeeder::definitions();

                return array_map(
                    static fn (array $definition): array => $definition['key'] === 'product_approved'
                        ? [...$definition, 'body' => '<p>Hi {student_name} and {undeclared_variable}</p>']
                        : $definition,
                    $definitions
                );
            }
        };

        $command->setLaravel(app());
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertNotSame(SyncBrandedEmailTemplates::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('failed validation', $tester->getDisplay());

        $this->assertSame(0, EmailTemplate::count());
    }

    public function test_apply_rolls_back_when_an_update_fails(): void
    {
        $command = new #[Signature('email:sync-branded-templates {--dry-run} {--apply}')] class extends SyncBrandedEmailTemplates
        {
            protected function legacyBodies(): array
            {
                return [
                    'product_approved' => EmailTemplateLegacyBodies::body('product_approved'),
                    'payment_reminder' => EmailTemplateLegacyBodies::body('payment_reminder'),
                ];
            }

            protected function updateBody(EmailTemplate $template, string $body): void
            {
                if ($template->key === 'payment_reminder') {
                    throw new Exception('Simulated database failure');
                }

                $template->update(['body' => $body]);
            }
        };

        $command->setLaravel(app());

        $this->createTemplate('product_approved', $this->legacyBody('product_approved'));
        $this->createTemplate('payment_reminder', $this->legacyBody('payment_reminder'));

        $tester = new CommandTester($command);
        $tester->execute(['--apply' => true]);

        $this->assertNotSame(SyncBrandedEmailTemplates::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('rolled back', $tester->getDisplay());

        // The first row was updated inside the transaction and must be rolled
        // back together with the failing second row.
        $this->assertSame($this->legacyBody('product_approved'), EmailTemplate::where('key', 'product_approved')->value('body'));
        $this->assertSame($this->legacyBody('payment_reminder'), EmailTemplate::where('key', 'payment_reminder')->value('body'));
    }
}
