<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\Category;
use App\Models\Course;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Lead;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Stage 6B: free courses activate immediately (zero-price enrollment with no
 * payment claim), and `payment_method=free` can never enroll a paid course.
 */
class FreeCourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_enroll_in_a_published_free_course_and_is_active_immediately(): void
    {
        $student = $this->student();
        $course = $this->course('0.00');

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'free',
            ])
            ->assertRedirect()
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        // No transaction ID / sender number were required.
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'student_email' => $student->email,
            'amount' => 0,
            'amount_paid' => 0,
            'amount_due' => 0,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'status' => 'active',
            'payment_method' => 'free',
            'transaction_id' => null,
            'sender_number' => null,
            'paid_at' => null,
        ]);
    }

    public function test_free_enrollment_creates_no_payment_claim_and_an_in_progress_canonical_order(): void
    {
        $student = $this->student();
        $course = $this->course(0);

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), ['payment_method' => 'free'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 0);

        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();
        $this->assertSame('in_progress', $order->order_status);
        $this->assertSame('0.00', $order->total_amount);
        $this->assertSame(0.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PAID, $order->paymentState());

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'unit_price' => 0,
        ]);
    }

    public function test_free_course_student_can_access_lessons_immediately(): void
    {
        $student = $this->student();
        [$course, $lesson] = $this->courseWithLesson();

        // Not enrolled → no access.
        $this->actingAs($student)
            ->get(route('dashboard.lessons.show', ['course' => $course->slug, 'lesson' => $lesson->slug]))
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), ['payment_method' => 'free'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($student)
            ->get(route('dashboard.lessons.show', ['course' => $course->slug, 'lesson' => $lesson->slug]))
            ->assertOk();
    }

    public function test_repeated_free_enrollment_does_not_create_duplicates(): void
    {
        $student = $this->student();
        $course = $this->course(0);

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), ['payment_method' => 'free'])
            ->assertSessionHasNoErrors();

        // Second attempt keeps the existing friendly response and mutates nothing.
        $this->actingAs($student)
            ->from(route('courses.show', $course->slug))
            ->post(route('courses.enroll', $course->slug), ['payment_method' => 'free'])
            ->assertRedirect(route('courses.show', $course->slug))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_free_enrollment_sends_one_confirmation_email_and_no_payment_emails(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        EmailTemplate::factory()->create([
            'key' => 'course_enrollment_confirmation',
            'subject' => 'Welcome to {course_title}!',
            'body' => '<p>Hi {student_name}, welcome to {course_title}.</p>',
            'variables' => ['student_name', 'course_title'],
            'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
        ]);

        $student = $this->student();
        $course = $this->course(0);

        Queue::fake([SendEmailJob::class]);

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), ['payment_method' => 'free'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        Queue::assertPushed(SendEmailJob::class, 1);

        // Only the free confirmation; no order-received/payment-pending email.
        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'course_enrollment_confirmation',
            'recipient_email' => $student->email,
        ]);
        $this->assertNotNull(Enrollment::firstOrFail()->confirmation_email_sent_at);
        $this->assertNull(Enrollment::firstOrFail()->order_received_email_sent_at);
    }

    public function test_paid_course_with_free_payment_method_is_rejected_without_creating_records(): void
    {
        $student = $this->student();
        $course = $this->course(9500);

        $this->actingAs($student)
            ->from(route('courses.show', $course->slug))
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'free',
                'transaction_id' => 'FAKEFREE123',
                'sender_number' => '01712345678',
            ])
            ->assertRedirect(route('courses.show', $course->slug))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('email_logs', 0);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_unpublished_free_course_cannot_be_enrolled_through_the_public_route(): void
    {
        $student = $this->student();
        $course = $this->course(0, ['is_published' => false]);

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), ['payment_method' => 'free'])
            ->assertNotFound();

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_paid_course_page_offers_the_paid_checkout_not_the_free_form(): void
    {
        $course = $this->course(9500);

        $response = $this->actingAs($this->student())
            ->get(route('courses.show', $course->slug));

        $response->assertOk();
        $response->assertSee(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]), false);
        $response->assertDontSee('Enroll for Free');
        $response->assertDontSee('payment_method" value="free"', false);
    }

    public function test_free_enrollment_still_captures_a_checkout_lead(): void
    {
        $student = $this->student();
        $course = $this->course(0);

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), ['payment_method' => 'free'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leads', [
            'email' => $student->email,
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => Lead::INTEREST_COURSES,
            'notes' => 'Course: '.$course->title,
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
            'phone' => '01712345678',
        ]);
    }

    protected function course(int|string $price, array $overrides = []): Course
    {
        return Course::create(array_merge([
            'title' => 'Course '.Str::random(3),
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => $price,
            'is_published' => true,
        ], $overrides));
    }

    /**
     * @return array{0: Course, 1: Lesson}
     */
    protected function courseWithLesson(): array
    {
        $category = Category::create(['name' => 'HSK', 'slug' => 'hsk-'.Str::lower(Str::random(4))]);
        $course = $this->course(0, ['category_id' => $category->id]);

        /** @var Module $module */
        $module = $course->modules()->create(['title' => 'Module 1', 'order' => 1]);
        $lesson = $module->lessons()->create([
            'title' => 'Lesson 1',
            'slug' => 'lesson-1-'.Str::lower(Str::random(6)),
            'content' => '<p>Free lesson content</p>',
            'order' => 1,
            'is_free_preview' => false,
        ]);

        return [$course, $lesson];
    }
}
