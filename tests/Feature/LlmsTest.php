<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LlmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_returns_plain_text_site_description(): void
    {
        $response = $this->get('/llms.txt')->assertOk();

        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));

        $content = $response->getContent();

        // Real site identity.
        $this->assertStringContainsString('# Banglay Chinese', $content);
        $this->assertStringContainsString('Official website: '.url('/'), $content);

        // Real public navigation information.
        $this->assertStringContainsString('## Public content', $content);
        $this->assertStringContainsString(route('courses.index'), $content);
        $this->assertStringContainsString(route('study-in-china'), $content);
        $this->assertStringContainsString(route('posts.index'), $content);
        $this->assertStringContainsString(route('pages.show', 'faq'), $content);

        // No secrets or private/transactional URLs.
        foreach (['/dashboard', '/checkout', '/login', '/register', '/profile', 'password', 'bkash', 'nagad', '0177'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $content);
        }
    }
}
