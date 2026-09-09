<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutOrderWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutDualWriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_course_purchase_creates_both_enrollment_and_unified_records(): void
    {
        $user = $this->student();
        $course = $this->course(9500);

        $this->actingAs($user)
            ->from(route('checkout.show', $course->slug))
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'bkash',
                'transaction_id' => '9JQ2A3B4C5',
                'sender_number' => '01712345678',
            ])
            ->assertRedirect(route('checkout.confirmation'));

        $enrollment = Enrollment::firstOrFail();

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_email' => $user->email,
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
            'payment_status' => 'pending',
        ]);

        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();

        $this->assertSame($enrollment->id, $order->legacy_id);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('9500.00', $order->total_amount);
        $this->assertSame($enrollment->student_name, $order->student_name);
        $this->assertSame($enrollment->student_email, $order->student_email);
        $this->assertSame($enrollment->student_phone, $order->student_phone);

        $item = $order->items()->firstOrFail();

        $this->assertSame(Course::class, $item->purchasable_type);
        $this->assertSame($course->id, $item->purchasable_id);
        $this->assertSame($course->title, $item->title);
        $this->assertSame('9500.00', $item->unit_price);
        $this->assertSame(1, $item->quantity);

        $payment = $order->payments()->firstOrFail();

        $this->assertSame('bkash', $payment->method);
        $this->assertSame('9JQ2A3B4C5', $payment->trx_reference);
        $this->assertSame('01712345678', $payment->sender_number);
        $this->assertSame('9500.00', $payment->amount);
        $this->assertSame('pending', $payment->status);
        $this->assertNull($payment->review_note);
        $this->assertNull($payment->paid_at);
    }

    public function test_new_digital_purchase_creates_both_digital_order_and_unified_records(): void
    {
        $product = $this->product(800);

        $this->post(route('shop.checkout.store', $product->slug), [
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01812345678',
            'trx_id' => 'TRX987654321',
        ])->assertRedirect(route('shop.thank-you'));

        $digitalOrder = DigitalOrder::firstOrFail();

        $this->assertDatabaseHas('digital_orders', [
            'id' => $digitalOrder->id,
            'product_id' => $product->id,
            'student_email' => 'rahim@example.com',
            'trx_id' => 'TRX987654321',
            'amount' => 800,
            'status' => 'pending',
        ]);

        $order = Order::where('legacy_source', 'digital_order')->firstOrFail();

        $this->assertSame($digitalOrder->id, $order->legacy_id);
        $this->assertNull($order->user_id);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('800.00', $order->total_amount);
        $this->assertSame('Rahim Uddin', $order->student_name);
        $this->assertSame('rahim@example.com', $order->student_email);
        $this->assertSame('01812345678', $order->student_phone);

        $item = $order->items()->firstOrFail();

        $this->assertSame(Product::class, $item->purchasable_type);
        $this->assertSame($product->id, $item->purchasable_id);
        $this->assertSame($product->title, $item->title);
        $this->assertSame('800.00', $item->unit_price);

        $payment = $order->payments()->firstOrFail();

        // The shop checkout never collected a payment method.
        $this->assertNull($payment->method);
        $this->assertSame('TRX987654321', $payment->trx_reference);
        $this->assertNull($payment->sender_number);
        $this->assertSame('800.00', $payment->amount);
        $this->assertSame('pending', $payment->status);
    }

    public function test_free_course_enrollment_is_immediately_active_and_dual_written_without_a_payment_claim(): void
    {
        $user = $this->student();
        $course = $this->course(0);

        $this->actingAs($user)
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'free',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        // A free course is a zero-price sale: it activates immediately and
        // never lands in payment review.
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_method' => 'free',
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'status' => 'active',
            'amount' => 0,
            'amount_paid' => 0,
            'amount_due' => 0,
            'paid_at' => null,
        ]);

        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();

        $this->assertSame('in_progress', $order->order_status);
        $this->assertSame('0.00', $order->total_amount);
        $this->assertSame(0.0, $order->dueTotal());

        // No payment row is invented for a zero-price sale.
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'unit_price' => 0,
        ]);
    }

    public function test_a_failing_unified_course_write_rolls_back_the_enrollment(): void
    {
        $user = $this->student();
        $course = $this->course(9500);

        $this->app->instance(CheckoutOrderWriter::class, new class extends CheckoutOrderWriter
        {
            public function recordEnrollmentSale(Enrollment $enrollment): Order
            {
                throw new \RuntimeException('unified write failed');
            }
        });

        $this->actingAs($user)
            ->from(route('checkout.show', $course->slug))
            ->post(route('courses.enroll', $course->slug), [
                'payment_method' => 'bkash',
                'transaction_id' => '9JQ2A3B4C5',
                'sender_number' => '01712345678',
            ])
            ->assertSessionHasErrors('enroll');

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_failing_unified_digital_write_rolls_back_the_digital_order(): void
    {
        $product = $this->product(800);

        $this->app->instance(CheckoutOrderWriter::class, new class extends CheckoutOrderWriter
        {
            public function recordDigitalOrderSale(DigitalOrder $digitalOrder, array $paymentDetails = []): Order
            {
                throw new \RuntimeException('unified write failed');
            }
        });

        $response = $this->post(route('shop.checkout.store', $product->slug), [
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01812345678',
            'trx_id' => 'TRX987654321',
        ]);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('digital_orders', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'phone' => '01712345678',
            'email' => 'student-'.Str::lower(Str::random(6)).'@example.com',
        ]);
    }

    protected function course(int $price): Course
    {
        return Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => $price,
            'is_published' => true,
        ]);
    }

    protected function product(int $price): Product
    {
        return Product::create([
            'title' => 'HSK Vocabulary PDF',
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => $price,
            'file_path' => 'products/hsk-vocabulary.pdf',
        ]);
    }
}
