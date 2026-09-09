<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RobotsMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_stay_indexable(): void
    {
        $course = Course::create([
            'title' => 'HSK 2 Track',
            'slug' => 'hsk-2-track',
            'description' => 'HSK 2 preparation.',
            'price' => 3000,
            'is_published' => true,
        ]);

        foreach (['/', '/courses', '/courses/'.$course->slug, '/blog', '/about', '/pages/faq'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('content="index, follow"', $html, 'expected indexable robots on '.$path);
            $this->assertStringNotContainsString('noindex', $html, 'expected no noindex on '.$path);
        }
    }

    public function test_checkout_page_is_noindexed(): void
    {
        $course = Course::create([
            'title' => 'HSK 3 Track',
            'slug' => 'hsk-3-track',
            'price' => 3000,
            'is_published' => true,
        ]);

        $html = $this->get('/checkout/course/'.$course->slug)->assertOk()->getContent();

        $this->assertStringContainsString('content="noindex, nofollow"', $html);
    }

    public function test_authenticated_dashboard_and_profile_pages_are_noindexed(): void
    {
        $user = $this->student();

        foreach ([route('dashboard.index'), route('profile.edit')] as $path) {
            $html = $this->actingAs($user)->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('content="noindex, nofollow"', $html);
        }
    }

    public function test_authentication_pages_are_noindexed(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('content="noindex, follow"', $html);

        $confirm = $this->actingAs($this->student())->get(route('password.confirm'))->assertOk()->getContent();

        $this->assertStringContainsString('content="noindex, nofollow"', $confirm);
    }

    public function test_order_confirmation_page_is_noindexed(): void
    {
        $course = Course::create([
            'title' => 'HSK 4 Track',
            'slug' => 'hsk-4-track',
            'price' => 4000,
            'is_published' => true,
        ]);

        $user = $this->student();

        // Build an enrollment the same way the checkout flow does.
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'student_phone' => '01712345678',
            'amount' => 4000,
            'amount_paid' => 0,
            'amount_due' => 4000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);

        $url = URL::temporarySignedRoute(
            'checkout.course.confirmation',
            now()->addMinutes(60),
            ['enrollment' => $enrollment->getKey()]
        );

        $html = $this->get($url)->assertOk()->getContent();

        $this->assertStringContainsString('content="noindex, nofollow"', $html);
    }

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
            'phone' => '01712345678',
        ]);
    }
}
