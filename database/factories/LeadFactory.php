<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'whatsapp_number' => fake()->numerify('01#########'),
            'source' => fake()->randomElement(array_keys(Lead::SOURCES)),
            'interest' => fake()->randomElement(array_keys(Lead::INTERESTS)),
            'notes' => null,
            'is_subscribed' => true,
        ];
    }
}
