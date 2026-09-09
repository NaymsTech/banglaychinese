<?php

namespace App\Support;

/**
 * Single source of truth for the editable homepage content.
 *
 * Every key here is stored as a `settings` row (via SettingsService::set)
 * and read back by HomeController, which merges these defaults underneath
 * whatever an admin has saved — so the homepage is identical before the
 * CMS page is ever opened.
 */
class HomePageContent
{
    /**
     * Editable section groups: group name => [key => field definition].
     */
    public static function sections(): array
    {
        return [
            'Hero' => [
                'hero_badge' => ['label' => 'Badge text', 'type' => 'text', 'default' => 'New Batch Open · ভর্তি চলছে'],
                'hero_title' => ['label' => 'Headline (one line per sentence)', 'type' => 'textarea', 'default' => "Learn Chinese Live.\nStudy in China.\nUnlock Your Future."],
                'hero_subtitle' => ['label' => 'Subtitle', 'type' => 'textarea', 'default' => 'বাংলায় চীনা ভাষা শিখুন + চীনে পড়াশোনার সম্পূর্ণ গাইডেন্স — লাইভ ক্লাস, HSK প্রস্তুতি ও স্কলারশিপ মেন্টরশিপ এক জায়গায়।'],
                'hero_image' => ['label' => 'Hero image', 'type' => 'file', 'default' => ''],
                'hero_cta1_text' => ['label' => 'Primary button text', 'type' => 'text', 'default' => 'Explore Live Courses'],
                'hero_cta1_link' => ['label' => 'Primary button link', 'type' => 'link', 'default' => '/courses'],
                'hero_cta2_text' => ['label' => 'Secondary button text', 'type' => 'text', 'default' => 'Consult an Advisor'],
                'hero_cta2_link' => ['label' => 'Secondary button link', 'type' => 'link', 'default' => 'https://wa.me/8618223249514'],
            ],

            '3-Card Service Grid' => [
                'card_1_icon' => ['label' => 'Card 1 icon', 'type' => 'icon', 'default' => 'video'],
                'card_1_title' => ['label' => 'Card 1 title', 'type' => 'text', 'default' => 'Live Batches'],
                'card_1_desc' => ['label' => 'Card 1 description', 'type' => 'textarea', 'default' => 'Small-group live classes with lifetime recordings — new batches starting soon.'],
                'card_1_link' => ['label' => 'Card 1 link', 'type' => 'link', 'default' => '/courses'],
                'card_2_icon' => ['label' => 'Card 2 icon', 'type' => 'icon', 'default' => 'school'],
                'card_2_title' => ['label' => 'Card 2 title', 'type' => 'text', 'default' => 'Study in China Consultancy'],
                'card_2_desc' => ['label' => 'Card 2 description', 'type' => 'textarea', 'default' => 'University applications, CSC scholarships, visa & pre-departure guidance — end to end.'],
                'card_2_link' => ['label' => 'Card 2 link', 'type' => 'link', 'default' => '/study-in-china'],
                'card_3_icon' => ['label' => 'Card 3 icon', 'type' => 'icon', 'default' => 'clipboard'],
                'card_3_title' => ['label' => 'Card 3 title', 'type' => 'text', 'default' => 'CSCA Exam Preparation'],
                'card_3_desc' => ['label' => 'Card 3 description', 'type' => 'textarea', 'default' => 'Comprehensive preparation for Chinese Scholarship Council Assessment (CSCA) exams.'],
                'card_3_link' => ['label' => 'Card 3 link', 'type' => 'link', 'default' => ''],
            ],

            'Why Choose Us' => [
                'why_1_title' => ['label' => 'Point 1 title', 'type' => 'text', 'default' => 'Expert Mentorship'],
                'why_1_desc' => ['label' => 'Point 1 description', 'type' => 'textarea', 'default' => 'Experienced Chinese speakers & certified instructors'],
                'why_1_icon' => ['label' => 'Point 1 icon', 'type' => 'icon', 'default' => 'badge'],
                'why_2_title' => ['label' => 'Point 2 title', 'type' => 'text', 'default' => '95% Visa Success'],
                'why_2_desc' => ['label' => 'Point 2 description', 'type' => 'textarea', 'default' => 'Proven track record with 50+ Chinese universities'],
                'why_2_icon' => ['label' => 'Point 2 icon', 'type' => 'icon', 'default' => 'shield'],
                'why_3_title' => ['label' => 'Point 3 title', 'type' => 'text', 'default' => 'Affordable Pricing'],
                'why_3_desc' => ['label' => 'Point 3 description', 'type' => 'textarea', 'default' => 'Best value with flexible payment options'],
                'why_3_icon' => ['label' => 'Point 3 icon', 'type' => 'icon', 'default' => 'cash'],
                'why_4_title' => ['label' => 'Point 4 title', 'type' => 'text', 'default' => 'Complete Support'],
                'why_4_desc' => ['label' => 'Point 4 description', 'type' => 'textarea', 'default' => 'From admission to arrival in China'],
                'why_4_icon' => ['label' => 'Point 4 icon', 'type' => 'icon', 'default' => 'users'],
            ],

            'WhatsApp CTA' => [
                'wa_heading' => ['label' => 'Heading', 'type' => 'text', 'default' => 'ভর্তি সংক্রান্ত যেকোনো প্রশ্ন?'],
                'wa_subtext' => ['label' => 'Subtext', 'type' => 'textarea', 'default' => 'ইউনিভার্সিটি চয়েস, স্কলারশিপ বা ভিসা যেকোনো বিষয়ে কনফিউশন থাকলে আমাদের সাথে সরাসরি কথা বলুন।'],
                'wa_number' => ['label' => 'WhatsApp number (full international, incl. country code)', 'type' => 'text', 'default' => '8618223249514'],
            ],

            'Meet Your Mentor' => [
                'mentor_heading' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Meet Your Mentor'],
                'mentor_subtitle' => ['label' => 'Subtitle (optional)', 'type' => 'text', 'default' => ''],
                'founder_quote' => ['label' => 'Highlighted Bengali line', 'type' => 'textarea', 'default' => 'যে চীনা শেখে, সে বিশ্ব জয় করে'],
                'founder_bio' => ['label' => 'Bio', 'type' => 'textarea', 'default' => 'বাংলাদেশের শিক্ষার্থীদের জন্য চীনা ভাষা শিক্ষা এবং চীনে উচ্চশিক্ষার স্বপ্নকে বাস্তবে রূপ দিতে Banglay Chinese-এর যাত্রা শুরু। সঠিক গাইডেন্স ও অভিজ্ঞ মেন্টরশিপে বিদেশে পড়াশোনার পথটি আত্মবিশ্বাস ও সাফল্যের যাত্রায় পরিণত হয়। আপনার স্বপ্নগুলি বাস্তবে রূপ নেওয়ার যোগ্য — আসুন, এই যাত্রায় একসাথে এগিয়ে চলি!'],
                'founder_name' => ['label' => 'Founder name', 'type' => 'text', 'default' => 'Md. Naymur Rahman'],
                'founder_role' => ['label' => 'Founder role', 'type' => 'text', 'default' => 'Founder & CEO, Banglay Chinese'],
                'founder_image' => ['label' => 'Founder photo', 'type' => 'file', 'default' => ''],
                'founder_cta_text' => ['label' => 'Button text', 'type' => 'text', 'default' => 'Learn More About Us'],
                'founder_cta_link' => ['label' => 'Button link', 'type' => 'link', 'default' => '/about'],
            ],

            'Awards' => [
                'award_eyebrow' => ['label' => 'Label (bordered box)', 'type' => 'text', 'default' => 'AWARDS & RECOGNITION'],
                'award_heading' => ['label' => 'Heading', 'type' => 'text', 'default' => 'AWARD-WINNING EXCELLENCE'],
                'award_subheading' => ['label' => 'Small subheading', 'type' => 'text', 'default' => 'RECOGNITION'],
                'award_image' => ['label' => 'Award / Recognition photo', 'type' => 'file', 'default' => ''],
                'award_title' => ['label' => 'Award title', 'type' => 'text', 'default' => 'Chinese Bridge Competition 2024 — Finalist'],
                'award_description' => ['label' => 'Award description', 'type' => 'textarea', 'default' => '২০২৪ সালে চীনে ফিরে আসার পর আমি বিভিন্ন ভাষাভিত্তিক প্রতিযোগিতায় সক্রিয়ভাবে অংশগ্রহণ করি। এর মধ্যে উল্লেখযোগ্য হলো "2024 Whole World Chinese Bridge Chinese Proficiency Competition for Foreigners — Dubbing Show"। বিশ্বজুড়ে হাজারো প্রতিযোগীর মধ্য থেকে নির্বাচিত মাত্র ১০ জন ফাইনালিস্টের একজন হওয়ার গৌরব অর্জন করি।'],
                'award_quote' => ['label' => 'Quote text', 'type' => 'textarea', 'default' => 'বর্তমানে আমি চীনে শিক্ষা গ্রহণের পাশাপাশি বিভিন্ন আন্তর্জাতিক ভাষা-ভিত্তিক কার্যক্রমে সক্রিয় রয়েছি। আমার এই বাস্তব অভিজ্ঞতা এবং স্বীকৃতি Banglay Chinese-এর প্রতিটি শিক্ষার্থীকে সঠিক ও মানসম্মত গাইডলাইন প্রদানের মূল ভিত্তি।'],
            ],

            'Final CTA' => [
                'final_heading' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Take the First Step Towards Your Future'],
                'final_subtext' => ['label' => 'Subtext', 'type' => 'textarea', 'default' => 'Join thousands of students who have already started their journey with Banglay Chinese. Your dream university is just one click away.'],
                'final_btn1_text' => ['label' => 'Button 1 text', 'type' => 'text', 'default' => 'Chat on WhatsApp'],
                'final_btn1_link' => ['label' => 'Button 1 link', 'type' => 'link', 'default' => 'https://wa.me/8618223249514'],
                'final_btn2_text' => ['label' => 'Button 2 text', 'type' => 'text', 'default' => 'Book Free Consultation'],
                'final_btn2_link' => ['label' => 'Button 2 link', 'type' => 'link', 'default' => '#contact'],
            ],

            'SEO & Social Sharing' => [
                'home_meta_title' => ['label' => 'Meta title', 'type' => 'text', 'default' => 'Banglay Chinese | Learn Chinese Live. Study in China. Unlock Your Future.'],
                'home_meta_description' => ['label' => 'Meta description', 'type' => 'textarea', 'default' => 'বাংলায় চীনা ভাষা শিখুন + চীনে পড়াশোনার সম্পূর্ণ গাইডেন্স। লাইভ ব্যাচ, HSK প্রস্তুতি ও চায়না স্কলারশিপ সাপোর্ট — সব এক জায়গায়।'],
                'home_og_image' => ['label' => 'Social share image (OG)', 'type' => 'file', 'default' => '', 'maxSize' => 2048],
            ],
        ];
    }

    /**
     * Flat key => default value map.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return collect(self::sections())
            ->flatMap(fn (array $fields): array => $fields)
            ->map(fn (array $def): mixed => $def['default'])
            ->all();
    }

    /**
     * Icons available for the icon fields (emoji rendered inside the chips).
     */
    public static function icons(): array
    {
        return [
            'video' => '🎥 Live / video',
            'school' => '🎓 Graduation',
            'clipboard' => '📋 Exam / clipboard',
            'book' => '📖 Book',
            'badge' => '🏅 Achievement',
            'shield' => '🛡️ Shield / visa',
            'cash' => '💲 Pricing',
            'users' => '👥 Support / users',
            'globe' => '🌍 Globe',
            'trophy' => '🏆 Trophy',
            'briefcase' => '💼 Briefcase',
            'chat' => '💬 Chat',
            'rocket' => '🚀 Rocket',
            'star' => '⭐ Star',
        ];
    }
}
