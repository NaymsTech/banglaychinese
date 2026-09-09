<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Store an EmailProvider's configuration payload encrypted at rest.
 *
 * The value is serialized to JSON, encrypted with the application key and
 * persisted as an opaque text blob. Reads decrypt transparently, so callers
 * keep working with a plain PHP array. Rows written before encryption was
 * introduced (plaintext JSON in the column) are still readable until the
 * next write re-encrypts them.
 */
class EncryptedConfig implements CastsAttributes
{
    /**
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        try {
            $payload = Crypt::decryptString($value);
        } catch (DecryptException) {
            $payload = $value;
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>|null  $value
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return [$key => Crypt::encryptString((string) json_encode($value ?? []))];
    }
}
