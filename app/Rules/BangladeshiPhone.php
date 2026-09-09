<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class BangladeshiPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('The :attribute must be a valid Bangladeshi phone number.');

            return;
        }

        $phone = preg_replace('/[\s\-()]/', '', (string) $value);

        if (! preg_match('/^(?:\+88|88)?01[3-9]\d{8}$/', $phone)) {
            $fail('The :attribute must be a valid Bangladeshi phone number (e.g. 01XXXXXXXXX or +8801XXXXXXXXX).');
        }
    }
}
