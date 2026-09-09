<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
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

    public function test_payment_belongs_to_its_order(): void
    {
        $order = $this->order();
        $payment = $order->payments()->create([
            'method' => Payment::METHOD_NAGAD,
            'amount' => 500,
            'status' => Payment::STATUS_PAID,
        ]);

        $this->assertSame($order->id, $payment->order->id);
    }

    public function test_payment_stores_the_bangladeshi_manual_transfer_details(): void
    {
        $order = $this->order();
        $order->payments()->create([
            'method' => Payment::METHOD_BKASH,
            'trx_reference' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
            'amount' => 1000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'bkash',
            'trx_reference' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
            'amount' => 1000,
            'status' => 'pending',
            'currency' => 'BDT',
        ]);
    }

    public function test_payment_defaults_to_bdt_currency_when_not_provided(): void
    {
        $payment = $this->order()->payments()->create([
            'method' => Payment::METHOD_FREE,
            'amount' => 0,
            'status' => Payment::STATUS_PAID,
        ]);

        $this->assertSame('BDT', $payment->fresh()->currency);
    }

    public function test_payment_status_rolls_back_pending_default_when_not_provided(): void
    {
        $payment = $this->order()->payments()->create([
            'method' => Payment::METHOD_BANK,
            'amount' => 1000,
        ]);

        $fresh = $payment->fresh();

        $this->assertSame(Payment::STATUS_PENDING, $fresh->status);
        $this->assertNull($fresh->trx_reference);
        $this->assertNull($fresh->sender_number);
    }
}
