<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTitleTest extends TestCase
{
    use RefreshDatabase;

    public function test_title_contains_site_name_exactly_once_when_meta_title_includes_brand(): void
    {
        $title = $this->titleOf('/contact');

        $this->assertSame('Contact Us | Banglay Chinese', $title);
        $this->assertSame(1, substr_count($title, 'Banglay Chinese'));
    }

    public function test_title_contains_site_name_exactly_once_on_default_homepage_title(): void
    {
        $title = $this->titleOf('/');

        $this->assertSame(1, substr_count($title, 'Banglay Chinese'));
        $this->assertSame('Banglay Chinese | Learn Chinese Live. Study in China. Unlock Your Future.', $title);
    }

    public function test_page_without_page_specific_meta_title_uses_bare_site_name_once(): void
    {
        $title = $this->titleOf('/about');

        $this->assertSame('Banglay Chinese', $title);
    }

    public function test_course_page_title_is_not_duplicated(): void
    {
        $course = Course::create([
            'title' => 'HSK 1 Foundation',
            'slug' => 'hsk-1-foundation',
            'price' => 2500,
            'is_published' => true,
        ]);

        $title = $this->titleOf('/courses/'.$course->slug);

        $this->assertSame('HSK 1 Foundation | Banglay Chinese', $title);
        $this->assertSame(1, substr_count($title, 'Banglay Chinese'));
    }

    protected function titleOf(string $path): string
    {
        $html = $this->get($path)->assertOk()->getContent();

        preg_match('#<title>(.*?)</title>#s', $html, $matches);

        return trim($matches[1] ?? '');
    }
}
