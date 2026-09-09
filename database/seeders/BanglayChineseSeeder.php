<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Database\Seeder;

class BanglayChineseSeeder extends Seeder
{
    /**
     * Seed the BanglayChinese.com content (categories + courses/services) from the real site.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedCourses();

        $this->command->info('BanglayChinese site content seeded: 3 categories, 4 language courses.');
    }

    /**
     * Seed the CMS categories.
     */
    protected function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'HSK Preparation',
                'slug' => 'hsk-preparation',
                'description' => 'Structured HSK exam preparation resources and tracks from HSK 1 to HSK 4.',
            ],
            [
                'name' => 'Speaking & Fluency',
                'slug' => 'speaking-fluency',
                'description' => 'Conversation-focused courses to build real-world Chinese speaking confidence and fluency.',
            ],
            [
                'name' => 'Kids Program',
                'slug' => 'kids-program',
                'description' => 'Play-based Chinese learning programs designed for young learners aged 8–13.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }

    /**
     * Seed the LMS products with real BanglayChinese.com data from WordPress XML.
     *
     * Pricing ladder (business-optimized, ascending by value):
     *   Language Courses (type: course):
     *     1. Fun Chinese for Kids        – 12,000 TK  (entry)
     *     2. HSK Standard Track          – 12,000 TK  (core)
     *     3. Chinese Speaking Mastery    – 16,000 TK  (mid)
     *     4. HSK Intensive Program       – 20,000 TK  (premium language)
     *
     *   Study in China Services (type: service):
     *     5. Application Guide           – 25,000 TK  (DIY guide)
     *     6. Complete Application Support – 60,000 TK  (done-for-you)
     *     7. Complete China Success      – 100,000 TK (VIP 1-year pathway)
     */
    protected function seedCourses(): void
    {
        $products = [
            // ── Language Courses ──────────────────────────────────────────
            [
                'title' => 'Fun Chinese for Kids',
                'slug' => 'fun-chinese-for-kids',
                'description' => 'Fun Chinese for Kids: আপনার সন্তানের জন্য চাইনিজের প্রথম ধাপ। '
                    .'Fun Chinese for Kids প্রোগ্রামটি ডিজাইন করা হয়েছে বাংলাদেশি বাচ্চাদের জন্য, '
                    .'যেখানে খেলা, গান ও গল্পের মাধ্যমে HSK ফাউন্ডেশন তৈরি করা হয়। '
                    .'ছোট গ্রুপে interactive ক্লাস যাতে প্রতিটি শিক্ষার্থী মনোযোগ পায় এবং মজার মাধ্যমে শেখে। '
                    .'বয়স: ৮–১৩ বছর।',
                'hsk_level' => 1,
                'price' => 12000,
                'duration_months' => 2,
                'category_slug' => 'kids-program',
                'is_published' => true,
                'is_featured' => false,
            ],
            [
                'title' => 'HSK Standard Track',
                'slug' => 'hsk-standard-track',
                'description' => 'HSK Standard Track: টেকসই গতিতে নিশ্চিত প্রস্তুতি। '
                    .'HSK Standard Track হলো আমাদের ফ্ল্যাগশিপ একাডেমিক প্রোগ্রাম। Official HSK Curriculum অনুসরণ করে তৈরি '
                    .'এই track-এ রয়েছে structured lessons, regular mock tests, এবং China admission preparation — '
                    .'যাতে আপনি নির্ধারিত সময়ের মধ্যে HSK 1–4 শেষ করে scholarship-এর জন্য প্রস্তুত হতে পারেন।',
                'hsk_level' => 4,
                'price' => 12000,
                'duration_months' => 4,
                'category_slug' => 'hsk-preparation',
                'is_published' => true,
                'is_featured' => true,
            ],
            [
                'title' => 'Chinese Speaking Mastery',
                'slug' => 'chinese-speaking-mastery',
                'description' => 'আত্মবিশ্বাসের সঙ্গে সাবলীল চাইনিজ বলুন। '
                    .'Chinese Speaking Mastery প্রোগ্রামটি তৈরি করা হয়েছে সেই বাংলাদেশি শিক্ষার্থীদের জন্য '
                    .'যারা HSKK + HSK4 প্রস্তুতির পাশাপাশি real-world conversation-এ দক্ষ হতে চান। '
                    .'AI word map ও daily-life scenario practice-এর মাধ্যমে vocabulary retention ও speaking fluency নিশ্চিত করে।',
                'hsk_level' => 4,
                'price' => 16000,
                'duration_months' => 3,
                'category_slug' => 'speaking-fluency',
                'is_published' => true,
                'is_featured' => true,
            ],
            [
                'title' => 'HSK Intensive Program',
                'slug' => 'hsk-intensive-program',
                'description' => 'HSK Intensive: দ্রুত গতিতে সিরিয়াস প্রস্তুতি। '
                    .'HSK Intensive Program তাদের জন্য, যারা কম সময়ে স্ট্রাকচার্ড ও ফলাফল-কেন্দ্রিক প্রস্তুতি নিতে চান। '
                    .'Accelerated lessons, priority feedback, এবং scholarship application guidance সহ — '
                    .'যাতে upcoming deadline-এর আগেই HSK 1–4 complete করে China admission-এর জন্য আবেদন করতে পারেন।',
                'hsk_level' => 4,
                'price' => 20000,
                'duration_months' => 2,
                'category_slug' => 'hsk-preparation',
                'is_published' => true,
                'is_featured' => true,
            ],

            // Study in China service packages now live in the dedicated `services`
            // table and are seeded via ServiceSeeder.
        ];

        foreach ($products as $product) {
            // Resolve category ID
            $categoryId = null;
            if (! empty($product['category_slug'])) {
                $cat = Category::where('slug', $product['category_slug'])->first();
                $categoryId = $cat ? $cat->id : null;
            }

            Course::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'title' => $product['title'],
                    'description' => $product['description'],
                    'hsk_level' => $product['hsk_level'],
                    'price' => $product['price'],
                    'duration_months' => $product['duration_months'] ?? null,
                    'category_id' => $categoryId,
                    'is_published' => $product['is_published'],
                    'is_featured' => $product['is_featured'],
                ]
            );
        }
    }
}
