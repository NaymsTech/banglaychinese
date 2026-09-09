<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function course(array $overrides = []): Course
    {
        return Course::create(array_merge([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'price' => 0,
            'is_published' => true,
        ], $overrides));
    }

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'phone' => '01712345678',
        ]);
    }

    public function test_free_course_enrollment_does_not_require_payment_details(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'free',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'payment_method' => 'free',
            'transaction_id' => null,
            'sender_number' => null,
        ]);

        $this->assertDatabaseHas('leads', [
            'email' => $student->email,
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => Lead::INTEREST_COURSES,
        ]);
    }

    public function test_paid_enrollment_still_requires_transaction_and_sender(): void
    {
        $course = $this->course(['price' => 9500]);

        $this->actingAs($this->student())
            ->from(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'bkash',
                'transaction_id' => '',
                'sender_number' => '',
            ])
            ->assertRedirect(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->assertSessionHasErrors(['transaction_id', 'sender_number']);

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_checkout_page_renders_server_side_validation_errors(): void
    {
        $this->followingRedirects();

        $course = $this->course(['price' => 9500]);

        $this->actingAs($this->student())
            ->from(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'bkash',
                'transaction_id' => 'short',
                'sender_number' => '123',
            ])
            ->assertOk()
            ->assertSee('ট্রানজেকশন আইডি কমপক্ষে ৮ অক্ষরের হতে হবে।');
    }
}
