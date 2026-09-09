<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's default admin user.
     */
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD');

        if (! $password && app()->environment('production')) {
            throw new \RuntimeException('ADMIN_PASSWORD must be set in the environment before seeding the admin user in production.');
        }

        User::updateOrCreate(
            ['email' => 'admin@banglaychinese.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make($password ?: 'change-me-in-prod'),
                'role' => 'admin',
                'is_admin' => true,
            ]
        );
    }
}
