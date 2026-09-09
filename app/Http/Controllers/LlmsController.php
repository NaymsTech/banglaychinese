<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Response;

class LlmsController extends Controller
{
    /**
     * A concise, machine-readable description of the site for AI crawlers.
     * Only real site settings, public routes and public contact details are used.
     */
    public function show(): Response
    {
        $siteName = SettingsService::get('site_name', 'Banglay Chinese');
        $tagline = SettingsService::get('site_tagline', 'বাংলায় চাইনিজ ভাষা শেখার সেরা প্লাটফর্ম');
        $metaDescription = SettingsService::get('meta_description', 'বাংলায় চাইনিজ ভাষা শিখুন। HSK প্রস্তুতি, CSC স্কলারশিপ সাপোর্ট, স্পিকিং মাস্টারি — সব এক জায়গায়।');
        $contactEmail = SettingsService::get('contact_email', 'info@banglaychinese.com');

        $sections = [
            ['Courses', route('courses.index'), 'Chinese language courses and HSK preparation in Bengali'],
            ['Study in China', route('study-in-china'), 'Guidance and services for Bangladeshi students applying to study in China'],
            ['Blog', route('posts.index'), 'Guides on learning Chinese, HSK and China scholarships'],
            ['Shop', route('shop.index'), 'Digital products such as e-books and practice materials'],
            ['Free Resources', route('free-resources.index'), 'Free vocabulary lists, grammar guides and study tips'],
            ['About', route('about'), 'About Banglay Chinese'],
            ['Contact', route('contact'), 'How to contact the team'],
            ['FAQ', route('pages.show', 'faq'), 'Frequently asked questions'],
        ];

        $lines = [
            '# '.$siteName,
            '',
            '> '.$tagline,
            '',
            $metaDescription,
            '',
            'Official website: '.url('/'),
            'Contact email: '.$contactEmail,
            '',
            '## Public content',
            '',
        ];

        foreach ($sections as [$label, $url, $description]) {
            $lines[] = sprintf('- [%s](%s): %s.', $label, $url, $description);
        }

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
