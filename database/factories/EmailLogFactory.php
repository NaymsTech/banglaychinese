<?php

namespace Database\Factories;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailLog>
 */
class EmailLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => null,
            'template_key' => null,
            'recipient_email' => fake()->safeEmail(),
            'recipient_name' => fake()->name(),
            'subject' => fake()->sentence(),
            'from_address' => null,
            'from_name' => null,
            'body' => null,
            'status' => EmailLog::STATUS_SENT,
            'error_message' => null,
            'queued_at' => now(),
            'sending_at' => null,
            'sent_at' => now(),
            'failed_at' => null,
            'attempt_count' => 1,
            'message_id' => null,
        ];
    }
}
