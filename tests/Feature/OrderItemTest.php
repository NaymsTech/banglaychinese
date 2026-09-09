<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    protected function order(): Order
    {
        return Order::create([
            'total_amount' => 1000,
            'order_status' => Order::STATUS_PENDING,
            'student_name' => 'Test Student',
            'student_email' => 'test@example.com',
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

    protected function product(): Product
    {
        return Product::create([
            'title' => 'HSK Vocabulary PDF',
            'slug' => 'hsk-vocabulary-pdf',
            'price' => 500,
            'file_path' => 'products/hsk-vocabulary.pdf',
        ]);
    }

    public function test_item_belongs_to_its_order(): void
    {
        $order = $this->order();
        $item = $this->item($order, Course::class, $this->course()->id, 9500);

        $this->assertSame($order->id, $item->order->id);
    }

    public function test_item_resolves_a_course_through_the_purchasable_relation(): void
    {
        $course = $this->course();
        $item = $this->item($this->order(), Course::class, $course->id, $course->price);

        $this->assertInstanceOf(Course::class, $item->purchasable);
        $this->assertSame($course->id, $item->purchasable->id);
    }

    public function test_item_resolves_a_product_through_the_purchasable_relation(): void
    {
        $product = $this->product();
        $item = $this->item($this->order(), Product::class, $product->id, $product->price);

        $this->assertInstanceOf(Product::class, $item->purchasable);
        $this->assertSame($product->id, $item->purchasable->id);
    }

    public function test_item_keeps_a_snapshot_of_the_title_and_unit_price(): void
    {
        $item = $this->item($this->order(), Product::class, $this->product()->id, 500, 'HSK Vocabulary PDF');

        $fresh = $item->fresh();

        $this->assertSame('HSK Vocabulary PDF', $fresh->title);
        $this->assertSame('500.00', $fresh->unit_price);
        $this->assertSame(1, $fresh->quantity);
    }

    protected function item(Order $order, string $purchasableType, int $purchasableId, float $unitPrice, string $title = 'Purchased Item'): OrderItem
    {
        return $order->items()->create([
            'purchasable_type' => $purchasableType,
            'purchasable_id' => $purchasableId,
            'title' => $title,
            'unit_price' => $unitPrice,
        ]);
    }
}
