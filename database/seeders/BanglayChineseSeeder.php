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
                'description' => <<<'HTML'
<h2>আত্মবিশ্বাসের সঙ্গে সাবলীল চাইনিজ বলুন</h2>
<p><strong>Chinese Speaking Mastery প্রোগ্রামটি তৈরি করা হয়েছে সেই বাংলাদেশি শিক্ষার্থীদের জন্য যারা বাস্তব কথোপকথনে Chinese দক্ষতা গড়ে তুলতে চান। স্ট্রাকচার্ড লাইভ ক্লাস, ছোট ব্যাচ সিস্টেম এবং কার্যকর প্রগ্রেস ট্র্যাকিংয়ের মাধ্যমে আপনি পাবেন এক টেকসই ভাষা ভিত্তি ও ভবিষ্যতের HSK সাফল্যের রোডম্যাপ।</strong></p>
<p><strong>অধিকাংশ শিক্ষার্থী চাইনিজ শুনে বুঝতে  পারলেও কথা বলার সময় জড়তা অনুভব করে। Chinese Speaking Mastery হলো এমন একটি নিবিড় প্রশিক্ষণ যা আপনার উচ্চারণের ত্রুটি দূর করে আপনাকে সাবলীল ভাবে  কথা বলতে সাহায্য করবে। এটি কোনো সাধারণ কোর্স নয়, বরং আপনার Verbal Skills বৃদ্ধির একটি Professional Lab।</strong></p>
<h3>এই প্রোগ্রাম যাদের জন্য</h3>
<ul>
<li><strong>HSK Learners:  যারা তাদের HSKK (Oral) পরীক্ষার স্কোর ইমপ্রুভ করতে চান।</strong></li>
<li><strong>Scholarship Aspirants:  যারা চীনা বিশ্ববিদ্যালয়ের Admission Interview-তে সেরা পারফরম্যান্স দিতে চান।</strong></li>
<li><strong>সবাই যারা চাইনিজ শেখাকে বিনিয়োগ হিসেবে দেখেন</strong></li>
<li><strong>যারা ভবিষ্যতে HSK পরীক্ষা ও বিশ্ববিদ্যালয় অ্যাডমিশনে আগ্রহী</strong></li>
<li><strong>যারা স্পষ্ট গাইডলাইন ও দায়িত্বশীল প্রশিক্ষণ চান</strong></li>
<li><strong>যারা নৈমিত্তিক লার্নিং নয়, ফলাফল চান</strong></li>
</ul>
<h3>আপনি যা শিখবেন</h3>
<ul>
<li><strong>Tone Correction: চাইনিজ ভাষার ৪টি Tones-এর নিখুঁত প্রয়োগ নিশ্চিত করা।</strong></li>
<li><strong>Spontaneous Response: অনুবাদ না করে সরাসরি চাইনিজ ভাষায় চিন্তা ও উত্তর দেওয়ার Natural Ability।</strong></li>
<li><strong>দৈনন্দিন কথোপকথনের জন্য core speaking skills</strong></li>
<li><strong>পিনইন (Pinyin) উচ্চারণ ও টোনের পারফেকশন</strong></li>
<li><strong>বাস্তব Chinese কমিউনিকেশনের আত্মবিশ্বাস</strong></li>
</ul>
<h3>প্রোগ্রামের কাঠামো</h3>
<ul>
<li><strong>4 মাস (সপ্তাহে  ২-৩ দিন)</strong></li>
<li><strong>সাপ্তাহিক speaking lab ও অ্যাসাইনমেন্ট</strong></li>
<li><strong>PDF মেটেরিয়াল, অডিও প্র্যাকটিস ও প্রগ্রেস ট্র্যাকিং সাপোর্ট</strong></li>
<li><strong>মক স্পিকিং অ্যাসেসমেন্ট ও ফিডব্যাক</strong></li>
</ul>
<h3>কেন Banglay Chinese</h3>
<ul>
<li><strong>স্পষ্ট রোডম্যাপ: Speaking → HSK → Admission Success</strong></li>
<li><strong>বিশ্বস্ত গাইডেন্স ও প্রিমিয়াম সার্ভিস স্ট্যান্ডার্ড</strong></li>
</ul>
<h3>Program Fee</h3>
<p><strong>মোট: ৳১৬, ০০০</strong></p>
HTML,
                'hsk_level' => 4,
                'price' => 16000,
                'duration_months' => 4,
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
