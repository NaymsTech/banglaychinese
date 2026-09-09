<?php

namespace App\Support;

/**
 * Single source of truth for the editable homepage marketing copy.
 *
 * The values below mirror what was previously hardcoded in
 * resources/views/home.blade.php. Stored home_sections rows override
 * these defaults; unknown/extra rows are ignored by the page template.
 */
class HomePageDefaults
{
    public static function groups(): array
    {
        return [
            'hero' => 'Hero',
            'roadmap' => 'Scholarship Roadmap',
            'mentor' => 'Meet Your Mentor',
            'faqs' => 'FAQ',
            'final_cta' => 'Final CTA',
        ];
    }

    /**
     * Default rows: group => [key => [value, type, label, sort_order]].
     */
    public static function data(): array
    {
        return [
            'hero' => [
                'hero_badge' => ['নতুন ব্যাচ শুরু — লাইভ ক্লাসে জায়গা সীমিত', 'string', 'Badge text', 1],
                'hero_title' => ['সরাসরি চীন থেকে এক্সক্লুসিভ মেন্টরশিপে স্কলারশিপ ও চাইনীজ ভাষা শিখুন', 'text', 'Hero title', 2],
                'hero_subtitle' => ['লাইভ ব্যাচ, HSK ১–৪ প্রস্তুতি, স্পিকিং মাস্টারি এবং CSC স্কলারশিপ সাপোর্ট — সব এক জায়গায়। চায়নার বিশ্ববিদ্যালয়ে ভর্তির স্বপ্ন পূরণ করুন বাংলায় শেখা চাইনিজে।', 'longtext', 'Hero subtitle', 3],
                'cta_primary_text' => ['🎓 Study in China', 'string', 'Primary CTA text', 4],
                'cta_secondary_text' => ['কোর্সসমূহ দেখুন', 'string', 'Secondary CTA text', 5],
                'social_proof' => ['৫০০+ শিক্ষার্থী চায়নায় স্কলারশিপ পেয়েছেন আমাদের গাইডেন্সে', 'string', 'Social proof line', 6],
                'trust_stats' => [[
                    ['value' => 'HSK ১–৪', 'label' => 'কমপ্লিট ট্র্যাক'],
                    ['value' => '৯৫%+', 'label' => 'স্কলারশিপ সাকসেস'],
                    ['value' => '১০০%', 'label' => 'বাংলা সাপোর্ট'],
                    ['value' => 'লাইভ', 'label' => 'স্মল-গ্রুপ ক্লাস'],
                ], 'json', 'Trust stats (JSON array)', 7],
            ],
            'roadmap' => [
                'roadmap_eyebrow' => ['🎓 চায়না স্কলারশিপ রোডম্যাপ', 'string', 'Eyebrow', 1],
                'roadmap_heading' => ['৪ ধাপে চীনের বিশ্ববিদ্যালয়ে ভর্তি', 'text', 'Heading', 2],
                'roadmap_subtitle' => ['Banglay Chinese-এর মেন্টরশিপে সম্পূর্ণ গাইডেন্স — শুরু থেকে ফ্লাইট পর্যন্ত।', 'text', 'Subtitle', 3],
                'roadmap_steps' => [[
                    ['num' => '১', 'icon' => '📚', 'title' => 'HSK প্রস্তুতি', 'desc' => 'HSK 1–4 প্রমাণিত রোডম্যাপে ভাষা দক্ষতা অর্জন করুন মক টেস্ট ও লাইভ ক্লাসে।', 'color' => 'bg-emerald-50 text-emerald-800'],
                    ['num' => '২', 'icon' => '📝', 'title' => 'ডকুমেন্ট ও SOP', 'desc' => 'SOP, ট্রান্সক্রিপ্ট, রেকমেন্ডেশন — সব ডকুমেন্ট এক্সপার্ট রিভিউসহ প্রস্তুত করুন।', 'color' => 'bg-sky-50 text-sky-800'],
                    ['num' => '৩', 'icon' => '🏫', 'title' => 'ইউনিভার্সিটি অ্যাপ্লিকেশন', 'desc' => 'আপনার প্রোফাইল অনুযায়ী সেরা ইউনিভার্সিটি নির্বাচন ও আবেদন টাইমলাইন।', 'color' => 'bg-amber-50 text-amber-800'],
                    ['num' => '৪', 'icon' => '✈️', 'title' => 'ভিসা ও ফ্লাইট', 'desc' => 'ভিসা প্রসেসিং, প্রি-ডিপার্চার ব্রিফিং এবং চীনে পৌঁছানোর পর সাপোর্ট।', 'color' => 'bg-red-50 text-red-700'],
                ], 'json', 'Steps (JSON array)', 4],
                'roadmap_cta_text' => ['স্কলারশিপ গাইডেন্স শুরু করুন', 'string', 'CTA text', 5],
            ],
            'mentor' => [
                'mentor_badge' => ['👋 আপনার মেন্টর', 'string', 'Badge', 1],
                'mentor_name' => ['Md. Naymur Rahman', 'string', 'Name', 2],
                'mentor_title' => ['Founder & Lead Instructor', 'string', 'Title', 3],
                'mentor_bio' => ['🇨🇳 ২০১৭ সাল থেকে চীনে পড়াশোনা করছি। স্ক্র্যাচ থেকে ফ্লুয়েন্ট — আমি নিজে এই জার্নি করেছি। এখন বাংলাদেশি শিক্ষার্থীদের সহজভাবে চাইনিজ শেখাচ্ছি।', 'longtext', 'Bio', 4],
                'mentor_initial' => ['ন', 'string', 'Avatar letter', 5],
                'mentor_cta_text' => ['সম্পূর্ণ গল্প পড়ুন', 'string', 'CTA text', 6],
            ],
            'faqs' => [
                'faq_eyebrow' => ['সাধারণ প্রশ্ন', 'string', 'Eyebrow', 1],
                'faq_heading' => ['আপনার প্রশ্নের উত্তর', 'text', 'Heading', 2],
                'faq_items' => [[
                    ['q' => 'বাংলাদেশি শিক্ষার্থীরা কীভাবে চায়না স্কলারশিপ পেতে পারে?', 'a' => 'চায়না স্কলারশিপ (CSC) পেতে HSK 3–4 লেভেলের সার্টিফিকেট, একাডেমিক ট্রান্সক্রিপ্ট, SOP এবং ইউনিভার্সিটি অ্যাপ্লিকেশন প্রয়োজন। আমাদের স্কলারশিপ মেন্টরশিপে ডকুমেন্ট প্রস্তুতি থেকে ইউনিভার্সিটি সিলেকশন পর্যন্ত সম্পূর্ণ গাইডেন্স দেওয়া হয়।'],
                    ['q' => 'HSK কী এবং কত লেভেল পর্যন্ত শেখানো হয়?', 'a' => 'HSK হলো চীনা ভাষার আন্তর্জাতিক দক্ষতা পরীক্ষা। Banglay Chinese-এ HSK 1 থেকে HSK 4 পর্যন্ত সম্পূর্ণ প্রস্তুতি করা হয় — লাইভ ক্লাস, মক টেস্ট ও পার্সোনাল ফিডব্যাক সহ।'],
                    ['q' => 'কোর্সের ফি কত এবং কী কী সুবিধা আছে?', 'a' => 'কোর্স অনুযায়ী ফি আলাদা — Fun Chinese for Kids ১২,০০০ টাকা, HSK Standard Track ১২,০০০ টাকা, Chinese Speaking Mastery ১৬,০০০ টাকা এবং HSK Intensive Program ২০,০০০ টাকা। Study in China সার্ভিস ২৫,০০০ টাকা থেকে শুরু। সব কোর্সে লাইভ স্মল-গ্রুপ ক্লাস, রেকর্ডিং, AI ওয়ার্ড ম্যাপ ও সার্টিফিকেট অন্তর্ভুক্ত।'],
                    ['q' => 'কোর্স করতে কি আগে থেকে চাইনিজ জানতে হবে?', 'a' => 'না, কোনো পূর্ব অভিজ্ঞতা লাগবে না। HSK 1 থেকে শুরু হয় আমাদের সিলেবাস — সম্পূর্ণ বাংলায় ব্যাখ্যাসহ, তাই একদম শূন্য থেকেও শুরু করতে পারবেন।'],
                    ['q' => 'লাইভ ক্লাসের সময়সূচী কেমন?', 'a' => 'লাইভ ক্লাস বাংলাদেশ সময় সন্ধ্যায় সপ্তাহে ৩ দিন হয়। প্রতিটি ক্লাসের রেকর্ডিং থাকে, তাই মিস করলেও পরে দেখে নিতে পারবেন।'],
                ], 'json', 'FAQ items (JSON array)', 3],
            ],
            'final_cta' => [
                'final_badge' => ['🔥 নতুন ব্যাচে ভর্তি চলছে', 'string', 'Badge', 1],
                'final_heading' => ['আপনার চায়না ড্রিম শুরু হোক আজই', 'text', 'Heading', 2],
                'final_subtitle' => ['জায়গা সীমিত — আজই রেজিস্ট্রেশন করুন এবং প্রথম লাইভ ক্লাসে ফ্রি অংশ নিন।', 'text', 'Subtitle', 3],
                'final_primary_cta' => ['ফ্রি রেজিস্ট্রেশন করুন', 'string', 'Primary CTA text', 4],
                'final_secondary_cta' => ['💬 WhatsApp-এ কথা বলুন', 'string', 'Secondary CTA text', 5],
            ],
        ];
    }
}
