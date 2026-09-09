<?php

namespace Tests\Feature;

use App\Filament\Resources\Enrollments\Pages\CreateEnrollment;
use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EnrollmentsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function course(): Course
    {
        return Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'price' => 9500,
            'is_published' => true,
        ]);
    }

    protected function enrollment(Course $course, array $overrides = []): Enrollment
    {
        $user = User::factory()->create();

        return Enrollment::create(array_merge([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 9500,
            'amount_paid' => 0,
            'amount_due' => 9500,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ], $overrides));
    }

    public function test_admin_can_open_enrollment_list_and_create_page(): void
    {
        $admin = $this->admin();
        $course = $this->course();
        $this->enrollment($course);

        $this->actingAs($admin)->get(ListEnrollments::getUrl())->assertOk();
        $this->actingAs($admin)->get(CreateEnrollment::getUrl())->assertOk();
    }

    public function test_admin_can_create_a_course_enrollment_through_the_form(): void
    {
        $course = $this->course();
        $user = User::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
                'student_name' => 'Karim Ahmed',
                'student_email' => 'karim@example.com',
                'student_phone' => '01812345678',
                'amount' => 9500,
                'amount_paid' => 5000,
                'amount_due' => 4500,
                'payment_method' => 'bkash',
                'payment_status' => 'partially_paid',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => 'Karim Ahmed',
            'student_email' => 'karim@example.com',
            'amount_paid' => 5000,
            'amount_due' => 4500,
            'payment_method' => 'bkash',
            'payment_status' => 'partially_paid',
            'enrollment_status' => 'pending',
        ]);
    }

    public function test_mark_as_paid_action_unlocks_the_course(): void
    {
        $enrollment = $this->enrollment($this->course());

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('markPaid', $enrollment);

        $enrollment->refresh();

        $this->assertSame('paid', $enrollment->payment_status);
        $this->assertSame('in_progress', $enrollment->enrollment_status);
        $this->assertSame('9500.00', $enrollment->amount_paid);
        $this->assertSame('0.00', $enrollment->amount_due);
        $this->assertNotNull($enrollment->paid_at);
    }

    public function test_bulk_mark_completed_completes_selected_enrollments(): void
    {
        $course = $this->course();
        $first = $this->enrollment($course, ['payment_status' => 'paid', 'enrollment_status' => 'in_progress']);
        $second = $this->enrollment($course, ['payment_status' => 'paid', 'enrollment_status' => 'in_progress']);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableBulkAction('bulkMarkCompleted', [$first->id, $second->id]);

        $this->assertSame('completed', $first->fresh()->enrollment_status);
        $this->assertSame('completed', $second->fresh()->enrollment_status);
    }
}
