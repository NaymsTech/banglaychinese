<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_scholarship_application_can_be_submitted()
    {
        $response = $this->post('/study-in-china/apply', [
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'phone' => '+8801712345678',
            'highest_qualification' => 'HSC',
            'desired_program' => 'Chinese Language Program',
            'target_intake' => 'September 2027',
            'statement_of_purpose' => 'I want to study in China to pursue a degree in Computer Science. This is my dream and I am fully dedicated to achieving it through hard work and perseverance.',
        ]);

        $response->assertRedirect(route('study-in-china.consultation'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('scholarship_applications', [
            'email' => 'rahim@example.com',
            'desired_program' => 'Chinese Language Program',
        ]);
    }

    public function test_public_pages_render()
    {
        $this->get('/study-in-china')->assertStatus(200);
        $this->get('/study-in-china/consultation')->assertStatus(200);
        $this->get('/blog')->assertStatus(200);
        $this->get('/about')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);
    }

    public function test_study_in_china_nav_item_is_highlighted_on_all_sic_routes(): void
    {
        // Desktop nav paints the active item with this exact class sequence.
        foreach (['/study-in-china', '/study-in-china/consultation'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('transition-colors text-emerald-700', false);
        }
    }
}
