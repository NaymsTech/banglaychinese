<?php

namespace Database\Factories;

use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Welcome email',
            'key' => fake()->unique()->slug(2),
            'subject' => 'Hello {name}, welcome!',
            'body' => '<h1>Hello {name}</h1><p>We are glad you joined.</p>',
            'variables' => ['name'],
            'category' => EmailTemplate::CATEGORY_NOTIFICATION,
            'is_active' => true,
        ];
    }
}
