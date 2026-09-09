<?php

namespace Tests\Unit;

use App\Rules\BangladeshiPhone;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BangladeshiPhoneTest extends TestCase
{
    public static function validPhones(): array
    {
        return [
            'local format' => ['01712345678'],
            'with country code' => ['+8801712345678'],
            'with leading 88' => ['8801712345678'],
            'spaces and dashes stripped' => ['017 1234-5678'],
            'parentheses stripped' => ['017(1234)5678'],
            'other operator 018' => ['01812345678'],
            'other operator 019' => ['01912345678'],
        ];
    }

    public static function invalidPhones(): array
    {
        return [
            'too short' => ['12345'],
            'no leading zero' => ['1712345678'],
            'operator not allowed' => ['01012345678'],
            'too long' => ['017123456789'],
            'not a phone' => ['someone@example.com'],
            'empty' => [''],
        ];
    }

    #[DataProvider('validPhones')]
    public function test_valid_bangladeshi_phone_passes(string $phone): void
    {
        $validator = Validator::make(['phone' => $phone], ['phone' => ['required', new BangladeshiPhone]]);

        $this->assertFalse($validator->fails(), "Expected [{$phone}] to be accepted.");
    }

    #[DataProvider('invalidPhones')]
    public function test_invalid_bangladeshi_phone_is_rejected(string $phone): void
    {
        $validator = Validator::make(['phone' => $phone], ['phone' => ['required', new BangladeshiPhone]]);

        $this->assertTrue($validator->fails(), "Expected [{$phone}] to be rejected.");
    }
}
