<?php

namespace App\Support\Concerns;

use App\Models\User;

/**
 * Buyer snapshot fallback used by the manual-sale Create pages.
 *
 * The legacy sale tables snapshot the buyer's name/email/phone so an order
 * stays readable even when the linked account changes later. When the admin
 * linked a registered user but left a snapshot field blank (for example when
 * the reactive form autofill never ran), the blank is filled from the user's
 * account here — the admin never has to retype it. Non-blank snapshots are
 * never overwritten, and nothing is ever written back to the User.
 */
trait FillsBuyerSnapshotFromUser
{
    protected function fillBuyerSnapshotFromUser(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);

        if ($userId <= 0) {
            return $data;
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            return $data;
        }

        $data['student_name'] = filled($data['student_name'] ?? null) ? $data['student_name'] : $user->name;
        $data['student_email'] = filled($data['student_email'] ?? null) ? $data['student_email'] : $user->email;
        $data['student_phone'] = filled($data['student_phone'] ?? null) ? $data['student_phone'] : $user->phone;

        return $data;
    }
}
