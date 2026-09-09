<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Resolver for CMS-managed legal/FAQ page content.
 *
 * The canonical default copy lives in StaticPageController::pages() (unchanged
 * single source of truth). An admin-edited JSON blob stored under
 * `legal_page_{slug}` overrides that copy field-by-field, so editing never
 * requires a new content table or model — the public page output stays
 * identical until an administrator changes it.
 */
class LegalPagesContent
{
    public static function resolve(string $slug, array $defaults): array
    {
        $raw = Setting::where('key', 'legal_page_'.$slug)->value('value');

        if (! is_string($raw) || $raw === '') {
            return $defaults;
        }

        $stored = json_decode($raw, true);

        if (! is_array($stored)) {
            return $defaults;
        }

        $merged = array_replace($defaults, $stored);

        // Never let a malformed/partial override drop required structure.
        if (! is_array($merged['sections'] ?? null)) {
            $merged['sections'] = $defaults['sections'];
        }

        foreach ($merged['sections'] as &$section) {
            if (! is_array($section) || ! isset($section['heading']) || ! is_array($section['body'] ?? null)) {
                $section = null;
            }
        }
        unset($section);

        $merged['sections'] = array_values(array_filter($merged['sections']));

        if ($merged['sections'] === []) {
            return $defaults;
        }

        return $merged;
    }
}
