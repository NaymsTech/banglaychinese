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
        User::updateOrCreate(
            ['email' => 'admin@banglaychinese.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'change-me-in-prod')),
                'role' => 'admin',
                'is_admin' => true,
            ]
        );
    }
}
