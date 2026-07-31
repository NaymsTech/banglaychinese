<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $courses = Course::where('is_published', true)->get();
        $featured = Course::where('is_published', true)->where('is_featured', true)->get();

        $metaTitle = 'Banglay Chinese | Best Learn Chinese for Bangladeshi Students';
        $metaDescription = 'সরাসরি চীন থেকে এক্সক্লুসিভ মেন্টরশিপে স্কলারশিপ ও চাইনীজ ভাষা শিখুন। HSK 1-4 preparation, live speaking classes ও চায়না স্কলারশিপ সাপোর্ট।';

        $courseJsonLd = $featured->isNotEmpty()
            ? json_encode($this->buildCourseJsonLd($featured), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
            : null;

        $faqJsonLd = $this->buildFaqJsonLd();

        return view('home', compact('categories', 'courses', 'featured', 'metaTitle', 'metaDescription', 'courseJsonLd', 'faqJsonLd'));
    }

    protected function buildFaqJsonLd(): string
    {
        $faqs = [
            [
                'q' => 'বাংলাদেশি শিক্ষার্থীরা কীভাবে চায়না স্কলারশিপ পেতে পারে?',
                'a' => 'চায়না স্কলারশিপ (CSC) পেতে HSK 3–4 লেভেলের সার্টিফিকেট, একাডেমিক ট্রান্সক্রিপ্ট, SOP (স্টেটমেন্ট অব পারপাস) এবং ইউনিভার্সিটি অ্যাপ্লিকেশন প্রয়োজন। Banglay Chinese-এর স্কলারশিপ মেন্টরশিপ প্রোগ্রামে আমরা ডকুমেন্ট প্রস্তুতি থেকে শুরু করে ইউনিভার্সিটি সিলেকশন এবং অ্যাপ্লিকেশন টাইমলাইন পর্যন্ত সম্পূর্ণ গাইডেন্স দিয়ে থাকি।',
            ],
            [
                'q' => 'HSK কী এবং কত লেভেল পর্যন্ত শেখানো হয়?',
                'a' => 'HSK হলো চীনা ভাষার আন্তর্জাতিক দক্ষতা পরীক্ষা। Banglay Chinese-এ HSK 1 থেকে HSK 4 পর্যন্ত সম্পূর্ণ প্রস্তুতি করানো হয় — লাইভ ক্লাস, নিয়মিত মক টেস্ট ও পার্সোনাল ফিডব্যাক সহ।',
            ],
            [
                'q' => 'কোর্সের ফি কত এবং কী কী সুবিধা আছে?',
                'a' => 'কোর্স অনুযায়ী ফি আলাদা — HSK Standard Track ১৮,০০০ টাকা, Chinese Speaking Mastery ২০,০০০ টাকা এবং HSK Intensive Program ২৫,০০০ টাকা। সব কোর্সে লাইভ স্মল-গ্রুপ ক্লাস, লাইফটাইম রেকর্ডিং, AI ওয়ার্ড ম্যাপ এবং সার্টিফিকেট অন্তর্ভুক্ত।',
            ],
            [
                'q' => 'কোর্স করার জন্য কি আগে থেকে চাইনিজ জানতে হবে?',
                'a' => 'না, কোনো পূর্ব অভিজ্ঞতা লাগবে না। আমাদের HSK 1 থেকে শুরু করার পূর্ণাঙ্গ সিলেবাস এবং বাংলায় ব্যাখ্যাসহ পাঠদান করা হয়, তাই একদম শূন্য থেকে শুরু করলেও সহজে শিখতে পারবেন।',
            ],
            [
                'q' => 'লাইভ ক্লাসের সময়সূচী কেমন এবং রেকর্ডিং পাওয়া যায় কি?',
                'a' => 'আমাদের লাইভ ক্লাস সন্ধ্যায় বাংলাদেশ সময় অনুযায়ী সপ্তাহে ৩ দিন হয়। প্রতিটি ক্লাসের রেকর্ডিং থাকে, তাই মিস করলেও পরে দেখে নিতে পারবেন।',
            ],
            [
                'q' => 'শিক্ষার্থীদের জন্য ক্যারিয়ার বা পড়াশোনার সুযোগ কী?',
                'a' => 'HSK 3–4 লেভেল সম্পন্ন করা শিক্ষার্থীদের জন্য চায়নার সরকারি ও বিশ্ববিদ্যালয় স্কলারশিপের আবেদন প্রক্রিয়ায় আমরা সরাসরি সাপোর্ট করি। চীনে আন্ডারগ্রাজুয়েট ও মাস্টার্স প্রোগ্রামে স্কলারশিপ নিয়ে পড়ার সুযোগ রয়েছে।',
            ],
        ];

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

        return '<script type="application/ld+json">'.json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG).'</script>';
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
