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

        $this->command->info('BanglayChinese site content seeded: 5 categories, 7 products (4 courses + 3 services).');
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
            [
                'name' => 'Scholarship Guidance',
                'slug' => 'scholarship-guidance',
                'description' => 'Fast-track prep and guidance for Chinese university scholarships and admission deadlines.',
            ],
            [
                'name' => 'Study in China',
                'slug' => 'study-in-china',
                'description' => 'Complete application support, guidance, and pathway programs for studying in China.',
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
                'title'              => 'Fun Chinese for Kids',
                'slug'               => 'fun-chinese-for-kids',
                'description'        => 'Fun Chinese for Kids: আপনার সন্তানের জন্য চাইনিজের প্রথম ধাপ। '
                    . 'Fun Chinese for Kids প্রোগ্রামটি ডিজাইন করা হয়েছে বাংলাদেশি বাচ্চাদের জন্য, '
                    . 'যেখানে খেলা, গান ও গল্পের মাধ্যমে HSK ফাউন্ডেশন তৈরি করা হয়। '
                    . 'ছোট গ্রুপে interactive ক্লাস যাতে প্রতিটি শিক্ষার্থী মনোযোগ পায় এবং মজার মাধ্যমে শেখে। '
                    . 'বয়স: ৮–১৩ বছর।',
                'hsk_level'          => 1,
                'price'              => 12000,
                'type'               => 'course',
                'duration_weeks'     => 8,
                'category_slug'      => 'kids-program',
                'is_published'       => true,
                'is_featured'        => false,
            ],
            [
                'title'              => 'HSK Standard Track',
                'slug'               => 'hsk-standard-track',
                'description'        => 'HSK Standard Track: টেকসই গতিতে নিশ্চিত প্রস্তুতি। '
                    . 'HSK Standard Track হলো আমাদের ফ্ল্যাগশিপ একাডেমিক প্রোগ্রাম। Official HSK Curriculum অনুসরণ করে তৈরি '
                    . 'এই track-এ রয়েছে structured lessons, regular mock tests, এবং China admission preparation — '
                    . 'যাতে আপনি নির্ধারিত সময়ের মধ্যে HSK 1–4 শেষ করে scholarship-এর জন্য প্রস্তুত হতে পারেন।',
                'hsk_level'          => 4,
                'price'              => 12000,
                'type'               => 'course',
                'duration_weeks'     => 16,
                'category_slug'      => 'hsk-preparation',
                'is_published'       => true,
                'is_featured'        => true,
            ],
            [
                'title'              => 'Chinese Speaking Mastery',
                'slug'               => 'chinese-speaking-mastery',
                'description'        => 'আত্মবিশ্বাসের সঙ্গে সাবলীল চাইনিজ বলুন। '
                    . 'Chinese Speaking Mastery প্রোগ্রামটি তৈরি করা হয়েছে সেই বাংলাদেশি শিক্ষার্থীদের জন্য '
                    . 'যারা HSKK + HSK4 প্রস্তুতির পাশাপাশি real-world conversation-এ দক্ষ হতে চান। '
                    . 'AI word map ও daily-life scenario practice-এর মাধ্যমে vocabulary retention ও speaking fluency নিশ্চিত করে।',
                'hsk_level'          => 4,
                'price'              => 16000,
                'type'               => 'course',
                'duration_weeks'     => 12,
                'category_slug'      => 'speaking-fluency',
                'is_published'       => true,
                'is_featured'        => true,
            ],
            [
                'title'              => 'HSK Intensive Program',
                'slug'               => 'hsk-intensive-program',
                'description'        => 'HSK Intensive: দ্রুত গতিতে সিরিয়াস প্রস্তুতি। '
                    . 'HSK Intensive Program তাদের জন্য, যারা কম সময়ে স্ট্রাকচার্ড ও ফলাফল-কেন্দ্রিক প্রস্তুতি নিতে চান। '
                    . 'Accelerated lessons, priority feedback, এবং scholarship application guidance সহ — '
                    . 'যাতে upcoming deadline-এর আগেই HSK 1–4 complete করে China admission-এর জন্য আবেদন করতে পারেন।',
                'hsk_level'          => 4,
                'price'              => 20000,
                'type'               => 'course',
                'duration_weeks'     => 8,
                'category_slug'      => 'hsk-preparation',
                'is_published'       => true,
                'is_featured'        => true,
            ],

            // ── Study in China Services ───────────────────────────────────
            [
                'title'              => 'Study In China – Application Guide',
                'slug'               => 'study-in-china-application-guide',
                'description'        => 'নিজে নিজে করতে চান? শুধু সঠিক গাইডেন্স দরকার? '
                    . 'আপনি self-motivated এবং capable — নিজেই চীনে application করতে চান। কিন্তু একটা বড় সমস্যা: '
                    . 'সঠিক তথ্য ও step-by-step গাইডেন্সের অভাব। এই Application Guide-এ পাবেন সম্পূর্ণ প্রক্রিয়ার '
                    . 'detailed roadmap: document checklist, university shortlisting strategy, application timeline, '
                    . 'এবং scholarship interview tips — সবকিছু এক জায়গায়।',
                'hsk_level'          => null,
                'price'              => 25000,
                'type'               => 'service',
                'category_slug'      => 'study-in-china',
                'consultation_link'  => 'https://wa.me/8618223249514?text=I%20want%20the%20Application%20Guide',
                'is_published'       => true,
                'is_featured'        => false,
            ],
            [
                'title'              => 'Study in China – Complete Application Support',
                'slug'               => 'study-in-china-complete-support',
                'description'        => 'Full Application Support: আপনার পুরো China Process আমাদের উপর ছেড়ে দিন। '
                    . 'যারা চান কোনও ঝামেলা ছাড়া সম্পূর্ণ application process আমাদের expert টিম handle করুক, '
                    . 'তাদের জন্য এই প্যাকেজ। আমরা করব: university shortlisting, document preparation ও verification, '
                    . 'application submission, scholarship application, interview preparation, এবং visa guidance — '
                    . 'সবকিছু step-by-step, personal mentorship-এর মাধ্যমে। আপনার শুধু focus করতে হবে পড়াশোনায়।',
                'hsk_level'          => null,
                'price'              => 60000,
                'type'               => 'service',
                'category_slug'      => 'study-in-china',
                'consultation_link'  => 'https://wa.me/8618223249514?text=I%20want%20Complete%20Application%20Support',
                'is_published'       => true,
                'is_featured'        => true,
            ],
            [
                'title'              => 'Complete China Success – 1 Year Pathway',
                'slug'               => 'complete-china-success',
                'description'        => 'Complete China Success: Study থেকে Career পর্যন্ত ১-বছরের সম্পূর্ণ সাপোর্ট। '
                    . 'Complete China Success হলো আমাদের সবচেয়ে comprehensive package, যেখানে China admission, language, culture, '
                    . 'student life — সবকিছু মিলিয়ে একটি ১-বছরের complete pathway। '
                    . 'এই প্রোগ্রামে থাকছে: সম্পূর্ণ application support, ১ বছরের HSK language training, '
                    . 'pre-departure cultural orientation, accommodation assistance, এবং China-তে পৌঁছানোর পর '
                    . 'প্রথম মাসের settlement support। এটি শুধু admission নয় — এটি আপনার পুরো China journey-এর গ্যারান্টি।',
                'hsk_level'          => null,
                'price'              => 100000,
                'type'               => 'service',
                'category_slug'      => 'study-in-china',
                'consultation_link'  => 'https://wa.me/8618223249514?text=I%20want%20Complete%20China%20Success%20Pathway',
                'is_published'       => true,
                'is_featured'        => true,
            ],
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
                    'title'             => $product['title'],
                    'description'       => $product['description'],
                    'hsk_level'         => $product['hsk_level'],
                    'price'             => $product['price'],
                    'type'              => $product['type'],
                    'duration_weeks'    => $product['duration_weeks'] ?? null,
                    'category_id'       => $categoryId,
                    'consultation_link' => $product['consultation_link'] ?? null,
                    'is_published'      => $product['is_published'],
                    'is_featured'       => $product['is_featured'],
                ]
            );
        }
    }
}
