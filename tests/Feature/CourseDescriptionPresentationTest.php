<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The public course page presents descriptions generically for any Filament
 * course: RichEditor HTML keeps its structure (headings, lists, emphasis and
 * line breaks) verbatim, plain-text descriptions are escaped and line-broken,
 * and the enrollment panel precedes the description in the DOM so it renders
 * above it on mobile.
 */
class CourseDescriptionPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_plain_text_description_is_escaped_and_line_breaks_are_preserved(): void
    {
        $course = $this->publishedCourse([
            'description' => "প্রথম অনুচ্ছেদের লেখা।\nদ্বিতীয় লাইনের লেখা।",
        ]);

        $response = $this->get(route('courses.show', $course->slug));

        $response->assertSee('প্রথম অনুচ্ছেদের লেখা।');
        $response->assertSee('দ্বিতীয় লাইনের লেখা।');
        $response->assertSee('<br', false);
    }

    public function test_plain_text_description_never_renders_markup_as_html(): void
    {
        $course = $this->publishedCourse([
            'description' => "<script>alert('x')</script>\nসাধারণ লেখা।",
        ]);

        $this->get(route('courses.show', $course->slug))
            ->assertDontSee('<script>', false)
            ->assertSee('&lt;script&gt;', false)
            ->assertSee('সাধারণ লেখা।');
    }

    public function test_rich_text_description_keeps_headings_lists_emphasis_and_line_breaks_verbatim(): void
    {
        $course = $this->publishedCourse([
            'description' => '<h3>আমার কোর্সের শিরোনাম</h3>'
                .'<p>একটি <strong>গুরুত্বপূর্ণ</strong> এবং <em>জোরালো</em> বাক্য।</p>'
                .'<ol><li>প্রথম ধাপ</li><li>দ্বিতীয় ধাপ</li></ol>'
                .'<p>লাইন এক<br>লাইন দুই</p>',
        ]);

        $response = $this->get(route('courses.show', $course->slug));

        // Wording unchanged…
        $response->assertSee('আমার কোর্সের শিরোনাম');
        $response->assertSee('একটি গুরুত্বপূর্ণ এবং জোরালো বাক্য।');
        $response->assertSee('প্রথম ধাপ');
        $response->assertSee('দ্বিতীয় ধাপ');
        // …and the structure passes through as authored.
        $response->assertSee('<h3>আমার কোর্সের শিরোনাম</h3>', false);
        $response->assertSee('<strong>গুরুত্বপূর্ণ</strong>', false);
        $response->assertSee('<em>জোরালো</em>', false);
        $response->assertSee('<ol><li>প্রথম ধাপ</li><li>দ্বিতীয় ধাপ</li></ol>', false);
        $response->assertSee('<p>লাইন এক<br>লাইন দুই</p>', false);
    }

    public function test_enrollment_panel_precedes_the_description_in_the_dom_for_mobile_ordering(): void
    {
        $course = $this->publishedCourse([
            'description' => '<p>কোর্সের বিবরণ শুরু।</p>',
        ]);

        $this->get(route('courses.show', $course->slug))
            ->assertSeeInOrder([
                '<h2 class="text-lg font-extrabold text-slate-900">এনরোল করুন</h2>',
                '<div class="course-content">',
            ], false);
    }

    protected function publishedCourse(array $overrides = []): Course
    {
        return Course::create(array_merge([
            'title' => 'Course '.Str::random(3),
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => 5000,
            'is_published' => true,
        ], $overrides));
    }
}
