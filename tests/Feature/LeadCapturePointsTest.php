<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadCapturePointsTest extends TestCase
{
    use RefreshDatabase;

    protected function course(): Course
    {
        return Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'price' => 9500,
            'is_published' => true,
        ]);
    }

    protected function product(): Product
    {
        return Product::create([
            'title' => 'HSK 1 Practice Tests',
            'slug' => 'hsk-1-practice-tests',
            'price' => 299,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Practice Tests',
            'is_published' => true,
        ]);
    }

    public function test_contact_form_submission_shows_success_and_captures_a_lead(): void
    {
        $response = $this->post(route('contact.send'), [
            'name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'email' => 'rahim@example.com',
            'topic' => 'Study in China Consultancy',
            'message' => 'Please send me the course details.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'rahim@example.com',
            'topic' => 'Study in China Consultancy',
        ]);
        $this->assertDatabaseHas('leads', [
            'email' => 'rahim@example.com',
            'name' => 'Rahim Uddin',
            'whatsapp_number' => '01712345678',
            'source' => Lead::SOURCE_CONTACT_FORM,
            'interest' => Lead::INTEREST_STUDY_IN_CHINA,
        ]);
    }

    public function test_contact_form_requires_an_email_address(): void
    {
        $response = $this->post(route('contact.send'), [
            'name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'topic' => 'General Inquiry',
            'message' => 'Please call me back.',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_homepage_consultation_form_redirects_back_to_the_form_with_success(): void
    {
        $response = $this->post(route('contact.lead'), [
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'phone' => '+8801712345678',
            'service' => 'Courses',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertStringEndsWith('#contact', $response->headers->get('Location'));

        $this->assertDatabaseHas('leads', [
            'email' => 'rahim@example.com',
            'source' => Lead::SOURCE_CONTACT_FORM,
            'interest' => Lead::INTEREST_COURSES,
            'notes' => 'Interested in: Courses',
        ]);
    }

    public function test_homepage_form_accepts_whatsapp_only_visitors(): void
    {
        $response = $this->post(route('contact.lead'), [
            'name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'service' => 'Study in China',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Rahim Uddin',
            'email' => null,
        ]);
        $this->assertDatabaseHas('leads', [
            'email' => null,
            'whatsapp_number' => '01712345678',
            'source' => Lead::SOURCE_CONTACT_FORM,
            'interest' => Lead::INTEREST_STUDY_IN_CHINA,
        ]);
    }

    public function test_homepage_form_rejects_an_invalid_bangladeshi_phone(): void
    {
        $this->post(route('contact.lead'), [
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'phone' => '12345',
            'service' => 'Courses',
        ])->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_registration_captures_a_lead_linked_to_the_new_user(): void
    {
        $this->post(route('register'), [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'phone' => '+8801712345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard.index'));

        $user = User::where('email', 'student@example.com')->firstOrFail();

        $this->assertDatabaseHas('leads', [
            'user_id' => $user->id,
            'email' => $user->email,
            'source' => Lead::SOURCE_REGISTRATION,
            'interest' => Lead::INTEREST_GENERAL,
        ]);
    }

    public function test_course_enrollment_captures_a_checkout_lead(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'phone' => '01712345678',
        ]);
        $course = $this->course();

        $this->actingAs($student)
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'bkash',
                'transaction_id' => 'ABC123XYZ9',
                'sender_number' => '01712345678',
            ])->assertRedirect(route('checkout.confirmation'));

        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $student->id]);
        $this->assertDatabaseHas('leads', [
            'user_id' => $student->id,
            'email' => $student->email,
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => Lead::INTEREST_COURSES,
            'notes' => 'Course: HSK 1 Crash Course',
        ]);
    }

    public function test_digital_product_checkout_captures_a_checkout_lead(): void
    {
        $product = $this->product();

        $this->post(route('shop.checkout.store', $product->slug), [
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => '9JQ2A3B4C5',
        ])->assertRedirect(route('shop.thank-you'));

        $this->assertDatabaseHas('leads', [
            'email' => 'rahim@example.com',
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => Lead::INTEREST_DIGITAL_PRODUCTS,
            'notes' => 'Product: HSK 1 Practice Tests',
        ]);
    }

    public function test_scholarship_application_captures_a_lead(): void
    {
        $this->post('/study-in-china/apply', [
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'phone' => '+8801712345678',
            'highest_qualification' => 'HSC',
            'desired_program' => 'BSc Computer Science',
            'target_intake' => 'September 2027',
            'statement_of_purpose' => 'I want to study Computer Science in China and eventually work in the tech industry back home.',
        ])->assertRedirect(route('study-in-china.consultation'));

        $this->assertDatabaseHas('leads', [
            'email' => 'rahim@example.com',
            'source' => Lead::SOURCE_APPLICATION,
            'interest' => Lead::INTEREST_STUDY_IN_CHINA,
            'notes' => 'Desired program: BSc Computer Science',
        ]);
    }
}
