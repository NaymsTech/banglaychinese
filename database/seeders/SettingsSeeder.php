<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Site Info
            ['key' => 'site_name', 'value' => 'Banglay Chinese'],
            ['key' => 'site_tagline', 'value' => 'বাংলায় চাইনিজ ভাষা শেখার সেরা প্লাটফর্ম'],
            ['key' => 'site_logo', 'value' => ''],
            ['key' => 'site_favicon', 'value' => ''],

            // Contact Info
            ['key' => 'whatsapp_number', 'value' => '8618223249514'],
            ['key' => 'contact_email', 'value' => 'info@banglaychinese.com'],
            ['key' => 'physical_address', 'value' => 'Beijing, China'],
            ['key' => 'bkash_number', 'value' => '01700000000'],
            ['key' => 'nagad_number', 'value' => '01700000000'],

            // Homepage
            ['key' => 'hero_title', 'value' => 'সরাসরি চীন থেকে এক্সক্লুসিভ মেন্টরশিপে স্কলারশিপ ও চাইনীজ ভাষা শিখুন'],
            ['key' => 'hero_subtitle', 'value' => 'লাইভ ব্যাচ, HSK ১–৪ প্রস্তুতি, স্পিকিং মাস্টারি এবং CSC স্কলারশিপ সাপোর্ট — সব এক জায়গায়। চায়নার বিশ্ববিদ্যালয়ে ভর্তির স্বপ্ন পূরণ করুন বাংলায় শেখা চাইনিজে।'],
            ['key' => 'stats_students', 'value' => '৫০০+'],
            ['key' => 'stats_courses', 'value' => '১০+'],
            ['key' => 'stats_years', 'value' => '৫+'],
            ['key' => 'stats_success_rate', 'value' => '৯৫%+'],
            ['key' => 'stats_support', 'value' => '১০০% বাংলা'],
            ['key' => 'stats_hsk_range', 'value' => 'HSK ১–৪'],

            // SEO
            ['key' => 'meta_description', 'value' => 'বাংলায় চাইনিজ ভাষা শিখুন। HSK প্রস্তুতি, CSC স্কলারশিপ সাপোর্ট, স্পিকিং মাস্টারি — সব এক জায়গায়।'],
            ['key' => 'google_analytics_id', 'value' => ''],
            ['key' => 'facebook_pixel_id', 'value' => ''],

            // Study in China / Mentorship
            ['key' => 'study_in_china_whatsapp', 'value' => '8618223249514'],
            ['key' => 'study_in_china_phone', 'value' => '01300000000'],
            ['key' => 'study_in_china_office', 'value' => 'Beijing, China'],

            // Social Links
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/banglaychinese'],
            ['key' => 'youtube_url', 'value' => 'https://youtube.com/@banglaychinese'],
            ['key' => 'linkedin_url', 'value' => ''],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
