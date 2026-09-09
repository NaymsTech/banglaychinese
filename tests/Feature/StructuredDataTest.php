<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_course_page_contains_course_structured_data_from_real_data(): void
    {
        $course = Course::create([
            'title' => 'HSK 1 Foundation',
            'slug' => 'hsk-1-foundation',
            'description' => 'A complete HSK 1 course taught in Bengali.',
            'price' => 9500,
            'is_published' => true,
        ]);

        $html = $this->get('/courses/'.$course->slug)->assertOk()->getContent();

        $courseSchema = $this->schemaByType($html, 'Course');

        $this->assertNotNull($courseSchema);
        $this->assertSame('HSK 1 Foundation', $courseSchema['name']);
        $this->assertSame('A complete HSK 1 course taught in Bengali.', $courseSchema['description']);
        $this->assertSame(route('courses.show', ['slug' => $course->slug]), $courseSchema['url']);
        $this->assertSame('9500', $courseSchema['offers']['price']);
        $this->assertSame('BDT', $courseSchema['offers']['priceCurrency']);
        $this->assertSame('EducationalOrganization', $courseSchema['provider']['@type']);
        $this->assertSame('Banglay Chinese', $courseSchema['provider']['name']);
    }

    public function test_course_schema_never_emits_invented_fields(): void
    {
        $course = Course::create([
            'title' => 'Speaking Mastery',
            'slug' => 'speaking-mastery',
            'description' => 'Live speaking practice.',
            'price' => 0,
            'is_published' => true,
        ]);

        $html = $this->get('/courses/'.$course->slug)->assertOk()->getContent();

        $courseSchema = $this->schemaByType($html, 'Course');

        $this->assertNotNull($courseSchema);
        foreach (['image', 'aggregateRating', 'review', 'instructor', 'startDate', 'provider', 'hasCourseInstance'] as $missing) {
            if ($missing === 'provider') {
                // provider (site identity) is always present; nothing else is invented.
                $this->assertArrayHasKey('provider', $courseSchema);

                continue;
            }
            $this->assertArrayNotHasKey($missing, $courseSchema);
        }

        $this->assertSame('0', $courseSchema['offers']['price']);
    }

    public function test_faq_page_emits_faqpage_schema_matching_visible_content(): void
    {
        $html = $this->get('/pages/faq')->assertOk()->getContent();

        $faqSchema = $this->schemaByType($html, 'FAQPage');

        $this->assertNotNull($faqSchema);
        $this->assertNotEmpty($faqSchema['mainEntity']);

        $first = $faqSchema['mainEntity'][0];
        $this->assertSame('Question', $first['@type']);
        $this->assertSame('How do I pay for a course?', $first['name']);
        $this->assertSame('Answer', $first['acceptedAnswer']['@type']);
        $this->assertStringContainsString('bKash', $first['acceptedAnswer']['text']);
    }

    public function test_pages_without_visible_faq_content_emit_no_faqpage_schema(): void
    {
        foreach (['/', '/pages/terms-and-conditions', '/courses'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringNotContainsString('FAQPage', $html, 'FAQPage schema must not appear on '.$path);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function schemaByType(string $html, string $type): ?array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        foreach ($matches[1] as $raw) {
            $decoded = json_decode(trim($raw), true);

            if (is_array($decoded) && ($decoded['@type'] ?? null) === $type) {
                return $decoded;
            }
        }

        return null;
    }
}
