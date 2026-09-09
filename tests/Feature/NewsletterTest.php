<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribing_with_a_valid_email_saves_a_newsletter_lead(): void
    {
        $this->from(route('home'))
            ->post(route('newsletter.subscribe'), [
                'email' => 'reader@example.com',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('newsletter');

        $this->assertDatabaseHas('leads', [
            'email' => 'reader@example.com',
            'source' => Lead::SOURCE_NEWSLETTER,
            'interest' => Lead::INTEREST_GENERAL,
            'is_subscribed' => true,
        ]);
    }

    public function test_subscribing_with_the_same_email_does_not_create_duplicates(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'reader@example.com']);
        $this->post(route('newsletter.subscribe'), ['email' => 'reader@example.com']);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_subscribing_with_an_invalid_email_is_rejected(): void
    {
        $this->post(route('newsletter.subscribe'), [
            'email' => 'not-an-email',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('leads', 0);
    }
}
