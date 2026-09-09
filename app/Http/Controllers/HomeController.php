<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\HomeSection;
use App\Models\Setting;
use App\Support\HomePageContent;
use App\Support\HomePageDefaults;

class HomeController extends Controller
{
    /**
     * Build the flat, editable homepage fields: stored home_sections override
     * the defaults so the page looks identical before any CMS edit is made.
     *
     * @return array<string, mixed>
     */
    protected function buildHomeContent(): array
    {
        // key => [value, type]
        $map = collect(HomePageDefaults::data())
            ->flatMap(fn (array $rows): array => $rows)
            ->mapWithKeys(fn (array $row, string $key): array => [
                $key => ['value' => $row[0], 'type' => $row[1]],
            ]);

        $defaults = $map->map(fn (array $row): mixed => $row['value'])->all();

        foreach (HomeSection::pluck('value', 'key') as $key => $value) {
            if (! $map->has($key)) {
                continue;
            }

            $defaults[$key] = $map[$key]['type'] === 'json'
                ? (json_decode((string) $value, true) ?? [])
                : $value;
        }

        return $defaults;
    }

    public function index()
    {
        $categories = Category::all();
        $courses = Course::where('is_published', true)->get();
        $featured = Course::where('is_published', true)->where('is_featured', true)->get();
        $homeCourses = Course::query()
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with('category')
            ->limit(6)
            ->get();
        $home = $this->buildHomeContent();

        // Editable homepage content: saved `settings` rows on top of code defaults.
        // Blank/cleared rows fall back to the code default again.
        $settings = array_replace(
            HomePageContent::defaults(),
            array_filter(
                Setting::pluck('value', 'key')->toArray(),
                fn ($value) => ! blank($value),
            ),
        );

        $metaTitle = $settings['home_meta_title'] ?? 'Banglay Chinese | Learn Chinese Live. Study in China. Unlock Your Future.';
        $metaDescription = $settings['home_meta_description'] ?? 'বাংলায় চীনা ভাষা শিখুন + চীনে পড়াশোনার সম্পূর্ণ গাইডেন্স। লাইভ ব্যাচ, HSK প্রস্তুতি ও চায়না স্কলারশিপ সাপোর্ট — সব এক জায়গায়।';
        $metaImage = blank($settings['home_og_image'] ?? null)
            ? null
            : (preg_match('~^https?://~i', (string) $settings['home_og_image'])
                ? $settings['home_og_image']
                : asset('storage/'.$settings['home_og_image']));

        $courseJsonLd = $featured->isNotEmpty()
            ? json_encode($this->buildCourseJsonLd($featured), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
            : null;

        $faqItems = is_array($home['faq_items'] ?? null) ? $home['faq_items'] : [];
        $faqJsonLd = $faqItems
            ? json_encode($this->buildFaqJsonLd($faqItems), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
            : null;

        return view('home', compact(
            'categories',
            'courses',
            'featured',
            'homeCourses',
            'home',
            'settings',
            'faqItems',
            'metaTitle',
            'metaDescription',
            'metaImage',
            'courseJsonLd',
            'faqJsonLd',
        ));
    }

    /**
     * @param  array<int, array{q: string, a: string}>  $faqs
     */
    protected function buildFaqJsonLd(array $faqs): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a'],
                ],
            ], $faqs),
        ];

        return $data;
    }

    protected function buildCourseJsonLd($featuredCourses): array
    {
        $items = $featuredCourses->map(fn ($c) => [
            '@type' => 'Course',
            'name' => $c->title,
            'description' => $c->description,
            'provider' => [
                '@type' => 'EducationalOrganization',
                'name' => 'Banglay Chinese',
                'sameAs' => url('/'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $c->price,
                'priceCurrency' => 'BDT',
                'availability' => 'https://schema.org/InStock',
            ],
        ])->values()->all();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => array_map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'item' => $item,
            ], $items, array_keys($items)),
        ];
    }
}
