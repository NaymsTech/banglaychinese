<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Database\Seeder;

class BanglayChineseSeeder extends Seeder
{
    /**
     * Seed the BanglayChinese.com content (categories + courses) from the real site.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedCourses();

        $this->command->info('BanglayChinese site content seeded: 4 categories, 4 courses.');
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
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }

    /**
     * Seed the LMS courses with the real BanglayChinese.com programs.
     */
    protected function seedCourses(): void
    {
        $courses = [
            [
                'title' => 'Fun Chinese for Kids',
                'slug' => 'fun-chinese-for-kids',
                'description' => 'A lively, play-based Chinese program for children aged 8–13. Builds a strong HSK foundation through songs, games, stories, and interactive small-group classes designed to keep young learners engaged.',
                'hsk_level' => 1,
                'price' => 15000,
                'is_published' => true,
            ],
            [
                'title' => 'Chinese Speaking Mastery',
                'slug' => 'chinese-speaking-mastery',
                'description' => 'Master real-world Chinese conversation while preparing for HSKK + HSK4. Practice daily-life scenarios with an AI word map that accelerates vocabulary retention and speaking fluency.',
                'hsk_level' => 4,
                'price' => 20000,
                'is_published' => true,
            ],
            [
                'title' => 'HSK Standard Track',
                'slug' => 'hsk-standard-track',
                'description' => 'A structured HSK 1–4 roadmap with guided lessons, regular mock tests, and China admission preparation so you can reach your target within a clear timeline.',
                'hsk_level' => 4,
                'price' => 18000,
                'is_published' => true,
            ],
            [
                'title' => 'HSK Intensive Program',
                'slug' => 'hsk-intensive-program',
                'description' => 'Fast-track HSK 1–4 preparation built for students with upcoming scholarship deadlines. Includes accelerated lessons, priority feedback, and application guidance.',
                'hsk_level' => 4,
                'price' => 25000,
                'is_published' => true,
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['slug' => $course['slug']],
                $course
            );
        }
    }
}
