<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Database\Seeders\BanglayChineseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Chinese Speaking Mastery course page shows the owner-supplied description
 * verbatim (headings, intro, sections, bullets and fee) and keeps the paid
 * enrollment CTA for this ৳16,000 course.
 */
class ChineseSpeakingMasteryContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_renders_the_full_owner_description_verbatim(): void
    {
        $this->seed(BanglayChineseSeeder::class);

        $course = Course::where('slug', 'chinese-speaking-mastery')->firstOrFail();
        $response = $this->get(route('courses.show', $course->slug));

        // Main heading + every section heading.
        $response->assertSee('আত্মবিশ্বাসের সঙ্গে সাবলীল চাইনিজ বলুন');
        $response->assertSee('এই প্রোগ্রাম যাদের জন্য');
        $response->assertSee('আপনি যা শিখবেন');
        $response->assertSee('প্রোগ্রামের কাঠামো');
        $response->assertSee('কেন Banglay Chinese');
        $response->assertSee('Program Fee');

        // Intro paragraphs (fragments either side of the owner's double spaces).
        $response->assertSee('Chinese Speaking Mastery প্রোগ্রামটি তৈরি করা হয়েছে সেই বাংলাদেশি শিক্ষার্থীদের জন্য যারা বাস্তব কথোপকথনে Chinese দক্ষতা গড়ে তুলতে চান। স্ট্রাকচার্ড লাইভ ক্লাস, ছোট ব্যাচ সিস্টেম এবং কার্যকর প্রগ্রেস ট্র্যাকিংয়ের মাধ্যমে আপনি পাবেন এক টেকসই ভাষা ভিত্তি ও ভবিষ্যতের HSK সাফল্যের রোডম্যাপ।');
        $response->assertSee('অধিকাংশ শিক্ষার্থী চাইনিজ শুনে বুঝতে');
        $response->assertSee('পারলেও কথা বলার সময় জড়তা অনুভব করে। Chinese Speaking Mastery হলো এমন একটি নিবিড় প্রশিক্ষণ যা আপনার উচ্চারণের ত্রুটি দূর করে আপনাকে সাবলীল ভাবে');
        $response->assertSee('কথা বলতে সাহায্য করবে। এটি কোনো সাধারণ কোর্স নয়, বরং আপনার Verbal Skills বৃদ্ধির একটি Professional Lab।');

        // "এই প্রোগ্রাম যাদের জন্য" bullets.
        $response->assertSee('HSK Learners:');
        $response->assertSee('যারা তাদের HSKK (Oral) পরীক্ষার স্কোর ইমপ্রুভ করতে চান।');
        $response->assertSee('Scholarship Aspirants:');
        $response->assertSee('যারা চীনা বিশ্ববিদ্যালয়ের Admission Interview-তে সেরা পারফরম্যান্স দিতে চান।');
        $response->assertSee('সবাই যারা চাইনিজ শেখাকে বিনিয়োগ হিসেবে দেখেন');
        $response->assertSee('যারা ভবিষ্যতে HSK পরীক্ষা ও বিশ্ববিদ্যালয় অ্যাডমিশনে আগ্রহী');
        $response->assertSee('যারা স্পষ্ট গাইডলাইন ও দায়িত্বশীল প্রশিক্ষণ চান');
        $response->assertSee('যারা নৈমিত্তিক লার্নিং নয়, ফলাফল চান');

        // "আপনি যা শিখবেন" bullets.
        $response->assertSee('Tone Correction: চাইনিজ ভাষার ৪টি Tones-এর নিখুঁত প্রয়োগ নিশ্চিত করা।');
        $response->assertSee('Spontaneous Response: অনুবাদ না করে সরাসরি চাইনিজ ভাষায় চিন্তা ও উত্তর দেওয়ার Natural Ability।');
        $response->assertSee('দৈনন্দিন কথোপকথনের জন্য core speaking skills');
        $response->assertSee('পিনইন (Pinyin) উচ্চারণ ও টোনের পারফেকশন');
        $response->assertSee('বাস্তব Chinese কমিউনিকেশনের আত্মবিশ্বাস');

        // "প্রোগ্রামের কাঠামো" bullets — the owner text says 4 মাস, not ৬ মাস.
        $response->assertSee('4 মাস (সপ্তাহে');
        $response->assertSee('২-৩ দিন)');
        $response->assertSee('সাপ্তাহিক speaking lab ও অ্যাসাইনমেন্ট');
        $response->assertSee('PDF মেটেরিয়াল, অডিও প্র্যাকটিস ও প্রগ্রেস ট্র্যাকিং সাপোর্ট');
        $response->assertSee('মক স্পিকিং অ্যাসেসমেন্ট ও ফিডব্যাক');
        $response->assertDontSee('৬ মাস');

        // "কেন Banglay Chinese" bullets and the fee.
        $response->assertSee('স্পষ্ট রোডম্যাপ: Speaking → HSK → Admission Success');
        $response->assertSee('বিশ্বস্ত গাইডেন্স ও প্রিমিয়াম সার্ভিস স্ট্যান্ডার্ড');
        $response->assertSee('মোট: ৳১৬, ০০০');

        // Structural markup is preserved (h2 main heading, h3 fee section).
        $response->assertSee('<h2>আত্মবিশ্বাসের সঙ্গে সাবলীল চাইনিজ বলুন</h2>', false);
        $response->assertSee('<h3>Program Fee</h3>', false);
    }

    public function test_seeded_paid_course_page_offers_the_paid_checkout_cta(): void
    {
        $this->seed(BanglayChineseSeeder::class);

        $course = Course::where('slug', 'chinese-speaking-mastery')->firstOrFail();

        $this->actingAs(User::factory()->create())
            ->get(route('courses.show', $course->slug))
            ->assertSee(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]), false)
            ->assertSee('Enroll Now – ৳16,000')
            ->assertDontSee('Enroll for Free')
            ->assertDontSee('value="free"', false);
    }
}
