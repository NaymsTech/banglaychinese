<?php

namespace App\Support;

/**
 * Single source of truth for the editable Study in China landing page content.
 *
 * Every key is stored as a `settings` row (via SettingsService::set) and read
 * back by ScholarshipController, which merges these defaults underneath whatever
 * an admin has saved. List keys hold PHP arrays; the controller JSON-encodes
 * them into settings rows and decodes them back for the view.
 *
 * The three service packages (Guided / Full / Elite) are NOT here — they are
 * managed exclusively through the `services` table (Admin → Services) and the
 * Blade keeps rendering them from $services untouched.
 */
class StudyInChinaContent
{
    /**
     * Groups in public page order: group name => [keys in order].
     */
    public const GROUPS = [
        'Hero' => [
            'sic_hero_badge', 'sic_hero_title', 'sic_hero_subtitle', 'sic_hero_note',
            'sic_hero_cta1_text', 'sic_hero_cta1_url', 'sic_hero_cta2_text', 'sic_hero_cta2_url',
            'sic_hero_checklist_title', 'sic_hero_checklist',
        ],
        'Trust Strip' => [
            'sic_trust_values',
        ],
        'Why Study in China' => [
            'sic_why_china_eyebrow', 'sic_why_china_title', 'sic_why_china_intro', 'sic_why_china_cards',
            'sic_why_china_cta_text',
        ],
        'Programs' => [
            'sic_programs_eyebrow', 'sic_programs_title', 'sic_programs_intro', 'sic_programs_cards',
        ],
        'Scholarships' => [
            'sic_scholarships_eyebrow', 'sic_scholarships_title', 'sic_scholarships_intro', 'sic_scholarships_cards',
            'sic_scholarships_note', 'sic_scholarships_cta_text',
        ],
        'Eligibility' => [
            'sic_eligibility_eyebrow', 'sic_eligibility_title', 'sic_eligibility_intro', 'sic_eligibility_cards',
            'sic_eligibility_box_title', 'sic_eligibility_factors', 'sic_eligibility_note', 'sic_eligibility_cta_text',
        ],
        'Roadmap' => [
            'sic_roadmap_eyebrow', 'sic_roadmap_title', 'sic_roadmap_intro', 'sic_roadmap_steps',
        ],
        'Why Banglay Chinese' => [
            'sic_why_banglay_eyebrow', 'sic_why_banglay_title', 'sic_why_banglay_pillars',
        ],
        'More Than an Application' => [
            'sic_diff_title', 'sic_diff_intro', 'sic_diff_rows',
            'sic_flagship_badge', 'sic_flagship_title', 'sic_flagship_body_1', 'sic_flagship_body_2', 'sic_flagship_cta_text',
        ],
        'Which Service Is Right' => [
            'sic_decision_eyebrow', 'sic_decision_title', 'sic_decision_quotes', 'sic_decision_note',
        ],
        'Human Support' => [
            'sic_human_eyebrow', 'sic_human_title', 'sic_human_intro', 'sic_human_steps',
        ],
        'Next Steps' => [
            'sic_next_steps_eyebrow', 'sic_next_steps_title', 'sic_next_steps_intro', 'sic_next_steps_list', 'sic_next_steps_note',
        ],
        'FAQ' => [
            'sic_faq_eyebrow', 'sic_faq_title', 'sic_faq_items',
        ],
        'Final CTA' => [
            'sic_cta_title', 'sic_cta_body', 'sic_cta_btn1_text', 'sic_cta_btn2_text', 'sic_cta_note',
        ],
        'Consultation Intro' => [
            'sic_form_eyebrow', 'sic_form_title', 'sic_form_intro',
        ],
    ];

    /**
     * Key => [label, type]. Type drives the CMS component:
     * 'text' => TextInput, 'area' => Textarea, 'rich' => RichEditor, 'list' => Repeater.
     *
     * @return array<string, array{label: string, type: string}>
     */
    public static function fields(): array
    {
        return [
            // Hero
            'sic_hero_badge' => ['label' => 'Badge', 'type' => 'text'],
            'sic_hero_title' => ['label' => 'Headline', 'type' => 'text'],
            'sic_hero_subtitle' => ['label' => 'Subtitle', 'type' => 'area'],
            'sic_hero_note' => ['label' => 'Tagline (under the subtitle)', 'type' => 'area'],
            'sic_hero_cta1_text' => ['label' => 'Primary button text', 'type' => 'text'],
            'sic_hero_cta1_url' => ['label' => 'Primary button link', 'type' => 'text'],
            'sic_hero_cta2_text' => ['label' => 'Secondary button text', 'type' => 'text'],
            'sic_hero_cta2_url' => ['label' => 'Secondary button link', 'type' => 'text'],
            'sic_hero_checklist_title' => ['label' => 'Checklist card heading', 'type' => 'text'],
            'sic_hero_checklist' => ['label' => 'Checklist items', 'type' => 'list'],

            // Trust strip
            'sic_trust_values' => ['label' => 'Value cards', 'type' => 'list'],

            // Why Study in China
            'sic_why_china_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_why_china_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_why_china_intro' => ['label' => 'Intro', 'type' => 'area'],
            'sic_why_china_cards' => ['label' => 'Reason cards', 'type' => 'list'],
            'sic_why_china_cta_text' => ['label' => 'Bottom button text', 'type' => 'text'],

            // Programs
            'sic_programs_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_programs_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_programs_intro' => ['label' => 'Intro', 'type' => 'area'],
            'sic_programs_cards' => ['label' => 'Program cards', 'type' => 'list'],

            // Scholarships
            'sic_scholarships_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_scholarships_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_scholarships_intro' => ['label' => 'Intro', 'type' => 'area'],
            'sic_scholarships_cards' => ['label' => 'Scholarship cards', 'type' => 'list'],
            'sic_scholarships_note' => ['label' => 'Reminder note', 'type' => 'rich'],
            'sic_scholarships_cta_text' => ['label' => 'Bottom button text', 'type' => 'text'],

            // Eligibility
            'sic_eligibility_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_eligibility_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_eligibility_intro' => ['label' => 'Intro', 'type' => 'area'],
            'sic_eligibility_cards' => ['label' => 'Who-can-apply cards', 'type' => 'list'],
            'sic_eligibility_box_title' => ['label' => 'Box heading', 'type' => 'text'],
            'sic_eligibility_factors' => ['label' => 'Factor chips', 'type' => 'list'],
            'sic_eligibility_note' => ['label' => 'Box note', 'type' => 'text'],
            'sic_eligibility_cta_text' => ['label' => 'Bottom button text', 'type' => 'text'],

            // Roadmap
            'sic_roadmap_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_roadmap_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_roadmap_intro' => ['label' => 'Intro', 'type' => 'area'],
            'sic_roadmap_steps' => ['label' => 'Steps', 'type' => 'list'],

            // Why Banglay Chinese
            'sic_why_banglay_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_why_banglay_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_why_banglay_pillars' => ['label' => 'Pillars', 'type' => 'list'],

            // More Than an Application (differentiator band)
            'sic_diff_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_diff_intro' => ['label' => 'Intro', 'type' => 'area'],
            'sic_diff_rows' => ['label' => 'Differentiator rows', 'type' => 'list'],
            'sic_flagship_badge' => ['label' => 'Badge', 'type' => 'text'],
            'sic_flagship_title' => ['label' => 'Card heading', 'type' => 'text'],
            'sic_flagship_body_1' => ['label' => 'Paragraph 1', 'type' => 'rich'],
            'sic_flagship_body_2' => ['label' => 'Paragraph 2', 'type' => 'rich'],
            'sic_flagship_cta_text' => ['label' => 'Button text', 'type' => 'text'],

            // Which service
            'sic_decision_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_decision_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_decision_quotes' => ['label' => 'Quote cards', 'type' => 'list'],
            'sic_decision_note' => ['label' => 'Footnote', 'type' => 'area'],

            // Human support
            'sic_human_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_human_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_human_intro' => ['label' => 'Intro', 'type' => 'area'],
            'sic_human_steps' => ['label' => 'Process steps', 'type' => 'list'],

            // Next steps
            'sic_next_steps_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_next_steps_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_next_steps_intro' => ['label' => 'Intro', 'type' => 'area'],
            'sic_next_steps_list' => ['label' => 'Step sentences', 'type' => 'list'],
            'sic_next_steps_note' => ['label' => 'Footnote', 'type' => 'area'],

            // FAQ
            'sic_faq_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_faq_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_faq_items' => ['label' => 'FAQ items', 'type' => 'list'],

            // Final CTA
            'sic_cta_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_cta_body' => ['label' => 'Subtext', 'type' => 'area'],
            'sic_cta_btn1_text' => ['label' => 'Button 1 text', 'type' => 'text'],
            'sic_cta_btn2_text' => ['label' => 'Button 2 text', 'type' => 'text'],
            'sic_cta_note' => ['label' => 'Microcopy under the buttons', 'type' => 'area'],

            // Consultation intro
            'sic_form_eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
            'sic_form_title' => ['label' => 'Heading', 'type' => 'text'],
            'sic_form_intro' => ['label' => 'Intro', 'type' => 'area'],
        ];
    }

    /**
     * Flat key => default value map. List keys carry PHP arrays that are
     * JSON-encoded when stored in the settings table.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            // ===== Hero =====
            'sic_hero_badge' => '🇨🇳 Study in China Guidance for Bangladeshi Students',
            'sic_hero_title' => 'চীনে পড়াশোনার স্বপ্নকে একটি পরিষ্কার পরিকল্পনায় পরিণত করুন',
            'sic_hero_subtitle' => 'University selection, scholarship guidance, application support, Chinese language training এবং long-term mentorship — Banglay Chinese-এর সাথে আপনার China journey শুরু করুন।',
            'sic_hero_note' => 'Admission Support + Chinese Language + 1-Year Mentorship',
            'sic_hero_cta1_text' => 'Get Eligibility Review',
            'sic_hero_cta1_url' => '#consultation',
            'sic_hero_cta2_text' => 'Explore Study in China',
            'sic_hero_cta2_url' => '#services',
            'sic_hero_checklist_title' => 'আপনার সাথে যা থাকছে',
            'sic_hero_checklist' => [
                ['title' => 'University & Program Guidance', 'sub' => 'সঠিক university ও program বেছে নেওয়ার সাহায্য'],
                ['title' => 'Scholarship Support', 'sub' => 'Eligibility বুঝে scholarship-এর দিকনির্দেশনা'],
                ['title' => 'Application Support', 'sub' => 'সম্পূর্ণ process-এ human-led guidance'],
                ['title' => 'Chinese Language Training', 'sub' => 'বাংলায় Chinese শেখার সুযোগ'],
                ['title' => '1-Year Mentorship', 'sub' => 'দীর্ঘমেয়াদি structured support'],
            ],

            // ===== Trust strip =====
            'sic_trust_values' => [
                ['icon' => '🎓', 'title' => 'University Guidance', 'desc' => 'নিজের profile অনুযায়ী university ও program বেছে নেওয়ার দিকনির্দেশনা'],
                ['icon' => '🏅', 'title' => 'Scholarship Support', 'desc' => 'Eligibility ও types বুঝে scholarship-এর সম্ভাব্য দিক'],
                ['icon' => '📝', 'title' => 'Application Assistance', 'desc' => 'Documents ও application process-এ human-led help'],
                ['icon' => '🗓️', 'title' => '1-Year Mentorship', 'desc' => 'Elite students-দের জন্য দীর্ঘমেয়াদি structured support'],
            ],

            // ===== Why Study in China =====
            'sic_why_china_eyebrow' => 'Why China?',
            'sic_why_china_title' => 'কেন বাংলাদেশি শিক্ষার্থীরা China বেছে নেয়?',
            'sic_why_china_intro' => 'China পড়াশোনার জন্য একটি জনপ্রিয় গন্তব্য। তবে প্রতিটি student-এর জন্য সব option উপযুক্ত নয় — যা আপনার জন্য সঠিক, তা বুঝতে প্রোফাইলভিত্তিক review প্রয়োজন।',
            'sic_why_china_cards' => [
                ['icon' => '📚', 'title' => 'বিভিন্ন Program', 'desc' => 'Bachelor’s, Master’s, PhD, Chinese Language — অসংখ্য বিষয়ে পড়ার সুযোগ।'],
                ['icon' => '🏅', 'title' => 'Scholarship সুযোগ', 'desc' => 'University, government ও provincial level-এ নানা scholarship-এর সম্ভাবনা।'],
                ['icon' => '🌏', 'title' => 'আন্তর্জাতিক পরিবেশ', 'desc' => 'বিশ্বের বিভিন্ন দেশের শিক্ষার্থীদের সাথে পড়াশোনার সুযোগ।'],
                ['icon' => '🔬', 'title' => 'প্রযুক্তি ও গবেষণা', 'desc' => 'প্রযুক্তি ও গবেষণা-কেন্দ্রিক program-এ উন্নত সুযোগ।'],
                ['icon' => '🗣️', 'title' => 'Chinese Language', 'desc' => 'China-তে পড়তে গেলে ভাষা শেখার বাস্তব পরিবেশ পাওয়া যায়।'],
                ['icon' => '🏫', 'title' => 'নানা University Option', 'desc' => 'শহর, program ও budget অনুযায়ী অনেক university বেছে নেওয়ার সুযোগ।'],
            ],
            'sic_why_china_cta_text' => 'Check Your Eligibility',

            // ===== Programs =====
            'sic_programs_eyebrow' => 'Programs',
            'sic_programs_title' => 'China-তে কী কী পড়া যায়?',
            'sic_programs_intro' => 'এগুলো China-তে পড়ার program category — Banglay Chinese-এর নিয়মিত Chinese language course নয়। কোন category আপনার জন্য সম্ভব, তা program ও university-র উপর নির্ভর করে।',
            'sic_programs_cards' => [
                ['deg' => 'Bachelor’s', 'for' => 'HSC / equivalent শিক্ষার্থীদের জন্য', 'desc' => 'Undergraduate study plan করছেন এমন students-দের জন্য।'],
                ['deg' => 'Master’s', 'for' => 'Graduate শিক্ষার্থীদের জন্য', 'desc' => 'Advanced study করতে চাওয়া graduates-দের জন্য।'],
                ['deg' => 'PhD', 'for' => 'Research-focused applicants-দের জন্য', 'desc' => 'গবেষণায় আগ্রহী শিক্ষার্থীদের জন্য।'],
                ['deg' => 'Chinese Language Programs', 'for' => 'ভাষা শিখতে আগ্রহীদের জন্য', 'desc' => 'China-তে Chinese language study করতে চাওয়া students-দের জন্য।'],
                ['deg' => 'Diploma / Other Programs', 'for' => 'নির্দিষ্ট intake-এ উপযুক্ত applicants-দের জন্য', 'desc' => 'Institution ও intake অনুযায়ী কিছু program-এর ক্ষেত্রে প্রযোজ্য।'],
            ],

            // ===== Scholarships =====
            'sic_scholarships_eyebrow' => 'Scholarship',
            'sic_scholarships_title' => 'Scholarship-এর সম্ভাব্য সুযোগ',
            'sic_scholarships_intro' => 'China-তে পড়তে আগ্রহী students-দের জন্য নানা ধরনের scholarship-এর সুযোগ থাকতে পারে। কোনটি আপনার জন্য প্রযোজ্য, তা university, program ও intake-এর উপর নির্ভর করে।',
            'sic_scholarships_cards' => [
                ['title' => 'University Scholarships', 'desc' => 'অনেক university নিজস্ব scholarship-এর সুযোগ দেয়।'],
                ['title' => 'Chinese Government Scholarship', 'desc' => 'বিভিন্ন level-এ government-funded scholarship-এর সম্ভাবনা।'],
                ['title' => 'Provincial / Institutional', 'desc' => 'কিছু region বা institution নিজস্ব funding-এর সুযোগ দেয়।'],
                ['title' => 'University-Specific Funding', 'desc' => 'নির্দিষ্ট program বা university-এর নিজস্ব সুযোগ থাকতে পারে।'],
            ],
            'sic_scholarships_note' => 'Scholarship-এর availability, eligibility ও funding প্রতিটি university, program ও intake-এ ভিন্ন হয়। কোনো scholarship guaranteed নয় — যা সম্ভব, তা review করে জানানো হয়।',
            'sic_scholarships_cta_text' => 'Check Scholarship Eligibility',

            // ===== Eligibility =====
            'sic_eligibility_eyebrow' => 'Eligibility',
            'sic_eligibility_title' => 'কে আবেদন করতে পারে?',
            'sic_eligibility_intro' => 'নিচের category-র students সাধারণত China study-এর কথা ভাবতে পারেন। তবে কে যোগ্য, তা university ও program-এর requirements-এর উপর নির্ভর করে।',
            'sic_eligibility_cards' => [
                ['t' => 'HSC / Equivalent Students', 'd' => 'Bachelor’s program-এ আবেদন করতে চান এমন students।'],
                ['t' => 'Bachelor’s Graduates', 'd' => 'Master’s-এর জন্য আবেদন করতে চান এমন graduates।'],
                ['t' => 'Master’s Applicants', 'd' => 'Advanced study অথবা PhD-এর প্রস্তুতি নিচ্ছেন।'],
                ['t' => 'PhD Applicants', 'd' => 'Research-focused PhD program-এ আগ্রহী students।'],
                ['t' => 'Chinese Language Applicants', 'd' => 'ভাষা program-এ পড়তে চান এমন students।'],
            ],
            'sic_eligibility_box_title' => 'Eligibility যার উপর নির্ভর করে',
            'sic_eligibility_factors' => [
                ['label' => 'Academic background'],
                ['label' => 'Academic results'],
                ['label' => 'Desired program'],
                ['label' => 'Language requirements'],
                ['label' => 'University requirements'],
                ['label' => 'Intake'],
                ['label' => 'Documentation'],
                ['label' => 'Financial considerations'],
            ],
            'sic_eligibility_note' => 'Eligibility university ও program অনুযায়ী ভিন্ন হয়।',
            'sic_eligibility_cta_text' => 'Get Personal Eligibility Review',

            // ===== Roadmap =====
            'sic_roadmap_eyebrow' => 'Roadmap',
            'sic_roadmap_title' => 'China Application Journey-র ধাপগুলো',
            'sic_roadmap_intro' => 'নিচের প্রক্রিয়াটি একটি সাধারণ দিকনির্দেশনা। প্রতিটি ধাপ university, program ও intake অনুযায়ী ভিন্ন হতে পারে — কোনো ধাপ বা ফলাফল guaranteed নয়।',
            'sic_roadmap_steps' => [
                ['num' => '01', 'title' => 'Profile Evaluation', 'desc' => 'আপনার academic background ও goals বুঝে নেওয়া।'],
                ['num' => '02', 'title' => 'Eligibility Assessment', 'desc' => 'কোন program-এ আপনি eligible, তা যাচাই করা।'],
                ['num' => '03', 'title' => 'Program & University Selection', 'desc' => 'আপনার জন্য উপযুক্ত option চিহ্নিত করা।'],
                ['num' => '04', 'title' => 'Document Preparation', 'desc' => 'প্রয়োজনীয় documents তৈরি ও review করা।'],
                ['num' => '05', 'title' => 'Application Submission', 'desc' => 'University requirements অনুযায়ী application জমা দেওয়া।'],
                ['num' => '06', 'title' => 'Admission / Scholarship Process', 'desc' => 'পরবর্তী ধাপ ও requirements-এ সাড়া দেওয়া।'],
                ['num' => '07', 'title' => 'Visa Preparation', 'desc' => 'China-তে যাওয়ার প্রস্তুতির দিকনির্দেশনা।'],
                ['num' => '08', 'title' => 'Pre-departure & Next Steps', 'desc' => 'চীনে যাত্রা শুরুর প্রস্তুতি নেওয়া।'],
            ],

            // ===== Why Banglay Chinese =====
            'sic_why_banglay_eyebrow' => 'Why Banglay Chinese?',
            'sic_why_banglay_title' => 'আমরা শুধু application-এ সাহায্য করি না — আমরা China-র জন্য প্রস্তুত করি',
            'sic_why_banglay_pillars' => [
                ['num' => '01', 'title' => 'Bangla-first Guidance', 'desc' => 'জটিল China-study তথ্য সহজ ও স্পষ্ট বাংলায় বোঝানো হয়।'],
                ['num' => '02', 'title' => 'Human Support', 'desc' => 'আপনার application ও profile আমাদের টিম নিজ হাতে review করে।'],
                ['num' => '03', 'title' => 'Student-focused Strategy', 'desc' => 'University/program পছন্দ আপনার profile ও goals বিবেচনায় করা হয়।'],
                ['num' => '04', 'title' => 'Long-term Mentorship', 'desc' => 'Elite students-দের জন্য application-এর বাইরেও structured support দেওয়া হয়।'],
            ],

            // ===== More Than an Application =====
            'sic_diff_title' => 'More Than an Application Service',
            'sic_diff_intro' => 'Admission পাওয়াই শেষ নয়। China যাওয়ার আগে এবং China journey-র শুরুতেও সঠিক guidance দরকার।',
            'sic_diff_rows' => [
                ['title' => 'Application Support', 'desc' => 'University + application guidance'],
                ['title' => 'Chinese Language', 'desc' => 'বাংলায় Chinese language training'],
                ['title' => 'Academic Guidance', 'desc' => 'Study ও student-life preparation'],
                ['title' => 'Career Direction', 'desc' => 'Long-term academic ও career support'],
                ['title' => '1-Year Mentorship', 'desc' => 'Structured ongoing guidance'],
            ],
            'sic_flagship_badge' => 'Flagship',
            'sic_flagship_title' => '1-Year Mentorship Program',
            'sic_flagship_body_1' => 'বাংলাদেশি students-দের জন্য designed একটি long-term mentorship journey — যেখানে admission support-এর সাথে Chinese language, academic guidance এবং career direction একসাথে থাকে।',
            'sic_flagship_body_2' => 'একটি application শেষ করাই লক্ষ্য নয় — একজন শিক্ষার্থীকে China journey-র জন্য প্রস্তুত করাই লক্ষ্য।',
            'sic_flagship_cta_text' => 'Explore Elite Success Program',

            // ===== Which service is right =====
            'sic_decision_eyebrow' => 'Decision Helper',
            'sic_decision_title' => 'আপনার জন্য কোনটি?',
            'sic_decision_quotes' => [
                ['quote' => '“আমি নিজে application handle করতে চাই, কিন্তু expert direction দরকার।”'],
                ['quote' => '“আমি পুরো application process-এ professional support চাই।”'],
                ['quote' => '“আমি application + Chinese language + long-term academic/career mentorship চাই।”'],
            ],
            'sic_decision_note' => 'নিশ্চিত না? কোনো চিন্তা নেই — আমাদের টিম আপনার profile review করে সঠিক option বুঝতে সাহায্য করবে।',

            // ===== Human support =====
            'sic_human_eyebrow' => 'Human-led Process',
            'sic_human_title' => 'Your Application Is Reviewed By Our Team',
            'sic_human_intro' => 'আমরা automated application system নই। আপনার academic background, goals এবং application situation বুঝে আমাদের টিম next steps নিয়ে আপনার সাথে আলোচনা করে।',
            'sic_human_steps' => [
                ['title' => 'Submit Information', 'desc' => 'আপনার তথ্য জমা দিন'],
                ['title' => 'Team Reviews', 'desc' => 'টিম আপনার profile review করে'],
                ['title' => 'Consultation', 'desc' => 'goals ও options নিয়ে আলোচনা'],
                ['title' => 'Recommended Path', 'desc' => 'উপযুক্ত pathway recommend'],
                ['title' => 'Application Support', 'desc' => 'নির্বাচিত service অনুযায়ী support'],
            ],

            // ===== Next steps =====
            'sic_next_steps_eyebrow' => 'Next Steps',
            'sic_next_steps_title' => 'Consultation-এর পর কী হবে?',
            'sic_next_steps_intro' => 'কোনো বাধ্যবাধকতা নেই — আমরা প্রথমে আপনার situation বুঝে, তারপর উপযুক্ত দিকনির্দেশনা দিই।',
            'sic_next_steps_list' => [
                ['text' => 'আপনি consultation / eligibility form জমা দেন।'],
                ['text' => 'আমাদের টিম আপনার তথ্য review করে।'],
                ['text' => 'একজন টিম সদস্য আপনার সাথে যোগাযোগ করে।'],
                ['text' => 'আপনার academic background, goals ও preferred program নিয়ে আলোচনা হয়।'],
                ['text' => 'আমরা উপযুক্ত service ও পরবর্তী steps ব্যাখ্যা করি।'],
            ],
            'sic_next_steps_note' => 'আমাদের টিম আপনার তথ্য review করে পরবর্তী ধাপ সম্পর্কে যোগাযোগ করবে।',

            // ===== FAQ =====
            'sic_faq_eyebrow' => 'FAQ',
            'sic_faq_title' => 'সাধারণ প্রশ্নাবলি',
            'sic_faq_items' => [
                ['question' => 'China-তে কোন কোন program-এ apply করা যায়?', 'answer' => 'Bachelor’s, Master’s, PhD, Chinese Language program এবং কিছু ক্ষেত্রে Diploma-সহ বিভিন্ন program-এ আবেদন করা যায়। কোনটি আপনার জন্য সম্ভব, তা university এবং আপনার academic background-এর উপর নির্ভর করে।'],
                ['question' => 'IELTS বা English প্রমাণ কি বাধ্যতামূলক?', 'answer' => 'প্রতিটি university ও program-এর নিজস্ব language requirement থাকে। কিছুতে English medium-এ পড়া যায়, কিছুতে Chinese/HSK প্রয়োজন হয়। আপনার program অনুযায়ী requirement আলাদা হতে পারে।'],
                ['question' => 'HSK কি দরকার?', 'answer' => 'Chinese medium program-এ সাধারণত HSK দরকার হয়, আবার English medium program-এ নাও লাগতে পারে। কোন program-এ কী লাগবে, তা review করে জানানো হয়।'],
                ['question' => 'আমি HSC পাস করে Bachelor’s করতে পারব?', 'answer' => 'হ্যাঁ, HSC বা equivalent শিক্ষার্থীরা সাধারণত Bachelor’s program-এ আবেদন করতে পারেন। তবে যোগ্যতা নির্দিষ্ট university-র requirements-এর উপর নির্ভর করে।'],
                ['question' => 'Master’s-এর জন্য কী কী লাগে?', 'answer' => 'সাধারণত Bachelor’s degree এবং প্রাসঙ্গিক documents লাগে। Language ও অন্যান্য requirements university অনুযায়ী ভিন্ন হতে পারে।'],
                ['question' => 'Scholarship পাওয়ার সুযোগ আছে?', 'answer' => 'University, government ও provincial level-এ বিভিন্ন scholarship-এর সুযোগ থাকতে পারে। তবে এগুলো guaranteed নয় — availability ও eligibility university, program ও intake অনুযায়ী ভিন্ন।'],
                ['question' => 'আমি কি নিজের university বেছে নিতে পারি?', 'answer' => 'আপনার পছন্দকে গুরুত্ব দেওয়া হয়। তবে কোন university আপনার profile ও goals-এর সাথে মানানসই, তা review করে আপনাকে understand করতে সাহায্য করা হয়।'],
                ['question' => 'Banglay Chinese কি application করে দেয়?', 'answer' => 'আপনার নেওয়া service-এর উপর নির্ভর করে। Guided Application-এ আপনি নিজে করেন, Full ও Elite-তে আমাদের টিম application process-এ support করে।'],
                ['question' => 'Guided Application মানে কী?', 'answer' => 'এটি তাদের জন্য, যারা নিজে application করতে চান কিন্তু expert direction ও strategy support চান। Application নিজেই করবেন, দিকনির্দেশনা পাবেন আমাদের কাছ থেকে।'],
                ['question' => 'Full Application Service-এ কী কী পাব?', 'answer' => 'University/program guidance, document ও application support, admission process support, pre-departure preparation — application-এর মূল ধাপগুলোতে team-এর hands-on support।'],
                ['question' => 'Elite Success Program কী?', 'answer' => 'এটি একটি premium 1-Year Mentorship Program — যেখানে admission support-এর সাথে Chinese language training, academic guidance ও career-oriented mentorship একসাথে থাকে।'],
                ['question' => '1-Year Mentorship-এর মধ্যে কী থাকে?', 'answer' => 'Elite program-এ admission support-সহ Chinese language, academic ও career guidance এবং ongoing mentor support — application-এর বাইরে দীর্ঘমেয়াদি structured support।'],
                ['question' => 'আপনারা কি Chinese language training দেন?', 'answer' => 'হ্যাঁ, Banglay Chinese বাংলায় Chinese language course ও training দিয়ে থাকে। Elite Success Program-এ language training-এর সুযোগও থাকে।'],
                ['question' => 'Admission / Scholarship / Visa কি guaranteed?', 'answer' => 'না। Admission, scholarship বা visa-এর কোনোটি guaranteed নয় — এগুলো university ও সংশ্লিষ্ট প্রতিষ্ঠানের সিদ্ধান্তের উপর নির্ভর করে। আমরা প্রক্রিয়াটিতে সঠিক guidance দেওয়ার চেষ্টা করি।'],
                ['question' => 'Consultation-এর পর কী হবে?', 'answer' => 'আপনার তথ্য জমা দিলে আমাদের টিম তা review করে পরবর্তী ধাপ সম্পর্কে আপনার সাথে যোগাযোগ করবে। কোনো বাধ্যবাধকতা নেই।'],
            ],

            // ===== Final CTA =====
            'sic_cta_title' => 'Ready to Explore Your China Study Options?',
            'sic_cta_body' => 'আপনার academic background, goals এবং preferred program সম্পর্কে আমাদের জানান। আমাদের টিম আপনার জন্য সম্ভাব্য pathway বুঝতে সাহায্য করবে।',
            'sic_cta_btn1_text' => 'Get Eligibility Review',
            'sic_cta_btn2_text' => 'Talk to Our Team',
            'sic_cta_note' => 'অথবা WhatsApp-এ সরাসরি মেসেজ করুন — এটি একটি secondary contact option।',

            // ===== Consultation intro =====
            'sic_form_eyebrow' => 'Eligibility Review',
            'sic_form_title' => 'আপনার তথ্য জমা দিন',
            'sic_form_intro' => 'কোনো বাধ্যবাধকতা নেই। আমাদের টিম আপনার profile review করে পরবর্তী ধাপ সম্পর্কে যোগাযোগ করবে।',
        ];
    }
}
