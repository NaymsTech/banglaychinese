<?php

namespace App\Support;

use App\Models\EmailTemplate;
use Illuminate\Support\Str;

/**
 * The single source of truth for the {placeholder} system.
 *
 * Every consumer (EmailService rendering, the manual-email preflight and the
 * Filament template editor/preview) parses placeholders and decides which are
 * resolvable through this class, so there is exactly ONE consistent mechanism.
 *
 * Settings-backed variables are always resolvable without being declared on a
 * template, because EmailService merges them into every send.
 */
class EmailTemplatePlaceholders
{
    /**
     * Variables resolved from the site settings on every EmailService send.
     */
    public const SETTINGS_BACKED_VARIABLES = [
        'bkash_number',
        'nagad_number',
        'bank_details',
        'whatsapp_number',
        'contact_email',
    ];

    /**
     * Every {variable} token referenced by the given subject/body.
     *
     * @return array<int, string>
     */
    public static function referenced(string $subject, string $body): array
    {
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $subject."\n".$body, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * Placeholders that can never be filled: not declared by the template and
     * not provided by the caller or merged from the site settings.
     *
     * @param  array<int, string>  $used
     * @param  array<int, string>  $declared
     * @return array<int, string>
     */
    public static function unresolvable(array $used, array $declared): array
    {
        $available = array_unique(array_merge($declared, self::SETTINGS_BACKED_VARIABLES));

        return array_values(array_diff($used, $available));
    }

    /**
     * Whether every placeholder used by the template resolves.
     *
     * @param  array<int, string>  $declared
     */
    public static function areResolvable(EmailTemplate $template, array $declared): bool
    {
        return self::unresolvable(self::referenced($template->subject, $template->body), $declared) === [];
    }

    /**
     * Safe sample data for a preview or test send, keyed by variable name.
     * Never contains real recipient information.
     */
    public static function sampleValue(string $variable): string
    {
        return match ($variable) {
            'name', 'student_name' => 'Demo Student',
            'email', 'recipient_email', 'contact_email' => 'student@example.com',
            'url', 'verification_url', 'reset_url' => 'https://example.com/verify/12345',
            'download_link' => 'https://example.com/files/hsk1-ebook.pdf',
            'product_title' => 'HSK 1 E-Book',
            'course_title' => 'HSK 1 Standard Course',
            'amount_due' => '৳5,000',
            'due_date' => now()->addDays(7)->format('j M Y'),
            'desired_program' => 'Chinese Language Bachelor Program',
            'appName' => 'Banglay Chinese',
            'expireMinutes' => '60',
            default => 'Demo '.Str::headline($variable),
        };
    }
}
