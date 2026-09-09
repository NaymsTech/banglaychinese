<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Support\EmailShell;
use App\Support\EmailTemplateLegacyBodies;
use App\Support\EmailTemplatePlaceholders;
use Database\Seeders\EmailSystemSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Help;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('email:sync-branded-templates {--dry-run : Show what would change without modifying anything (this is also the default behaviour)} {--apply : Update eligible legacy template bodies (omit this flag for a read-only dry run)}')]
#[Description('Bring existing email_templates rows that still hold pre-branded bodies up to the current EmailSystemSeeder definitions')]
#[Help('Updates ONLY template bodies that still match a known legacy default; customized rows are always skipped.

Examples:
  php artisan email:sync-branded-templates --dry-run   # show what would change (default)
  php artisan email:sync-branded-templates --apply     # update the eligible rows')]
class SyncBrandedEmailTemplates extends Command
{
    /**
     * The exact template keys this command is allowed to touch. Nothing else
     * in the table is ever inspected or updated.
     */
    public const TARGET_KEYS = [
        'product_approved',
        'payment_reminder',
        'course_enrollment_confirmation',
        'welcome_email',
        'application_received',
        'contact_inquiry_received',
        'order_received_payment_pending',
        'payment_verification_failed',
        'payment_information_needs_attention',
        'email_verification',
        'password_reset',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $definitions = $this->definitions();

        $errors = static::validateDefinitions($definitions);

        if ($errors !== []) {
            $this->error('The canonical template definitions failed validation — no rows were changed:');

            foreach ($errors as $error) {
                $this->line('  - '.$error);
            }

            return self::FAILURE;
        }

        $byKey = collect($definitions)->keyBy('key');

        $keys = array_values(array_intersect(self::TARGET_KEYS, $byKey->keys()->all()));

        // The canonical definitions must cover every explicitly targeted key;
        // otherwise the command refuses to run so it can never silently ignore
        // (or accidentally widen) its scope.
        if (count($keys) !== count(self::TARGET_KEYS)) {
            $this->error('The canonical definitions are missing one or more targeted template keys — no rows were changed.');
            $this->line('  Missing: '.implode(', ', array_diff(self::TARGET_KEYS, $keys)));

            return self::FAILURE;
        }

        $legacyBodies = $this->legacyBodies();

        $rows = EmailTemplate::query()
            ->whereIn('key', $keys)
            ->get()
            ->keyBy('key');

        $eligible = [];
        $upToDate = 0;
        $customized = 0;
        $missing = 0;

        foreach ($keys as $key) {
            if (! $rows->has($key)) {
                $missing++;
                $this->line(sprintf('%-44s %s', $key, 'MISSING — row does not exist; not created'));

                continue;
            }

            $stored = (string) $rows[$key]->body;
            $canonicalBody = (string) $byKey[$key]['body'];

            if (static::sameHtml($stored, $canonicalBody)) {
                $upToDate++;
                $this->line(sprintf('%-44s %s', $key, 'ALREADY UP TO DATE'));

                continue;
            }

            $legacyBody = $legacyBodies[$key] ?? null;

            if ($legacyBody !== null && static::sameHtml($stored, $legacyBody)) {
                $eligible[$key] = ['template' => $rows[$key], 'body' => $canonicalBody];
                $this->line(sprintf('%-44s %s', $key, 'UPDATE'));

                continue;
            }

            $customized++;
            $this->line(sprintf('%-44s %s', $key, 'CUSTOMIZED — SKIP'));
        }

        $inspected = count($keys);

        $this->newLine();
        $this->line("{$inspected} templates inspected");
        $this->line(count($eligible).' eligible for update');
        $this->line("{$upToDate} already up to date");
        $this->line("{$customized} customized/unknown — skipped");
        $this->line("{$missing} missing (not created)");

        if ($this->option('dry-run') || ! $this->option('apply')) {
            $this->newLine();
            $this->info('Dry run — no changes made. Re-run with --apply to update the eligible rows.');

            return self::SUCCESS;
        }

        if ($eligible === []) {
            $this->newLine();
            $this->info('Nothing to update.');

            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($eligible): void {
                foreach ($eligible as $update) {
                    $this->updateBody($update['template'], $update['body']);
                }
            });
        } catch (Throwable $exception) {
            $this->error('The sync failed and was rolled back — no rows were updated. '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Updated '.count($eligible).' template bodies to the branded definitions.');

        return self::SUCCESS;
    }

    /**
     * The canonical definitions, from the same single source the seeder uses.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function definitions(): array
    {
        return EmailSystemSeeder::definitions();
    }

    /**
     * The known pre-branded default bodies used to recognise old rows.
     *
     * @return array<string, string>
     */
    protected function legacyBodies(): array
    {
        return EmailTemplateLegacyBodies::all();
    }

    /**
     * Apply a single body update (overridable so tests can simulate failures).
     */
    protected function updateBody(EmailTemplate $template, string $body): void
    {
        $template->update(['body' => $body]);
    }

    /**
     * Validate the canonical definitions before any row is touched. Returns a
     * list of human-readable problems; an empty list means "safe to proceed".
     *
     * @param  array<int, array<string, mixed>>  $definitions
     * @return array<int, string>
     */
    public static function validateDefinitions(array $definitions): array
    {
        $errors = [];

        foreach ($definitions as $definition) {
            $key = (string) ($definition['key'] ?? 'unknown');
            $subject = (string) ($definition['subject'] ?? '');
            $body = (string) ($definition['body'] ?? '');
            $variables = $definition['variables'] ?? [];

            if (trim($body) === '') {
                $errors[] = "[{$key}] has an empty body.";

                continue;
            }

            if (EmailShell::isCompleteEmail($body)) {
                $errors[] = "[{$key}] body is a standalone HTML document; it must stay a shell content fragment.";
            }

            if (str_contains($body, '#0f5132')) {
                $errors[] = "[{$key}] body still contains the legacy green (#0f5132).";
            }

            if (! str_contains($body, '#007A3D')) {
                $errors[] = "[{$key}] body does not use the brand green (#007A3D).";
            }

            if (str_contains($body, '<img') || str_contains($body, 'logo-full')) {
                $errors[] = "[{$key}] body embeds its own logo/image; the branded shell provides the PNG header logo.";
            }

            $unresolvable = EmailTemplatePlaceholders::unresolvable(
                EmailTemplatePlaceholders::referenced($subject, $body),
                is_array($variables) ? $variables : []
            );

            if ($unresolvable !== []) {
                $errors[] = "[{$key}] references placeholders that are not declared: ".implode(', ', $unresolvable).'.';
            }

            try {
                EmailShell::render($body);
            } catch (Throwable $exception) {
                $errors[] = "[{$key}] body failed to render through the branded shell: ".$exception->getMessage();
            }
        }

        return $errors;
    }

    /**
     * Whitespace-insensitive HTML comparison: line indentation from the
     * original heredocs must not decide whether a body is "the old default".
     */
    private static function sameHtml(string $stored, string $reference): bool
    {
        return static::normalize($stored) === static::normalize($reference);
    }

    private static function normalize(string $html): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $html) ?: [];

        $lines = array_map(static fn (string $line): string => trim($line), $lines);

        $lines = array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));

        return implode("\n", $lines);
    }
}
