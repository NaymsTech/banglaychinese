<?php

namespace Tests\Feature;

use App\Filament\Resources\DigitalOrders\Pages\CreateDigitalOrder;
use App\Filament\Resources\Enrollments\Pages\CreateEnrollment;
use App\Filament\Resources\ServiceOrders\Pages\CreateServiceOrder;
use App\Models\Course;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Reactive user-selection autofill (switching users refreshes the snapshot)
 * and the course → total-amount autofill on the Enrollment admin form.
 */
class AdminFormAutofillTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // User switching — Enrollment
    // ------------------------------------------------------------------

    public function test_switching_the_selected_user_refreshes_the_buyer_snapshot(): void
    {
        $userA = $this->student(['name' => 'User A', 'email' => 'a@example.com', 'phone' => '01800000001']);
        $userB = $this->student(['name' => 'User B', 'email' => 'b@example.com', 'phone' => '01800000002']);
        $course = $this->course(9500);

        $component = Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'course_id' => (string) $course->id,
                'amount' => 9500,
            ]);

        $component
            ->set('data.user_id', (string) $userA->id)
            ->assertFormSet([
                'student_name' => 'User A',
                'student_email' => 'a@example.com',
                'student_phone' => '01800000001',
            ]);

        $component
            ->set('data.user_id', (string) $userB->id)
            ->assertFormSet([
                'student_name' => 'User B',
                'student_email' => 'b@example.com',
                'student_phone' => '01800000002',
            ]);
    }

    public function test_switching_between_three_users_always_shows_the_current_user(): void
    {
        $users = collect(['A', 'B', 'C'])->mapWithKeys(fn (string $letter): array => [
            $letter => $this->student([
                'name' => "User {$letter}",
                'email' => strtolower($letter).'@example.com',
                'phone' => '0180000000'.ord($letter),
            ]),
        ]);
        $course = $this->course(9500);

        $component = Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm(['course_id' => (string) $course->id, 'amount' => 9500]);

        foreach (['A', 'B', 'C'] as $letter) {
            $user = $users[$letter];

            $component
                ->set('data.user_id', (string) $user->id)
                ->assertFormSet([
                    'student_name' => "User {$letter}",
                    'student_email' => strtolower($letter).'@example.com',
                    'student_phone' => '0180000000'.ord($letter),
                ]);
        }
    }

    public function test_clearing_the_user_drops_the_autofilled_snapshot_instead_of_leaving_a_stale_identity(): void
    {
        $userB = $this->student(['name' => 'User B', 'email' => 'b@example.com', 'phone' => '01800000002']);
        $course = $this->course(9500);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm(['course_id' => (string) $course->id, 'amount' => 9500])
            ->set('data.user_id', (string) $userB->id)
            ->assertFormSet(['student_name' => 'User B'])
            ->set('data.user_id', null)
            ->assertFormSet([
                'student_name' => null,
                'student_email' => null,
                'student_phone' => null,
            ]);
    }

    public function test_manual_snapshot_overrides_survive_user_switching(): void
    {
        $userA = $this->student(['name' => 'User A', 'email' => 'a@example.com', 'phone' => '01800000001']);
        $userB = $this->student(['name' => 'User B', 'email' => 'b@example.com', 'phone' => '01800000002']);
        $course = $this->course(9500);

        $component = Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm(['course_id' => (string) $course->id, 'amount' => 9500])
            ->set('data.user_id', (string) $userA->id);

        // The admin renames the buyer by hand.
        $component->set('data.student_name', 'Manually Typed Name');

        // Switching to User B must not destroy the manual name, but the
        // untouched email/phone must stop showing User A.
        $component
            ->set('data.user_id', (string) $userB->id)
            ->assertFormSet([
                'student_name' => 'Manually Typed Name',
                'student_email' => 'b@example.com',
                'student_phone' => '01800000002',
            ]);
    }

    public function test_manual_snapshot_overrides_survive_clearing_the_user(): void
    {
        $userA = $this->student(['name' => 'User A', 'email' => 'a@example.com', 'phone' => '01800000001']);
        $course = $this->course(9500);

        $component = Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm(['course_id' => (string) $course->id, 'amount' => 9500])
            ->set('data.user_id', (string) $userA->id)
            ->set('data.student_email', 'manual-email@example.com');

        $component
            ->set('data.user_id', null)
            ->assertFormSet([
                'student_name' => null,
                'student_email' => 'manual-email@example.com',
                'student_phone' => null,
            ]);
    }

    // ------------------------------------------------------------------
    // User switching — DigitalOrder & ServiceOrder
    // ------------------------------------------------------------------

    public function test_digital_order_form_refreshes_the_snapshot_when_the_user_changes(): void
    {
        $userA = $this->student(['name' => 'Digital A', 'email' => 'da@example.com', 'phone' => '01811111111']);
        $userB = $this->student(['name' => 'Digital B', 'email' => 'db@example.com', 'phone' => '01822222222']);
        $product = $this->product();

        Livewire::actingAs($this->admin())
            ->test(CreateDigitalOrder::class)
            ->fillForm(['product_id' => (string) $product->id, 'amount' => 299])
            ->set('data.user_id', (string) $userA->id)
            ->assertFormSet(['student_name' => 'Digital A', 'student_email' => 'da@example.com'])
            ->set('data.user_id', (string) $userB->id)
            ->assertFormSet(['student_name' => 'Digital B', 'student_email' => 'db@example.com', 'student_phone' => '01822222222']);
    }

    public function test_service_order_form_refreshes_the_snapshot_when_the_user_changes(): void
    {
        $userA = $this->student(['name' => 'Service A', 'email' => 'sa@example.com', 'phone' => '01833333333']);
        $userB = $this->student(['name' => 'Service B', 'email' => 'sb@example.com', 'phone' => '01844444444']);
        $service = $this->service();

        Livewire::actingAs($this->admin())
            ->test(CreateServiceOrder::class)
            ->fillForm(['service_id' => (string) $service->id, 'amount' => 25000])
            ->set('data.user_id', (string) $userA->id)
            ->assertFormSet(['student_name' => 'Service A', 'student_email' => 'sa@example.com', 'student_phone' => '01833333333'])
            ->set('data.user_id', (string) $userB->id)
            ->assertFormSet(['student_name' => 'Service B', 'student_email' => 'sb@example.com', 'student_phone' => '01844444444'])
            ->set('data.user_id', null)
            ->assertFormSet(['student_name' => null, 'student_email' => null, 'student_phone' => null]);
    }

    // ------------------------------------------------------------------
    // Course → total amount autofill (Enrollment)
    // ------------------------------------------------------------------

    public function test_selecting_a_course_populates_the_total_amount_with_its_price(): void
    {
        $course = $this->course(10000);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([])
            ->set('data.course_id', (string) $course->id)
            ->assertFormSet(['amount' => '10000.00']);
    }

    public function test_the_populated_amount_stays_editable_for_a_negotiated_price(): void
    {
        $course = $this->course(10000);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm(['student_name' => 'Negotiated Buyer', 'student_email' => 'neg@example.com', 'student_phone' => '01712345678'])
            ->set('data.course_id', (string) $course->id)
            ->assertFormSet(['amount' => '10000.00'])
            ->set('data.amount', 8500)
            ->assertFormSet(['amount' => 8500])
            // Unrelated reactive changes never reset the negotiated amount.
            ->set('data.amount_paid', 4000)
            ->set('data.payment_status', 'partially_paid')
            ->assertFormSet(['amount' => 8500]);
    }

    public function test_changing_the_course_updates_the_total_from_the_new_price_and_due_stays_calculated(): void
    {
        $courseA = $this->course(10000);
        $courseB = $this->course(12000);

        $component = Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm(['student_name' => 'Switch Buyer', 'student_email' => 'switch@example.com', 'student_phone' => '01712345678'])
            ->set('data.course_id', (string) $courseA->id)
            ->assertFormSet(['amount' => '10000.00']);

        $component
            ->set('data.course_id', (string) $courseB->id)
            ->assertFormSet(['amount' => '12000.00']);

        // A fully paid offline sale on the new price stores the coherent state.
        $component
            ->set('data.amount_paid', 12000)
            ->set('data.payment_status', 'paid')
            ->set('data.enrollment_status', 'in_progress')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enrollments', [
            'course_id' => $courseB->id,
            'amount' => 12000,
            'amount_paid' => 12000,
            'amount_due' => 0,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function student(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'student',
            'email_verified_at' => now(),
            'name' => 'Student '.Str::random(4),
            'email' => 'student-'.Str::lower(Str::random(6)).'@example.com',
            'phone' => '01712345678',
        ], $overrides));
    }

    protected function course(int $price): Course
    {
        return Course::create([
            'title' => 'HSK Course '.Str::random(3),
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => $price,
            'is_published' => true,
        ]);
    }

    protected function product(): Product
    {
        return Product::create([
            'title' => 'Practice Tests',
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => 299,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Practice Tests',
            'is_published' => true,
        ]);
    }

    protected function service(): Service
    {
        return Service::create([
            'name' => 'Application Package '.Str::random(3),
            'slug' => 'service-'.Str::lower(Str::random(8)),
            'price' => 25000,
            'status' => true,
        ]);
    }
}
