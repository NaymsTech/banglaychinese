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
            'target_course' => 'HSK Level 4',
            'educational_background' => 'HSC (Science), GPA 5.00',
            'statement_of_purpose' => 'I want to study in China to pursue a degree in Computer Science. This is my dream and I am fully dedicated to achieving it through hard work and perseverance.',
        ]);

        $response->assertRedirect(route('study-in-china.consultation'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('scholarship_applications', [
            'email' => 'rahim@example.com',
            'target_course' => 'HSK Level 4',
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
}
