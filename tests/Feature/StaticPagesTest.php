<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_helper_pages_render_with_layout(): void
    {
        $pages = [
            ['terms-and-conditions', 'Terms and Conditions'],
            ['privacy-policy', 'Privacy Policy'],
            ['refund-and-returns-policy', 'Refund and Returns Policy'],
            ['faq', 'Frequently Asked Questions'],
        ];

        foreach ($pages as [$slug, $title]) {
            $this->get('/pages/'.$slug)
                ->assertOk()
                ->assertSee($title)
                ->assertSee('Learn Chinese In Bangla'); // shared footer renders on every page
        }
    }

    public function test_unknown_helper_page_returns_404(): void
    {
        $this->get('/pages/not-a-real-page')->assertNotFound();
    }

    public function test_footer_includes_designed_sections_and_real_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Learn Chinese In Bangla')
            ->assertSee('We accept')
            ->assertSee('Menu')
            ->assertSee('Useful Links')
            ->assertSee('Serious About Studying in China?')
            ->assertSee('/pages/terms-and-conditions')
            ->assertSee('/pages/privacy-policy')
            ->assertSee('/pages/refund-and-returns-policy')
            ->assertSee('/pages/faq');
    }
}
