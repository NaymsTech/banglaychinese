<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderMaterializer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderMaterializerTest extends TestCase
{
    use RefreshDatabase;

    public function test_materialize_creates_the_unified_records_for_a_paid_enrollment(): void
    {
        $user = User::factory()->create(['phone' => '01712345678']);
        $course = $this->course();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'student_phone' => $user->phone,
            'amount' => 5000,
            'amount_paid' => 5000,
            'amount_due' => 0,
            'payment_status' => 'paid',
            'enrollment_status' => 'in_progress',
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
            'paid_at' => now()->subDay(),
        ]);

        $result = app(OrderMaterializer::class)->materialize($enrollment);

        $this->assertSame(1, $result['orders']);
        $this->assertSame(1, $result['items']);
        $this->assertSame(1, $result['payments']);
        $this->assertSame(0, $result['skipped']);

        $order = $result['order'];
        $this->assertSame('enrollment', $order->legacy_source);
        $this->assertSame($enrollment->id, $order->legacy_id);
        $this->assertSame('in_progress', $order->order_status);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('bkash', $payment->method);
        $this->assertSame('9JQ2A3B4C5', $payment->trx_reference);
        $this->assertSame('01712345678', $payment->sender_number);
    }

    public function test_materialize_is_idempotent_and_returns_the_existing_order(): void
    {
        $enrollment = Enrollment::create([
            'course_id' => $this->course()->id,
            'student_name' => 'Nusrat',
            'student_email' => 'nusrat@example.com',
            'student_phone' => '01712345678',
            'amount' => 5000,
            'amount_paid' => 0,
            'amount_due' => 5000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
        ]);

        $materializer = app(OrderMaterializer::class);
        $first = $materializer->materialize($enrollment);
        $second = $materializer->materialize($enrollment);

        $this->assertSame(1, $second['skipped']);
        $this->assertSame(0, $second['orders']);
        $this->assertSame(0, $second['items']);
        $this->assertSame(0, $second['payments']);
        $this->assertSame($first['order']->id, $second['order']->id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_materialize_never_modifies_the_legacy_record(): void
    {
        $enrollment = Enrollment::create([
            'course_id' => $this->course()->id,
            'student_name' => 'Nusrat',
            'student_email' => 'nusrat@example.com',
            'student_phone' => '01712345678',
            'amount' => 5000,
            'amount_paid' => 0,
            'amount_due' => 5000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
            'payment_method' => 'nagad',
            'transaction_id' => 'NAGAD11223344',
            'sender_number' => '01712345678',
        ]);

        $before = $enrollment->fresh()->getAttributes();

        $materializer = app(OrderMaterializer::class);
        $materializer->materialize($enrollment);
        $materializer->materialize($enrollment);

        $this->assertSame($before, $enrollment->fresh()->getAttributes());
    }

    public function test_materialize_rejects_an_unsupported_legacy_model(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        app(OrderMaterializer::class)->materialize(new User);
    }

    public function test_materialize_keeps_an_unknown_digital_payment_method_null(): void
    {
        $product = $this->product();
        $digitalOrder = DigitalOrder::create([
            'product_id' => $product->id,
            'student_name' => 'Rahim',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01812345678',
            'trx_id' => 'TRX987654321',
            'amount' => 800,
            'status' => 'pending',
        ]);

        $result = app(OrderMaterializer::class)->materialize($digitalOrder);

        $this->assertSame('pending', $result['order']->order_status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $result['order']->id,
            'method' => null,
            'trx_reference' => 'TRX987654321',
            'status' => 'pending',
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
            'title' => 'HSK Vocabulary PDF',
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => 800,
            'file_path' => 'products/hsk-vocabulary.pdf',
        ]);
    }
}
