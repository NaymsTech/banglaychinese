<?php

namespace Tests\Feature;

use App\Filament\Resources\DigitalOrders\Pages\ListDigitalOrders;
use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderMaterializer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OrderDeleteCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_an_enrollment_also_removes_its_canonical_order_items_and_payments(): void
    {
        $enrollment = $this->enrollment();
        $order = app(OrderMaterializer::class)->materialize($enrollment)['order'];

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('delete', $enrollment);

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    public function test_deleting_a_digital_order_also_removes_its_canonical_order_items_and_payments(): void
    {
        $digitalOrder = $this->digitalOrder();
        $order = app(OrderMaterializer::class)->materialize($digitalOrder)['order'];

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->callTableAction('delete', $digitalOrder);

        $this->assertDatabaseCount('digital_orders', 0);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    public function test_bulk_deleting_enrollments_removes_each_canonical_order(): void
    {
        $first = $this->enrollment();
        $second = $this->enrollment();
        $firstOrder = app(OrderMaterializer::class)->materialize($first)['order'];
        $secondOrder = app(OrderMaterializer::class)->materialize($second)['order'];

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableBulkAction('delete', [$first->id, $second->id]);

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseMissing('orders', ['id' => $firstOrder->id]);
        $this->assertDatabaseMissing('orders', ['id' => $secondOrder->id]);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_bulk_deleting_digital_orders_removes_each_canonical_order(): void
    {
        $first = $this->digitalOrder();
        $second = $this->digitalOrder();
        $firstOrder = app(OrderMaterializer::class)->materialize($first)['order'];
        $secondOrder = app(OrderMaterializer::class)->materialize($second)['order'];

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->callTableBulkAction('delete', [$first->id, $second->id]);

        $this->assertDatabaseCount('digital_orders', 0);
        $this->assertDatabaseMissing('orders', ['id' => $firstOrder->id]);
        $this->assertDatabaseMissing('orders', ['id' => $secondOrder->id]);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_deleting_a_legacy_only_enrollment_without_canonical_records_still_works(): void
    {
        $enrollment = $this->enrollment();

        $this->assertDatabaseCount('orders', 0);

        Livewire::actingAs($this->admin())
            ->test(ListEnrollments::class)
            ->callTableAction('delete', $enrollment);

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function enrollment(array $overrides = []): Enrollment
    {
        return Enrollment::create(array_merge([
            'course_id' => $this->course()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'amount' => 5000,
            'amount_paid' => 0,
            'amount_due' => 5000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
        ], $overrides));
    }

    protected function digitalOrder(): DigitalOrder
    {
        return DigitalOrder::create([
            'product_id' => $this->product()->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => 'TRX987654321',
            'amount' => 800,
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
