<?php

namespace Tests\Feature;

use App\Filament\Resources\DigitalOrders\Pages\CreateDigitalOrder;
use App\Filament\Resources\DigitalOrders\Pages\EditDigitalOrder;
use App\Filament\Resources\Enrollments\Pages\CreateEnrollment;
use App\Filament\Resources\Enrollments\Pages\EditEnrollment;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderMaterializer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCreateCanonicalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_enrollment_gets_its_canonical_records(): void
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

        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();

        $this->assertSame('9500.00', $order->total_amount);
        $this->assertSame('pending', $order->order_status);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'bkash',
            'amount' => 5000,
            'status' => 'paid',
        ]);
    }

    public function test_admin_created_digital_order_gets_its_canonical_records(): void
    {
        $product = $this->product();

        Livewire::actingAs($this->admin())
            ->test(CreateDigitalOrder::class)
            ->fillForm([
                'product_id' => (string) $product->id,
                'student_name' => 'Rahim Uddin',
                'student_email' => 'rahim@example.com',
                'student_phone' => '01712345678',
                'amount' => 299,
                'trx_id' => '9JQ2A3B4C5',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::where('legacy_source', 'digital_order')->firstOrFail();

        $this->assertSame('299.00', $order->total_amount);
        $this->assertSame('pending', $order->order_status);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'purchasable_type' => Product::class,
            'purchasable_id' => $product->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => null,
            'trx_reference' => '9JQ2A3B4C5',
            'status' => 'pending',
        ]);
    }

    public function test_editing_an_enrollment_cannot_drift_money_or_canonical_state(): void
    {
        $course = $this->course();
        $user = User::factory()->create();

        $component = Livewire::actingAs($this->admin())->test(CreateEnrollment::class);
        $component->fillForm([
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
        ])->call('create');

        $enrollment = Enrollment::firstOrFail();
        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();
        $paymentsBefore = $order->payments()->count();

        Livewire::actingAs($this->admin())
            ->test(EditEnrollment::class, ['record' => $enrollment->id])
            ->fillForm([
                'student_name' => 'Updated Name',
                'amount_paid' => 0,
                'payment_method' => 'nagad',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $enrollment->fresh();
        $this->assertSame('Updated Name', $fresh->student_name);
        $this->assertSame('5000.00', $fresh->amount_paid);
        $this->assertSame('bkash', $fresh->payment_method);

        $this->assertSame($paymentsBefore, $order->payments()->count());
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'bkash',
            'amount' => 5000,
            'status' => 'paid',
        ]);
    }

    public function test_editing_a_digital_order_cannot_drift_reference_or_canonical_state(): void
    {
        $product = $this->product();

        Livewire::actingAs($this->admin())
            ->test(CreateDigitalOrder::class)
            ->fillForm([
                'product_id' => (string) $product->id,
                'student_name' => 'Rahim Uddin',
                'student_email' => 'rahim@example.com',
                'student_phone' => '01712345678',
                'amount' => 299,
                'trx_id' => '9JQ2A3B4C5',
            ])
            ->call('create');

        $digitalOrder = DigitalOrder::firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditDigitalOrder::class, ['record' => $digitalOrder->id])
            ->fillForm([
                'student_name' => 'Updated Buyer',
                'trx_id' => 'FAKE9999999999',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('9JQ2A3B4C5', $digitalOrder->fresh()->trx_id);
        $this->assertDatabaseHas('payments', [
            'method' => null,
            'trx_reference' => '9JQ2A3B4C5',
            'status' => 'pending',
        ]);
    }

    public function test_create_rejects_pending_status_with_received_money(): void
    {
        $course = $this->course();

        Livewire::actingAs($this->admin())
            ->test(CreateEnrollment::class)
            ->fillForm([
                'course_id' => (string) $course->id,
                'student_name' => 'Karim Ahmed',
                'student_email' => 'karim@example.com',
                'student_phone' => '01812345678',
                'amount' => 9500,
                'amount_paid' => 5000,
                'amount_due' => 4500,
                'payment_method' => 'bkash',
                'payment_status' => 'pending',
                'enrollment_status' => 'pending',
            ])
            ->call('create')
            ->assertHasFormErrors(['payment_status']);

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_editing_an_enrollment_cannot_change_the_purchased_course(): void
    {
        $courseA = $this->course();
        $courseB = Course::create([
            'title' => 'HSK 2 Crash Course',
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => 8500,
            'is_published' => true,
        ]);

        $enrollment = Enrollment::create([
            'course_id' => $courseA->id,
            'student_name' => 'Karim Ahmed',
            'student_email' => 'karim@example.com',
            'student_phone' => '01812345678',
            'amount' => 9500,
            'amount_paid' => 5000,
            'amount_due' => 4500,
            'payment_status' => 'partially_paid',
            'enrollment_status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
        ]);
        app(OrderMaterializer::class)->materialize($enrollment);

        Livewire::actingAs($this->admin())
            ->test(EditEnrollment::class, ['record' => $enrollment->id])
            ->fillForm(['course_id' => (string) $courseB->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $enrollment->fresh();
        $this->assertSame($courseA->id, $fresh->course_id);

        $item = Order::where('legacy_source', 'enrollment')->firstOrFail()->items()->firstOrFail();
        $this->assertSame($courseA->id, $item->purchasable_id);
    }

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
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => 9500,
            'is_published' => true,
        ]);
    }

    protected function product(): Product
    {
        return Product::create([
            'title' => 'HSK 1 Practice Tests',
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => 299,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Practice Tests',
            'is_published' => true,
        ]);
    }
}
