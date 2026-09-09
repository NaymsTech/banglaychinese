<?php

namespace Tests\Feature;

use App\Filament\Resources\DigitalOrders\Pages\CreateDigitalOrder;
use App\Filament\Resources\DigitalOrders\Pages\ListDigitalOrders;
use App\Filament\Resources\Enrollments\Pages\CreateEnrollment;
use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Filament\Resources\ServiceOrders\Pages\CreateServiceOrder;
use App\Filament\Resources\ServiceOrders\Pages\ListServiceOrders;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Manual (offline / WhatsApp) admin sales workflow.
 *
 * Covers the corrected admin-creation behavior: selecting an existing user
 * autofills the buyer snapshot, a fully paid offline course/service sale can
 * be created in one coherent step, partial payments flow through the
 * canonical Payment ledger, due stays derived, digital products never unlock
 * before full settlement, and contradictory states are rejected.
 */
class AdminManualSaleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Buyer snapshot autofill
    // ------------------------------------------------------------------

    public function test_selecting_a_user_on_enrollment_creation_links_the_account_and_autofills_the_snapshot(): void
    {
        $user = $this->student([
            'name' => 'Autofill Student',
            'email' => 'autofill-student@example.com',
            'phone' => '01812345678',
        ]);
        $course = $this->course(9500);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
                'amount' => 9500,
                'amount_paid' => 0,
                'payment_method' => 'bkash',
                'payment_status' => 'pending',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => 'Autofill Student',
            'student_email' => 'autofill-student@example.com',
            'student_phone' => '01812345678',
        ]);

        // The user is linked; no second user account was created (the only
        // accounts present are the admin acting and the linked student).
        $this->assertSame(2, User::count());
    }

    public function test_a_user_without_a_phone_leaves_the_phone_field_blank(): void
    {
        $user = $this->student([
            'name' => 'No Phone User',
            'email' => 'no-phone@example.com',
            'phone' => null,
        ]);
        $course = $this->course(9500);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
                'amount' => 9500,
                'amount_paid' => 9500,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'enrollment_status' => 'in_progress',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'student_name' => 'No Phone User',
            'student_email' => 'no-phone@example.com',
            'student_phone' => null,
        ]);
    }

    public function test_a_manually_typed_snapshot_is_never_overwritten_by_user_autofill(): void
    {
        $user = $this->student([
            'name' => 'Account Holder',
            'email' => 'account-holder@example.com',
            'phone' => '01700000000',
        ]);
        $course = $this->course(9500);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
                'student_name' => 'Typed Snapshot Name',
                'amount' => 9500,
                'amount_paid' => 0,
                'payment_method' => 'bkash',
                'payment_status' => 'pending',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'student_name' => 'Typed Snapshot Name',
            // Only the blank snapshot fields were filled from the account.
            'student_email' => 'account-holder@example.com',
            'student_phone' => '01700000000',
        ]);

        // Editing the snapshot later never writes back to the User.
        $this->assertSame('Account Holder', $user->fresh()->name);
        $this->assertSame('01700000000', $user->fresh()->phone);
    }

    public function test_selecting_a_user_on_a_digital_order_creation_autofills_the_snapshot(): void
    {
        $user = $this->student([
            'name' => 'Digital Buyer',
            'email' => 'digital-buyer@example.com',
            'phone' => '01912345678',
        ]);
        $product = $this->product();

        Livewire::actingAs($this->admin())
            ->test(CreateDigitalOrder::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'product_id' => (string) $product->id,
                'amount' => 299,
                'trx_id' => 'TRX-AUTOFILL-1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('digital_orders', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'student_name' => 'Digital Buyer',
            'student_email' => 'digital-buyer@example.com',
            'student_phone' => '01912345678',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);
    }

    // ------------------------------------------------------------------
    // Course enrollment — fully paid offline sale
    // ------------------------------------------------------------------

    public function test_admin_can_create_a_fully_paid_course_enrollment_in_one_coherent_step(): void
    {
        $user = $this->student();
        $course = $this->course(10000);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
                'amount' => 10000,
                'amount_paid' => 10000,
                'payment_method' => 'nagad',
                'payment_status' => 'paid',
                'enrollment_status' => 'in_progress',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $enrollment = Enrollment::firstOrFail();
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'user_id' => $user->id,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'amount_paid' => 10000,
            'amount_due' => 0,
        ]);
        $this->assertNotNull($enrollment->paid_at);

        // Canonical mirror: full paid payment, nothing due, course in progress.
        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();
        $this->assertSame('in_progress', $order->order_status);
        $this->assertSame('10000.00', $order->total_amount);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'nagad',
            'amount' => 10000,
            'status' => 'paid',
        ]);
        $this->assertSame(0.0, $order->dueTotal());
        $this->assertNotNull($order->payments()->firstOrFail()->paid_at);
    }

    public function test_admin_can_create_a_partially_paid_course_enrollment(): void
    {
        $user = $this->student();
        $course = $this->course(10000);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
                'amount' => 10000,
                'amount_paid' => 4000,
                'payment_method' => 'bkash',
                'payment_status' => 'partially_paid',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'payment_status' => 'partially_paid',
            'enrollment_status' => 'pending',
            'amount_paid' => 4000,
            'amount_due' => 6000,
        ]);

        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'bkash',
            'amount' => 4000,
            'status' => 'paid',
        ]);
        $this->assertSame(4000.0, $order->paidTotal());
        $this->assertSame(6000.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PARTIALLY_PAID, $order->paymentState());
    }

    public function test_course_due_is_always_calculated_never_taken_from_the_form(): void
    {
        $course = $this->course(10000);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'course_id' => (string) $course->id,
                'student_name' => 'Due Calc Buyer',
                'student_email' => 'due-buyer@example.com',
                'student_phone' => '01712345678',
                'amount' => 10000,
                'amount_paid' => 4000,
                'amount_due' => 1,
                'payment_method' => 'bkash',
                'payment_status' => 'partially_paid',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enrollments', [
            'amount_paid' => 4000,
            'amount_due' => 6000,
        ]);

        // The canonical ledger agrees with the derived balance.
        $this->assertSame(6000.0, Order::where('legacy_source', 'enrollment')->firstOrFail()->dueTotal());
    }

    public function test_contradictory_course_payment_states_are_rejected(): void
    {
        $course = $this->course(10000);

        $attempts = [
            // "paid" while only part of the money is in.
            ['amount_paid' => 4000, 'payment_status' => 'paid'],
            // "partially_paid" with the full amount received.
            ['amount_paid' => 10000, 'payment_status' => 'partially_paid'],
            // "partially_paid" with nothing received.
            ['amount_paid' => 0, 'payment_status' => 'partially_paid'],
            // "pending" while money has been received.
            ['amount_paid' => 2000, 'payment_status' => 'pending'],
        ];

        foreach ($attempts as $attempt) {
            Livewire::actingAs($this->admin())
                ->test(CreateEnrollment::class)
                ->fillForm(array_merge([
                    'course_id' => (string) $course->id,
                    'student_name' => 'Invalid Buyer',
                    'student_email' => 'invalid-buyer@example.com',
                    'student_phone' => '01712345678',
                    'amount' => 10000,
                    'enrollment_status' => 'pending',
                ], $attempt))
                ->call('create')
                ->assertHasFormErrors(['payment_status']);
        }

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_fully_paid_enrollment_cannot_be_created_as_pending(): void
    {
        $course = $this->course(10000);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'course_id' => (string) $course->id,
                'student_name' => 'Pending Paid Buyer',
                'student_email' => 'pending-paid@example.com',
                'student_phone' => '01712345678',
                'amount' => 10000,
                'amount_paid' => 10000,
                'payment_status' => 'paid',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasFormErrors(['enrollment_status']);

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_a_course_cannot_start_in_progress_until_the_payment_is_fully_paid(): void
    {
        $course = $this->course(10000);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'course_id' => (string) $course->id,
                'student_name' => 'Early Access Buyer',
                'student_email' => 'early-access@example.com',
                'student_phone' => '01712345678',
                'amount' => 10000,
                'amount_paid' => 4000,
                'payment_status' => 'partially_paid',
                'enrollment_status' => 'in_progress',
            ])
            ->call('create')
            ->assertHasFormErrors(['enrollment_status']);

        $this->assertDatabaseCount('enrollments', 0);
    }

    // ------------------------------------------------------------------
    // Enrollment — recording further offline payments from the list
    // ------------------------------------------------------------------

    public function test_enrollment_record_payment_action_keeps_method_and_derives_the_balance(): void
    {
        $user = $this->student();
        $course = $this->course(10000);

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
                'amount' => 10000,
                'amount_paid' => 4000,
                'payment_method' => 'bkash',
                'payment_status' => 'partially_paid',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $enrollment = Enrollment::firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('recordPayment', $enrollment, [
                'amount' => 2000,
                'method' => 'nagad',
                'trx_reference' => 'TRX-INSTALLMENT-2',
                'sender_number' => '01800000000',
            ])
            ->assertHasNoTableActionErrors();

        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();
        $this->assertSame(6000.0, $order->paidTotal());
        $this->assertSame(4000.0, $order->dueTotal());

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'nagad',
            'amount' => 2000,
            'status' => 'paid',
            'trx_reference' => 'TRX-INSTALLMENT-2',
        ]);

        $fresh = $enrollment->fresh();
        $this->assertSame('6000.00', $fresh->amount_paid);
        $this->assertSame('4000.00', $fresh->amount_due);
        $this->assertSame('partially_paid', $fresh->payment_status);
    }

    // ------------------------------------------------------------------
    // Digital products — manual creation never unlocks before full payment
    // ------------------------------------------------------------------

    public function test_manually_created_digital_order_unlocks_only_after_full_payment_is_recorded(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('products/hsk1.pdf', 'pdf-content');

        $user = $this->student();
        $product = $this->product(['file_path' => 'products/hsk1.pdf']);

        Livewire::actingAs($this->admin())
            ->test(CreateDigitalOrder::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'product_id' => (string) $product->id,
                'amount' => 299,
                'trx_id' => 'TRX-MANUAL-DIGITAL-1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $digitalOrder = DigitalOrder::firstOrFail();
        $this->assertSame(DigitalOrder::STATUS_PENDING, $digitalOrder->status);

        // Pending → no download.
        $this->actingAs($user)
            ->get(route('dashboard.downloads.download', $digitalOrder))
            ->assertForbidden();

        // Partial payment is not an option for digital products: the action
        // is refused and nothing is recorded.
        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->callTableAction('recordPayment', $digitalOrder, [
                'amount' => 100,
                'method' => 'bkash',
            ]);

        $this->assertSame(DigitalOrder::STATUS_PENDING, $digitalOrder->fresh()->status);
        $this->assertDatabaseMissing('payments', ['status' => 'paid']);

        // Recording the full amount settles the sale and unlocks the file.
        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->callTableAction('recordPayment', $digitalOrder, [
                'amount' => 299,
                'method' => 'bkash',
                'trx_reference' => 'TRX-MANUAL-FULL-1',
                'sender_number' => '01712345678',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame(DigitalOrder::STATUS_APPROVED, $digitalOrder->fresh()->status);

        $order = Order::where('legacy_source', 'digital_order')->firstOrFail();
        $this->assertSame(Order::STATUS_COMPLETED, $order->order_status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'bkash',
            'amount' => 299,
            'status' => 'paid',
            'trx_reference' => 'TRX-MANUAL-FULL-1',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.downloads.download', $digitalOrder))
            ->assertOk();
    }

    // ------------------------------------------------------------------
    // Service orders — offline sales ledger through canonical payments
    // ------------------------------------------------------------------

    public function test_admin_can_create_a_fully_paid_service_order_with_a_canonical_mirror(): void
    {
        $user = $this->student();
        $service = $this->service();

        Livewire::actingAs($this->admin())
            ->test(CreateServiceOrder::class)
            ->fillForm([
                'user_id' => (string) $user->id,
                'service_id' => (string) $service->id,
                'amount' => 25000,
                'amount_paid' => 25000,
                'payment_method' => 'bank',
                'payment_status' => 'paid',
                'enrollment_status' => 'in_progress',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('service_orders', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'student_name' => $user->name,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'amount_paid' => 25000,
            'amount_due' => 0,
        ]);

        // The manually created service order must exist in the canonical
        // ledger too, exactly like course enrollments and digital orders.
        $order = Order::where('legacy_source', 'service_order')->firstOrFail();
        $this->assertSame('25000.00', $order->total_amount);
        $this->assertSame('in_progress', $order->order_status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'bank',
            'amount' => 25000,
            'status' => 'paid',
        ]);
        $this->assertSame(0.0, $order->dueTotal());
    }

    public function test_admin_can_create_a_partially_paid_service_order_and_top_it_up_from_the_list(): void
    {
        $service = $this->service(50000);

        Livewire::actingAs($this->admin())
            ->test(CreateServiceOrder::class)
            ->fillForm([
                'service_id' => (string) $service->id,
                'student_name' => 'Service Partial Buyer',
                'student_email' => 'service-partial@example.com',
                'student_phone' => '01712345678',
                'amount' => 50000,
                'amount_paid' => 20000,
                'payment_method' => 'bkash',
                'payment_status' => 'partially_paid',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $serviceOrder = ServiceOrder::firstOrFail();
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'payment_status' => 'partially_paid',
            'enrollment_status' => 'pending',
            'amount_paid' => 20000,
            'amount_due' => 30000,
        ]);

        $order = Order::where('legacy_source', 'service_order')->firstOrFail();
        $this->assertSame(20000.0, $order->paidTotal());
        $this->assertSame(30000.0, $order->dueTotal());

        // Settle the remaining ৳30,000 through the canonical ledger.
        Livewire::actingAs($this->admin())
            ->test(ListServiceOrders::class)
            ->callTableAction('recordPayment', $serviceOrder, [
                'amount' => 30000,
                'method' => 'cash',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'amount_paid' => 50000,
            'amount_due' => 0,
        ]);

        $this->assertSame(0.0, $order->fresh()->dueTotal());
        $this->assertSame(50000.0, $order->fresh()->paidTotal());
        $this->assertSame(2, $order->fresh()->payments()->where('status', 'paid')->count());
    }

    public function test_service_order_mark_as_paid_quick_action_stays_canonical_first(): void
    {
        $service = $this->service(25000);

        Livewire::actingAs($this->admin())
            ->test(CreateServiceOrder::class)
            ->fillForm([
                'service_id' => (string) $service->id,
                'student_name' => 'Quick Pay Buyer',
                'student_email' => 'quick-pay@example.com',
                'student_phone' => '01712345678',
                'amount' => 25000,
                'amount_paid' => 0,
                'payment_method' => 'nagad',
                'payment_status' => 'pending',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $serviceOrder = ServiceOrder::firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(ListServiceOrders::class)
            ->callTableAction('markPaid', $serviceOrder);

        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'amount_paid' => 25000,
            'amount_due' => 0,
        ]);

        $order = Order::where('legacy_source', 'service_order')->firstOrFail();
        $this->assertSame(25000.0, $order->paidTotal());
        $this->assertSame(0.0, $order->dueTotal());
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

    protected function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'title' => 'Practice Tests',
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => 299,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Practice Tests',
            'is_published' => true,
        ], $overrides));
    }

    protected function service(int $price = 25000): Service
    {
        return Service::create([
            'name' => 'Application Package '.Str::random(3),
            'slug' => 'service-'.Str::lower(Str::random(8)),
            'price' => $price,
            'status' => true,
        ]);
    }
}
