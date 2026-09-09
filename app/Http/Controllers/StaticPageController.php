<?php

namespace App\Http\Controllers;

use App\Support\LegalPagesContent;
use Illuminate\Support\Str;

class StaticPageController extends Controller
{
    /**
     * Public helper pages (Terms, Privacy, Refund, FAQ).
     *
     * Copy is intentionally concise placeholder wording — replace it here in one
     * place once final policy text has been reviewed.
     *
     * @return array<string, array{title: string, meta_title: string, meta_description: string, updated_at: string, intro: string, sections: array<int, array{heading: string, body: array<int, string>}>}>
     */
    protected function pages(): array
    {
        return [
            'terms-and-conditions' => [
                'title' => 'Terms and Conditions',
                'meta_title' => 'Terms and Conditions | Banglay Chinese',
                'meta_description' => 'The terms that apply when you enroll in a Banglay Chinese course or use our study-in-China services.',
                'updated_at' => 'January 2026',
                'intro' => 'Welcome to Banglay Chinese. By enrolling in any course, booking a consultation, or using this website you agree to the following terms. Please read them carefully.',
                'sections' => [
                    [
                        'heading' => '1. About our services',
                        'body' => [
                            'Banglay Chinese provides online Chinese language courses, HSK preparation, and study-in-China guidance for Bengali-speaking students.',
                            'Details of each course (price, duration, live classes, and materials) are shown on the course page before you enroll.',
                        ],
                    ],
                    [
                        'heading' => '2. Enrollment and payment',
                        'body' => [
                            'Enrollment is confirmed only after payment is received in full via one of the payment methods shown at checkout (bKash, Nagad, or bank transfer).',
                            'You are responsible for providing a correct email address and phone number — course access and updates are sent to those details.',
                        ],
                    ],
                    [
                        'heading' => '3. Course access',
                        'body' => [
                            'Course access is personal and must not be shared, resold, or redistributed.',
                            'Access remains available for the period stated on the course page; we may close old courses when a batch is completed, with advance notice.',
                        ],
                    ],
                    [
                        'heading' => '4. Acceptable use',
                        'body' => [
                            'You agree to use the website and course materials only for lawful personal study.',
                            'You must not copy, record, or republish our lessons or materials without written permission.',
                        ],
                    ],
                    [
                        'heading' => '5. Limitation of liability',
                        'body' => [
                            'Study-in-China information (visa rules, scholarship deadlines, university requirements) changes frequently. We update content in good faith but do not guarantee outcomes such as admission or scholarship awards.',
                            'To the maximum extent permitted by law, Banglay Chinese is not liable for indirect or consequential losses arising from use of the website or services.',
                        ],
                    ],
                    [
                        'heading' => '6. Changes to these terms',
                        'body' => [
                            'We may update these terms from time to time. The latest version will always be published on this page with the date shown below.',
                        ],
                    ],
                ],
            ],

            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'meta_title' => 'Privacy Policy | Banglay Chinese',
                'meta_description' => 'How Banglay Chinese collects, uses, and protects your personal information.',
                'updated_at' => 'January 2026',
                'intro' => 'Your privacy matters to us. This policy explains what information we collect, why we collect it, and how it is used and protected.',
                'sections' => [
                    [
                        'heading' => '1. Information we collect',
                        'body' => [
                            'When you enroll, contact us, or request guidance we collect the details you provide — name, email address, phone number, and any message content.',
                            'We may also collect basic technical data (browser type, pages visited) through analytics and advertising cookies.',
                        ],
                    ],
                    [
                        'heading' => '2. How we use your information',
                        'body' => [
                            'To deliver courses and services you paid for, send class updates, and respond to your enquiries.',
                            'To send study-in-China guidance only when you have opted into our email list — you can unsubscribe at any time.',
                            'To improve our website and measure the performance of our advertising.',
                        ],
                    ],
                    [
                        'heading' => '3. Sharing your information',
                        'body' => [
                            'We never sell your personal data. Information is shared only with service providers who help us operate (payment processing, email delivery) and only as necessary to provide the service.',
                            'We may disclose information where required by law or to protect the rights and safety of our students and staff.',
                        ],
                    ],
                    [
                        'heading' => '4. Data retention and security',
                        'body' => [
                            'We keep your information only as long as needed for the purposes above, after which it is deleted or anonymised.',
                            'Reasonable technical and organisational measures are in place to protect your data against loss or misuse.',
                        ],
                    ],
                    [
                        'heading' => '5. Your rights',
                        'body' => [
                            'You may ask us for a copy of the personal data we hold about you, or ask us to correct or delete it, by contacting us at the email address in the footer of this website.',
                        ],
                    ],
                    [
                        'heading' => '6. Contact',
                        'body' => [
                            'If you have any question about this policy, please contact us through the Contact page or by email.',
                        ],
                    ],
                ],
            ],

            'refund-and-returns-policy' => [
                'title' => 'Refund and Returns Policy',
                'meta_title' => 'Refund and Returns Policy | Banglay Chinese',
                'meta_description' => 'Our refund and cancellation policy for online courses and study-in-China services.',
                'updated_at' => 'January 2026',
                'intro' => 'We want every student to be happy with their course. Because digital course access is delivered instantly, our refund policy balances that with fairness for both sides.',
                'sections' => [
                    [
                        'heading' => '1. 7-day cooling-off refund',
                        'body' => [
                            'If you change your mind, you can request a full refund within 7 days of enrollment, provided you have not downloaded or completed more than the first introductory lesson.',
                        ],
                    ],
                    [
                        'heading' => '2. Course transfer',
                        'body' => [
                            'Instead of a refund, you may transfer your enrollment to another upcoming batch or another course of equal value at any time before the course starts — just message us.',
                        ],
                    ],
                    [
                        'heading' => '3. Non-refundable items',
                        'body' => [
                            'Consultation and mentorship bookings that have already taken place are non-refundable.',
                            'After the 7-day period, refunds for partially completed courses are not available, but the course-transfer option above still applies.',
                        ],
                    ],
                    [
                        'heading' => '4. How to request a refund',
                        'body' => [
                            'Email us from the address used at enrollment, or message us on WhatsApp, with your name, course, and payment reference.',
                            'Approved refunds are returned to the same payment method used (bKash, Nagad, or bank transfer) within 5–10 working days.',
                        ],
                    ],
                    [
                        'heading' => '5. Service cancellation by us',
                        'body' => [
                            'In the rare case we cancel a course before it starts, you receive a full refund or a free transfer to another batch.',
                        ],
                    ],
                ],
            ],

            'faq' => [
                'title' => 'Frequently Asked Questions',
                'meta_title' => 'FAQ | Banglay Chinese',
                'meta_description' => 'Answers to common questions about Banglay Chinese courses, payments, and studying in China.',
                'updated_at' => 'January 2026',
                'intro' => 'Quick answers to the questions we hear most often. If yours is not here, contact us — we reply fast.',
                'sections' => [
                    [
                        'heading' => 'How do I pay for a course?',
                        'body' => [
                            'You can pay with bKash, Nagad, or bank transfer. After enrolling you will see the exact payment details on the checkout page.',
                        ],
                    ],
                    [
                        'heading' => 'When do classes run?',
                        'body' => [
                            'Live classes are scheduled in the evening (Bangladesh time) so students and job-holders can attend. Recordings are available if you miss a session.',
                        ],
                    ],
                    [
                        'heading' => 'Do I need any experience to join HSK 1?',
                        'body' => [
                            'No. HSK 1 starts from zero — pinyin, tones, and the first characters. We teach everything in Bangla.',
                        ],
                    ],
                    [
                        'heading' => 'Can you help me get a scholarship in China?',
                        'body' => [
                            'Yes. Our study-in-China program includes university selection, application review, document guidance, and scholarship mentorship — all explained step by step in Bangla.',
                        ],
                    ],
                    [
                        'heading' => 'What if I miss a live class?',
                        'body' => [
                            'You get access to the recording and the lesson notes, and you can ask questions in the class group so you never fall behind.',
                        ],
                    ],
                    [
                        'heading' => 'Is there a refund policy?',
                        'body' => [
                            'Yes — a 7-day cooling-off refund applies from enrollment. See the Refund and Returns Policy page for full details.',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * The untouched default content for a helper page (single source of truth
     * shared with the CMS editors).
     *
     * @return array<string, array{title: string, meta_title: string, meta_description: string, updated_at: string, intro: string, sections: array<int, array{heading: string, body: array<int, string>}>}>|null
     */
    public static function defaultContent(string $page): ?array
    {
        return (new static)->pages()[$page] ?? null;
    }

    public function show(string $page)
    {
        $page = Str::slug($page);
        $defaults = static::defaultContent($page);

        if ($defaults === null) {
            abort(404);
        }

        // Admin overrides (settings-backed, edited in Filament → Content
        // Management) are applied on top of the canonical default copy.
        $data = LegalPagesContent::resolve($page, $defaults);

        return view($page === 'faq' ? 'pages.faq' : 'pages.static-page', $data);
    }
}
