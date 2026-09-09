<?php

namespace Database\Factories;

use App\Models\EmailProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailProvider>
 */
class EmailProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'driver' => EmailProvider::DRIVER_SMTP,
            'config' => [
                'host' => 'smtp-relay.brevo.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'smtp-username',
                'password' => 'smtp-password',
                'from_address' => fake()->unique()->safeEmail(),
                'from_name' => 'Banglay Chinese',
            ],
            'is_active' => true,
            'priority' => 1,
            'daily_limit' => 0,
            'sent_today' => 0,
            'last_reset' => null,
        ];
    }
}
