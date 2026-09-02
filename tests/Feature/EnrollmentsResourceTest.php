<?php

namespace Tests\Feature;

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

    protected function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    protected function course(): Course
    {
        return Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'price' => 9500,
        ]);
    }

    public function test_admin_can_view_enrollments_list(): void
    {
        $student = $this->student();
        $course = $this->course();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => 'TRX123',
            'sender_number' => '01700000000',
            'price_paid' => 9500,
        ]);

        $this->actingAs($this->admin())
            ->get(ListEnrollments::getUrl())
            ->assertOk()
            ->assertSee('TRX123')
            ->assertSee('bKash')
            ->assertSee('Pending');
    }

    public function test_admin_can_approve_a_pending_enrollment(): void
    {
        $enrollment = Enrollment::create([
            'user_id' => $this->student()->id,
            'course_id' => $this->course()->id,
            'status' => 'pending',
            'payment_method' => 'nagad',
            'transaction_id' => 'TRX-PENDING-1',
            'price_paid' => 9500,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('approve', $enrollment)
            ->assertNotified();

        $enrollment->refresh();

        $this->assertSame('active', $enrollment->status);
        $this->assertNotNull($enrollment->paid_at);
    }

    public function test_admin_can_reject_a_pending_enrollment_with_reason(): void
    {
        $enrollment = Enrollment::create([
            'user_id' => $this->student()->id,
            'course_id' => $this->course()->id,
            'status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => 'TRX-PENDING-2',
            'price_paid' => 9500,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('reject', $enrollment, data: [
                'reason' => 'Transaction not found on merchant account.',
            ])
            ->assertNotified();

        $enrollment->refresh();

        $this->assertSame('cancelled', $enrollment->status);
        $this->assertNull($enrollment->paid_at);
        $this->assertSame('Transaction not found on merchant account.', $enrollment->rejection_reason);
    }

    public function test_dashboard_widget_counts_and_revenue(): void
    {
        $student = $this->student();
        $course = $this->course();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'pending',
            'price_paid' => 9500,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'price_paid' => 1500,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('Pending Payments', $html);
        $this->assertStringContainsString('Approved This Month', $html);
        $this->assertStringContainsString('1,500.00', $html);
    }
}
