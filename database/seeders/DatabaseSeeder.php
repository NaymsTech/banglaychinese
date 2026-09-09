<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Production (`php artisan db:seed --force`) runs ONLY the essential
     * seeders the application genuinely needs to operate: core settings, the
     * secure admin account, the real course/service catalog and the email
     * system bootstrap. No fake users, customers, orders, payments or demo
     * content are ever created outside a local environment.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            AdminUserSeeder::class,
            BanglayChineseSeeder::class,
            ServiceSeeder::class,
            AboutPageSeeder::class,
            EmailSystemSeeder::class,
        ]);

        // Demo/test data is strictly local-only: it must never reach a
        // production or staging database.
        if (app()->isLocal()) {
            $this->call(FreeResourceSeeder::class);

            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
