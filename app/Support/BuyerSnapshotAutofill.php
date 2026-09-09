<?php

namespace App\Support;

use App\Models\User;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

/**
 * Reactive buyer-snapshot autofill shared by the manual-sale forms.
 *
 * Policy (predictable, no hidden state):
 *  - selecting a User fills blank snapshot fields with that user's
 *    name/email/phone;
 *  - switching User A → User B refreshes every snapshot field that still
 *    holds User A's auto-filled value — a field the admin edited by hand is
 *    never overwritten by a later selection, because it no longer equals the
 *    previous user's auto-filled value;
 *  - clearing the User drops the values that came from the previous autofill
 *    so no stale identity is left behind, while hand-entered values stay.
 *
 * The snapshot is a copy: nothing here ever writes back to the User row.
 */
class BuyerSnapshotAutofill
{
    /**
     * @var array<string, string> buyer snapshot field → User column
     */
    private const SNAPSHOT_FIELDS = [
        'student_name' => 'name',
        'student_email' => 'email',
        'student_phone' => 'phone',
    ];

    public static function apply(Get $get, Set $set, ?string $oldUserId, ?string $newUserId): void
    {
        $previousUser = filled($oldUserId) ? User::query()->find((int) $oldUserId) : null;
        $selectedUser = filled($newUserId) ? User::query()->find((int) $newUserId) : null;

        foreach (self::SNAPSHOT_FIELDS as $field => $column) {
            $current = self::normalize($get($field));

            if ($selectedUser === null) {
                // Cleared: remove only the value that the previous autofill
                // wrote; a manual override is left untouched.
                $previousValue = self::normalize($previousUser?->getAttribute($column));

                if ($current !== null && $current === $previousValue) {
                    $set($field, null);
                }

                continue;
            }

            $previousValue = self::normalize($previousUser?->getAttribute($column));

            // Replace when the field is blank or still carries the previous
            // user's auto-filled value; keep it when manually overridden.
            if ($current === null || ($previousValue !== null && $current === $previousValue)) {
                $set($field, $selectedUser->getAttribute($column));
            }
        }
    }

    private static function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
