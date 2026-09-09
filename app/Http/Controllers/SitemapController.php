<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    /**
     * Public, indexable marketing and content URLs. Private, transactional and
     * authenticated pages are intentionally excluded.
     */
    public function index(): Response
    {
        $entries = $this->staticPages()
            ->merge($this->courses())
            ->merge($this->services())
            ->merge($this->blogPosts())
            ->merge($this->legalPages())
            ->values();

        $urls = $entries
            ->map(static fn (array $entry): string => '    <url>'.PHP_EOL
                .'        <loc>'.e($entry['loc']).'</loc>'.PHP_EOL
                .($entry['lastmod'] !== null ? '        <lastmod>'.$entry['lastmod'].'</lastmod>'.PHP_EOL : '')
                .'    </url>')
            ->implode(PHP_EOL);

        $content = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL
            .$urls.PHP_EOL
            .'</urlset>'.PHP_EOL;

        return response($content)->header('Content-Type', 'application/xml');
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: string|null}>
     */
    protected function staticPages(): Collection
    {
        return collect([
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('courses.index'), 'lastmod' => null],
            ['loc' => route('study-in-china'), 'lastmod' => null],
            ['loc' => route('about'), 'lastmod' => null],
            ['loc' => route('contact'), 'lastmod' => null],
            ['loc' => route('posts.index'), 'lastmod' => null],
            ['loc' => route('shop.index'), 'lastmod' => null],
            ['loc' => route('free-resources.index'), 'lastmod' => null],
        ]);
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: string|null}>
     */
    protected function courses(): Collection
    {
        return Course::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->map(fn (Course $course): array => [
                'loc' => route('courses.show', ['slug' => $course->slug]),
                'lastmod' => $course->updated_at?->toDateString(),
            ]);
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: string|null}>
     */
    protected function services(): Collection
    {
        return Service::query()
            ->active()
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->map(fn (Service $service): array => [
                'loc' => route('services.show', ['service' => $service->slug]),
                'lastmod' => $service->updated_at?->toDateString(),
            ]);
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: string|null}>
     */
    protected function blogPosts(): Collection
    {
        return Post::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->map(fn (Post $post): array => [
                'loc' => route('posts.show', ['post' => $post->slug]),
                'lastmod' => $post->updated_at?->toDateString(),
            ]);
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: string|null}>
     */
    protected function legalPages(): Collection
    {
        return collect([
            'terms-and-conditions',
            'privacy-policy',
            'refund-and-returns-policy',
            'faq',
        ])->map(fn (string $page): array => [
            'loc' => route('pages.show', $page),
            'lastmod' => null,
        ]);
    }
}
