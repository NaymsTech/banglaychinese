<?php

namespace App\Support;

/**
 * Display helpers for the canonical WhatsApp/phone number.
 *
 * The stored values are plain digit strings without the leading '+' (e.g.
 * 8618223249514 for +86 182 2324 9514). Blade templates should render the
 * human-readable form through this single formatter instead of re-deriving
 * it inline, so every page shows the same grouping.
 */
class WhatsAppNumber
{
    public static function display(?string $digits): string
    {
        $digits = self::normalize($digits);

        // China international: 8618223249514 → +86 182-2324-9514
        if (strlen($digits) === 13 && str_starts_with($digits, '86')) {
            return '+86 '.substr($digits, 2, 3).'-'.substr($digits, 5, 4).'-'.substr($digits, 9, 4);
        }

        // Bangladesh local: 01774148708 → +880 1774-148708
        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            return '+880 '.substr($digits, 1, 4).'-'.substr($digits, 5);
        }

        // Bangladesh international: 8801774148708 → +880 1774-148708
        if (strlen($digits) === 13 && str_starts_with($digits, '880')) {
            return '+880 '.substr($digits, 3, 4).'-'.substr($digits, 7);
        }

        return filled($digits) ? $digits : '';
    }

    /**
     * Strip everything except digits (e.g. "+86 182-2324-9514" → "8618223249514").
     */
    public static function normalize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_replace('/\D/', '', (string) $value) ?? '';
    }
}
