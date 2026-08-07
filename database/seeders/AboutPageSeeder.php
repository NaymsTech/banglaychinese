<?php

namespace Database\Seeders;

use App\Models\AboutSection;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            // === HERO ===
            ['key' => 'hero_badge',           'value' => 'ABOUT US',                        'type' => 'string', 'group' => 'hero',       'label' => 'Hero Badge',         'sort_order' => 1],
            ['key' => 'hero_heading',          'value' => 'Meet the Founder',                'type' => 'string', 'group' => 'hero',       'label' => 'Hero Heading',       'sort_order' => 2],
            ['key' => 'hero_name',             'value' => 'Md. Naymur Rahman',               'type' => 'string', 'group' => 'hero',       'label' => 'Founder Name',       'sort_order' => 3],
            ['key' => 'hero_title',            'value' => 'Founder & Lead Instructor',       'type' => 'string', 'group' => 'hero',       'label' => 'Founder Title',      'sort_order' => 4],
            ['key' => 'hero_image',            'value' => null,                              'type' => 'image',  'group' => 'hero',       'label' => 'Founder Portrait',   'sort_order' => 5],
            ['key' => 'hero_highlights',       'value' => json_encode([
                ['icon' => '🇨🇳', 'text' => 'Studying in China Since 2017'],
                ['icon' => '💼', 'text' => '2+ Years Professional Chinese Interpreter'],
                ['icon' => '🏆', 'text' => 'Chinese Bridge 2024 Top 10 Finalist'],
                ['icon' => '🎓', 'text' => 'Helping Bangladeshi Students Learn Chinese'],
            ]), 'type' => 'json', 'group' => 'hero', 'label' => 'Hero Highlights (Json Array)', 'sort_order' => 6],
            ['key' => 'hero_cta_primary_text', 'value' => 'Explore Courses',                 'type' => 'string', 'group' => 'hero',       'label' => 'Primary CTA Text',   'sort_order' => 7],
            ['key' => 'hero_cta_primary_url',  'value' => '/courses',                        'type' => 'string', 'group' => 'hero',       'label' => 'Primary CTA URL',    'sort_order' => 8],
            ['key' => 'hero_cta_secondary_text','value' => 'Book Consultation',               'type' => 'string', 'group' => 'hero',       'label' => 'Secondary CTA Text', 'sort_order' => 9],
            ['key' => 'hero_cta_secondary_url','value' => '/study-in-china/consultation',    'type' => 'string', 'group' => 'hero',       'label' => 'Secondary CTA URL',  'sort_order' => 10],

            // === STORY ===
            ['key' => 'story_heading_bn',      'value' => 'আমার গল্প',                      'type' => 'string', 'group' => 'story',      'label' => 'Story Heading (Bangla)','sort_order' => 1],
            ['key' => 'story_subheading',      'value' => 'শূন্য থেকে চীনে — একটি বাস্তব যাত্রা','type' => 'string', 'group' => 'story',  'label' => 'Story Subheading',   'sort_order' => 2],
            ['key' => 'story_content',         'value' => "When I arrived in China in 2017, I knew absolutely no Chinese.\n\nEverything was new. A new country. A new language. A new culture.\n\nI wondered if I would ever be able to speak Chinese.\n\nBy the grace of Allah, after only five months I became fluent enough to communicate confidently. That experience completely changed my life.\n\nIt taught me that Chinese is not impossible. With the right methodology and proper guidance, anyone can learn it.\n\nThat experience later became the foundation of BanglayChinese.\n\nThe goal of this platform is simple: to make Chinese learning easier for Bangladeshi students.", 'type' => 'text', 'group' => 'story', 'label' => 'Story Content', 'sort_order' => 3],

            // === TIMELINE ===
            ['key' => 'timeline_heading',      'value' => 'My Journey',                      'type' => 'string', 'group' => 'timeline',   'label' => 'Timeline Heading',    'sort_order' => 1],
            ['key' => 'timeline_items',        'value' => json_encode([
                ['year' => '2017', 'title' => 'Moved to China',            'description' => 'Arrived in China with zero Chinese language knowledge.'],
                ['year' => '2017', 'title' => 'Started Learning Chinese',  'description' => 'Began the journey of mastering Mandarin Chinese.'],
                ['year' => '2017', 'title' => 'Fluent within 5 Months',    'description' => 'Achieved conversational fluency in just five months.'],
                ['year' => '2018', 'title' => 'Worked as Professional Interpreter', 'description' => 'Served as a professional Chinese interpreter.'],
                ['year' => '2023', 'title' => 'Returned to China',         'description' => 'Came back to China for further studies and opportunities.'],
                ['year' => '2024', 'title' => 'Chinese Bridge Top 10 Finalist', 'description' => 'Selected among the Top 10 worldwide in the Chinese Bridge Dubbing Competition.'],
                ['year' => '2024', 'title' => 'Founded BanglayChinese',    'description' => 'Launched a platform to help Bangladeshi students learn Chinese.'],
                ['year' => '2025', 'title' => 'Helping Bangladeshi Students', 'description' => 'Guiding students across Bangladesh on their Chinese language journey.'],
            ]), 'type' => 'json', 'group' => 'timeline', 'label' => 'Timeline Items (Json Array)', 'sort_order' => 2],

            // === EXPERIENCE ===
            ['key' => 'experience_heading',    'value' => 'Experience & Recognition',        'type' => 'string', 'group' => 'experience', 'label' => 'Experience Heading',  'sort_order' => 1],
            ['key' => 'experience_cards',      'value' => json_encode([
                [
                    'title'       => 'Professional Interpreter',
                    'icon'        => '💼',
                    'description' => 'Worked approximately two years as a Chinese interpreter during the COVID period. This experience provided practical professional Mandarin communication skills working with Chinese companies and international teams.',
                ],
                [
                    'title'       => 'Chinese Bridge Finalist',
                    'icon'        => '🏆',
                    'description' => 'Selected among the Top 10 Finalists worldwide in the 2024 Chinese Bridge Dubbing Competition — an internationally recognized Chinese proficiency competition that demonstrates advanced Mandarin ability.',
                ],
            ]), 'type' => 'json', 'group' => 'experience', 'label' => 'Experience Cards (Json Array)', 'sort_order' => 2],

            // === WHY ===
            ['key' => 'why_heading',           'value' => 'Why did I build this platform?',  'type' => 'string', 'group' => 'why',        'label' => 'Why Section Heading','sort_order' => 1],
            ['key' => 'why_content',           'value' => "When I was learning Chinese, I realized there were almost no organized Chinese learning resources in Bangla.\n\nMost resources were English-based. Many Bangladeshi students struggled to find quality guidance.\n\nI decided to build the platform I wished existed when I started.\n\nBanglayChinese is built by a Bangladeshi student who experienced the entire journey himself.", 'type' => 'text', 'group' => 'why', 'label' => 'Why Content', 'sort_order' => 2],

            // === MISSION ===
            ['key' => 'mission_heading',       'value' => 'Our Mission',                     'type' => 'string', 'group' => 'mission',    'label' => 'Mission Heading',     'sort_order' => 1],
            ['key' => 'mission_cards',         'value' => json_encode([
                ['icon' => '📚', 'title' => 'Affordable Education',      'description' => 'Provide practical and affordable Chinese education in Bangla for everyone.'],
                ['icon' => '📝', 'title' => 'HSK Preparation',           'description' => 'Prepare students thoroughly for HSK exams at all levels.'],
                ['icon' => '🎓', 'title' => 'Study in China',            'description' => 'Help students pursue higher education opportunities in China.'],
                ['icon' => '🧭', 'title' => 'Beyond Language',           'description' => 'Guide students beyond language learning — life, culture, and career.'],
            ]), 'type' => 'json', 'group' => 'mission', 'label' => 'Mission Cards (Json Array)', 'sort_order' => 2],

            // === COMMITMENT ===
            ['key' => 'commitment_heading',    'value' => 'Our Commitment',                  'type' => 'string', 'group' => 'commitment', 'label' => 'Commitment Heading',  'sort_order' => 1],
            ['key' => 'commitment_cards',      'value' => json_encode([
                ['icon' => '🗣️', 'title' => 'Chinese Language Learning', 'description' => 'From your first "Ni Hao" to confident communication.'],
                ['icon' => '🎓', 'title' => 'Scholarship Guidance',       'description' => 'Helping students prepare for study opportunities in China.'],
                ['icon' => '✈️', 'title' => 'Study in China Support',     'description' => 'Helping students prepare for life and studies in China.'],
                ['icon' => '📜', 'title' => 'Career Growth',              'description' => 'Helping students build opportunities through Chinese language skills.'],
            ]), 'type' => 'json', 'group' => 'commitment', 'label' => 'Commitment Cards (Json Array)', 'sort_order' => 2],
            ['key' => 'commitment_quote',      'value' => 'Your success is our success.',    'type' => 'string', 'group' => 'commitment', 'label' => 'Commitment Quote',    'sort_order' => 3],

            // === VISION ===
            ['key' => 'vision_heading',        'value' => 'Our Vision',                      'type' => 'string', 'group' => 'vision',     'label' => 'Vision Heading',      'sort_order' => 1],
            ['key' => 'vision_content',        'value' => "We aim to become Bangladesh's most trusted Chinese Learning and Study in China platform.\n\nWe are building more than courses. We are building a community.", 'type' => 'text', 'group' => 'vision', 'label' => 'Vision Content', 'sort_order' => 2],

            // === WHY CHOOSE US ===
            ['key' => 'choose_us_heading',     'value' => 'Why Choose Us',                   'type' => 'string', 'group' => 'choose_us',  'label' => 'Why Choose Us Heading','sort_order' => 1],
            ['key' => 'choose_us_cards',       'value' => json_encode([
                ['icon' => '✅', 'title' => 'Real Experience',         'description' => 'Learn from someone who has walked the path.'],
                ['icon' => '🇧🇩', 'title' => 'Designed for Bangladeshis','description' => 'Content built specifically for Bangla-speaking students.'],
                ['icon' => '📈', 'title' => 'Step-by-Step Learning',   'description' => 'A structured approach from beginner to advanced.'],
                ['icon' => '💰', 'title' => 'Affordable Education',    'description' => 'Quality education that does not break the bank.'],
                ['icon' => '🎓', 'title' => 'Study in China Guidance', 'description' => 'Complete support for your study abroad journey.'],
                ['icon' => '🤝', 'title' => 'Long-term Mentorship',    'description' => 'We stay with you beyond the classroom.'],
            ]), 'type' => 'json', 'group' => 'choose_us', 'label' => 'Why Choose Us Cards (Json Array)', 'sort_order' => 2],
            ['key' => 'choose_us_quote',       'value' => "We don't just sell courses. We help students build their future.", 'type' => 'string', 'group' => 'choose_us', 'label' => 'Why Choose Us Quote','sort_order' => 3],

            // === FAQ ===
            ['key' => 'faq_heading',           'value' => 'Frequently Asked Questions',      'type' => 'string', 'group' => 'faq',        'label' => 'FAQ Heading',         'sort_order' => 1],
            ['key' => 'faq_items',             'value' => json_encode([
                ['question' => 'Can I really learn Chinese?',           'answer' => 'Yes, absolutely. With the right guidance and consistent effort, anyone can learn Chinese. Our founder learned it from scratch in just five months.'],
                ['question' => 'Is scholarship really possible?',       'answer' => 'Yes. Many Bangladeshi students receive scholarships to study in China every year. We guide you through the entire process.'],
                ['question' => 'Can I adjust to life in China?',        'answer' => 'Yes. We provide practical guidance on culture, food, daily life, and academic expectations so you feel prepared and confident.'],
            ]), 'type' => 'json', 'group' => 'faq', 'label' => 'FAQ Items (Json Array)', 'sort_order' => 2],

            // === CTA ===
            ['key' => 'cta_heading',           'value' => 'Start Your Chinese Journey Today', 'type' => 'string', 'group' => 'cta',        'label' => 'CTA Heading',         'sort_order' => 1],
            ['key' => 'cta_primary_text',      'value' => 'Explore Courses',                 'type' => 'string', 'group' => 'cta',        'label' => 'CTA Primary Text',    'sort_order' => 2],
            ['key' => 'cta_primary_url',       'value' => '/courses',                        'type' => 'string', 'group' => 'cta',        'label' => 'CTA Primary URL',     'sort_order' => 3],
            ['key' => 'cta_secondary_text',    'value' => 'Book Consultation',               'type' => 'string', 'group' => 'cta',        'label' => 'CTA Secondary Text',  'sort_order' => 4],
            ['key' => 'cta_secondary_url',     'value' => '/study-in-china/consultation',    'type' => 'string', 'group' => 'cta',        'label' => 'CTA Secondary URL',   'sort_order' => 5],
        ];

        foreach ($sections as $section) {
            AboutSection::updateOrCreate(
                ['key' => $section['key']],
                $section
            );
        }
    }
}
