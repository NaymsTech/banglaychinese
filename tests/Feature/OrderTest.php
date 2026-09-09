<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function order(int $total, string $status = Order::STATUS_PENDING): Order
    {
        return Order::create([
            'total_amount' => $total,
            'order_status' => $status,
            'student_name' => 'Test Student',
            'student_email' => 'test@example.com',
        ]);
    }

    protected function payment(Order $order, float $amount, string $status): Payment
    {
        return $order->payments()->create([
            'method' => Payment::METHOD_BKASH,
            'trx_reference' => 'TRX12345678',
            'amount' => $amount,
            'status' => $status,
        ]);
    }

    protected function item(Order $order, float $unitPrice): OrderItem
    {
        return $order->items()->create([
            'purchasable_type' => Course::class,
            'purchasable_id' => 1,
            'title' => 'Course A',
            'unit_price' => $unitPrice,
        ]);
    }

    public function test_order_returns_its_items_through_the_items_relation(): void
    {
        $order = $this->order(1000);

        $first = $this->item($order, 400);
        $second = $this->item($order, 600);

        $items = $order->items;

        $this->assertCount(2, $items);
        $this->assertTrue($items->contains('id', $first->id));
        $this->assertTrue($items->contains('id', $second->id));
    }

    public function test_order_returns_its_payments_through_the_payments_relation(): void
    {
        $order = $this->order(1000);

        $paid = $this->payment($order, 600, Payment::STATUS_PAID);
        $pending = $this->payment($order, 400, Payment::STATUS_PENDING);

        $payments = $order->payments;

        $this->assertCount(2, $payments);
        $this->assertTrue($payments->contains('id', $paid->id));
        $this->assertTrue($payments->contains('id', $pending->id));
    }

    public function test_deleting_an_order_removes_its_items_and_payments(): void
    {
        $order = $this->order(1000);
        $item = $this->item($order, 1000);
        $payment = $this->payment($order, 1000, Payment::STATUS_PENDING);

        $order->delete();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_paid_total_sums_only_payments_that_cleared_as_paid(): void
    {
        $order = $this->order(1000);
        $this->payment($order, 600, Payment::STATUS_PAID);
        $this->payment($order, 250, Payment::STATUS_PENDING);
        $this->payment($order, 100, Payment::STATUS_REJECTED);
        $this->payment($order, 50, Payment::STATUS_NEEDS_ATTENTION);

        $this->assertSame(600.0, $order->paidTotal());
    }

    public function test_partial_payment_leaves_a_due_balance_and_a_partial_state(): void
    {
        $order = $this->order(1000);
        $this->payment($order, 400, Payment::STATUS_PAID);

        $this->assertSame(400.0, $order->paidTotal());
        $this->assertSame(600.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PARTIALLY_PAID, $order->paymentState());
    }

    public function test_fully_paid_order_has_no_due_balance_and_is_paid(): void
    {
        $order = $this->order(1000);
        $this->payment($order, 600, Payment::STATUS_PAID);
        $this->payment($order, 400, Payment::STATUS_PAID);

        $this->assertSame(1000.0, $order->paidTotal());
        $this->assertSame(0.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PAID, $order->paymentState());
    }

    public function test_overpayment_never_creates_a_negative_due_balance(): void
    {
        $order = $this->order(1000);
        $this->payment($order, 1200, Payment::STATUS_PAID);

        $this->assertSame(0.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PAID, $order->paymentState());
    }

    public function test_refunded_payment_increases_the_due_balance_and_reverts_the_state(): void
    {
        $order = $this->order(1000);
        $this->payment($order, 1000, Payment::STATUS_PAID);
        $this->payment($order, 250, Payment::STATUS_REFUNDED);

        $this->assertSame(1000.0, $order->paidTotal());
        $this->assertSame(250.0, $order->refundedTotal());
        $this->assertSame(250.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PARTIALLY_PAID, $order->paymentState());
    }

    public function test_fully_refunded_order_is_unpaid_with_the_full_amount_due_again(): void
    {
        $order = $this->order(1000);
        $this->payment($order, 1000, Payment::STATUS_PAID);
        $this->payment($order, 1000, Payment::STATUS_REFUNDED);

        $this->assertSame(0.0, $order->paidTotal() - $order->refundedTotal());
        $this->assertSame(1000.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_UNPAID, $order->paymentState());
    }

    public function test_zero_amount_order_is_paid_without_any_payment(): void
    {
        $order = $this->order(0);

        $this->assertSame(0.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_PAID, $order->paymentState());
    }

    public function test_unpaid_order_with_no_payments_reports_unpaid(): void
    {
        $order = $this->order(1000);

        $this->assertSame(1000.0, $order->dueTotal());
        $this->assertSame(Order::PAYMENT_STATE_UNPAID, $order->paymentState());
    }
}
