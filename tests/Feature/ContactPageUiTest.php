<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders_the_new_two_column_layout(): void
    {
        $response = $this->get('/contact');

        $response->assertOk()
            ->assertSee('Get in Touch')
            ->assertSee('Contact Information')
            ->assertSee('Send Us a Message')
            ->assertSee('https://wa.me/8618223249514', false)
            ->assertSee('mailto:info@banglaychinese.com', false)
            ->assertSee('name="topic"', false)
            ->assertSee('Service Interest')
            ->assertSee('WhatsApp Number');
    }

    public function test_contact_page_shows_the_success_banner_when_flashed(): void
    {
        session(['success' => 'Thank you! Your message has been sent. We will contact you shortly.']);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('Thank you! Your message has been sent. We will contact you shortly.')
            ->assertSee('bg-emerald-50', false);
    }
}
