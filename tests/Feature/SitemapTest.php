<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_xml_with_public_urls_only(): void
    {
        $publishedCourse = $this->course('hsk-1-foundation', true);
        $this->course('hidden-course', false);
        $activeService = $this->service('guided-application', true);
        $this->service('inactive-service', false);
        $publishedPost = $this->blogPost('learn-pinyin', true);
        $this->blogPost('hidden-post', false);

        $response = $this->get('/sitemap.xml')->assertOk();

        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));

        $content = $response->getContent();

        // Never a hardcoded production domain.
        $this->assertStringNotContainsString('banglaychinese.com', $content);

        // Static marketing pages.
        foreach ([route('home'), route('courses.index'), route('study-in-china'), route('about'), route('contact'), route('posts.index'), route('shop.index'), route('free-resources.index')] as $url) {
            $this->assertStringContainsString('<loc>'.$url.'</loc>', $content);
        }

        // Legal / FAQ pages.
        foreach (['terms-and-conditions', 'privacy-policy', 'refund-and-returns-policy', 'faq'] as $page) {
            $this->assertStringContainsString('<loc>'.route('pages.show', $page).'</loc>', $content);
        }

        // Published dynamic content only.
        $this->assertStringContainsString('<loc>'.route('courses.show', ['slug' => $publishedCourse->slug]).'</loc>', $content);
        $this->assertStringNotContainsString($this->courseUrl('hidden-course'), $content);
        $this->assertStringContainsString('<loc>'.route('services.show', ['service' => $activeService->slug]).'</loc>', $content);
        $this->assertStringNotContainsString($this->serviceUrl('inactive-service'), $content);
        $this->assertStringContainsString('<loc>'.route('posts.show', ['post' => $publishedPost->slug]).'</loc>', $content);
        $this->assertStringNotContainsString($this->postUrl('hidden-post'), $content);

        // The stale, non-existent URL is gone.
        $this->assertStringNotContainsString(url('/study-in-china/services').'</loc>', $content);

        // No private or transactional URLs.
        foreach (['/dashboard', '/login', '/register', '/profile', '/checkout'] as $private) {
            $this->assertStringNotContainsString('<loc>'.url($private), $content);
        }

        // Valid XML, every URL generated on the configured application URL, no duplicates.
        $xml = simplexml_load_string($content);
        $this->assertNotFalse($xml);

        $locs = [];
        foreach ($xml->url as $url) {
            $locs[] = (string) $url->loc;
        }

        $this->assertNotEmpty($locs);
        $this->assertSame(count($locs), count(array_unique($locs)));

        foreach ($locs as $loc) {
            $this->assertStringStartsWith(url('/'), $loc);
        }
    }

    protected function course(string $slug, bool $published): Course
    {
        return Course::create([
            'title' => 'Course '.$slug,
            'slug' => $slug,
            'price' => 2500,
            'is_published' => $published,
        ]);
    }

    protected function service(string $slug, bool $active): Service
    {
        return Service::create([
            'name' => 'Service '.$slug,
            'slug' => $slug,
            'description' => 'Description of '.$slug,
            'price' => 1000,
            'sort_order' => 1,
            'status' => $active,
        ]);
    }

    protected function blogPost(string $slug, bool $published): Post
    {
        $category = Category::firstOrCreate(['slug' => 'guides'], ['name' => 'Guides']);

        return Post::create([
            'title' => 'Post '.$slug,
            'slug' => $slug,
            'category_id' => $category->id,
            'content' => 'Content of '.$slug,
            'excerpt' => 'Excerpt of '.$slug,
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ]);
    }

    protected function courseUrl(string $slug): string
    {
        return route('courses.show', ['slug' => $slug]);
    }

    protected function serviceUrl(string $slug): string
    {
        return route('services.show', ['service' => $slug]);
    }

    protected function postUrl(string $slug): string
    {
        return route('posts.show', ['post' => $slug]);
    }
}
