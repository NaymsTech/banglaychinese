<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Users\Pages\ViewCustomer;
use App\Filament\Resources\Users\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\Users\UserResource;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\CustomerOrderSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_with_multiple_orders_sees_all_orders_only_their_own(): void
    {
        $customer = $this->customer();
        [$orderA, $orderB, $orderC] = $this->seedHistory($customer);

        $other = $this->customer('other@example.com');
        $otherOrder = $this->orderFor($other, 5000.0, status: 'completed');

        Livewire::actingAs($this->admin())
            ->test(OrdersRelationManager::class, ['ownerRecord' => $customer, 'pageClass' => ViewCustomer::class])
            ->assertCanSeeTableRecords([$orderA, $orderB, $orderC])
            ->assertCanNotSeeTableRecords([$otherOrder]);
    }

    public function test_customer_lifetime_money_totals_match_the_canonical_orders(): void
    {
        $customer = $this->customer();
        $this->seedHistory($customer);

        $summary = CustomerOrderSummary::forUser($customer);

        $this->assertSame(3, $summary->orderCount);
        $this->assertSame(62000.0, $summary->totalValue);
        $this->assertSame(27000.0, $summary->totalPaid);
        $this->assertSame(0.0, $summary->totalRefunded);
        $this->assertSame(35000.0, $summary->totalDue);
        $this->assertSame(2, $summary->partiallyPaidCount);
        $this->assertSame(1, $summary->completedCount);

        Livewire::actingAs($this->admin())
            ->test(ViewCustomer::class, ['record' => $customer->id])
            ->assertSee($customer->name)
            ->assertSee($customer->email)
            ->assertSee('৳ 62,000.00')
            ->assertSee('৳ 27,000.00')
            ->assertSee('৳ 35,000.00');
    }

    public function test_pending_rejected_and_needs_attention_claims_are_not_counted_as_paid(): void
    {
        $customer = $this->customer();

        $order = $this->orderFor($customer, 10000.0);
        $this->paymentFor($order, 4000.0, Payment::STATUS_PENDING);
        $this->paymentFor($order, 2000.0, Payment::STATUS_NEEDS_ATTENTION);
        $this->paymentFor($order, 1500.0, Payment::STATUS_REJECTED);

        $summary = CustomerOrderSummary::forUser($customer);

        $this->assertSame(0.0, $summary->totalPaid);
        $this->assertSame(0.0, $summary->totalRefunded);
        $this->assertSame(10000.0, $summary->totalValue);
        $this->assertSame(10000.0, $summary->totalDue);
        $this->assertSame(1, $summary->needsAttentionCount);
    }

    public function test_refunds_are_accounted_through_canonical_refunded_payments(): void
    {
        $customer = $this->customer();

        $order = $this->orderFor($customer, 10000.0, status: 'completed');
        $this->paymentFor($order, 10000.0, Payment::STATUS_PAID, paid: true);
        $this->paymentFor($order, 4000.0, Payment::STATUS_REFUNDED, refunded: true);

        $summary = CustomerOrderSummary::forUser($customer);

        $this->assertSame(10000.0, $summary->totalValue);
        $this->assertSame(10000.0, $summary->totalPaid);
        $this->assertSame(4000.0, $summary->totalRefunded);
        $this->assertSame(4000.0, $summary->totalDue);
        $this->assertSame(1, $summary->completedCount);
    }

    public function test_multiple_order_items_all_display_in_the_customer_table(): void
    {
        $customer = $this->customer();

        $order = $this->orderFor($customer, 18000.0, status: 'completed', titles: ['IELTS Course', 'Grammar Course', 'Digital Workbook']);

        Livewire::actingAs($this->admin())
            ->test(OrdersRelationManager::class, ['ownerRecord' => $customer, 'pageClass' => ViewCustomer::class])
            ->assertCanSeeTableRecords([$order])
            ->assertSee('IELTS Course')
            ->assertSee('Grammar Course')
            ->assertSee('Digital Workbook');
    }

    public function test_customer_orders_reuse_the_existing_unified_order_detail(): void
    {
        $customer = $this->customer();
        [$orderA] = $this->seedHistory($customer);

        $orderUrl = OrderResource::getUrl('view', ['record' => $orderA]);

        $this->assertStringContainsString('/orders/', $orderUrl);

        // The customer history target page (ViewOrder) opens for this order.
        $this->actingAs($this->admin())
            ->get($orderUrl)
            ->assertOk();

        Livewire::actingAs($this->admin())
            ->test(ViewOrder::class, ['record' => $orderA->id])
            ->assertSee('Payment history');
    }

    public function test_guest_checkout_account_appears_with_orders_linked_by_user_id(): void
    {
        // Simulates the automatically created account from a guest paid
        // checkout: the canonical order is linked through orders.user_id, so
        // the account shows up in customer history without email matching.
        $customer = $this->customer('guest-checkout@example.com', 'Guest Buyer');

        $order = Order::create([
            'user_id' => $customer->id,
            'student_name' => 'Typed Buyer Name',
            'student_email' => 'guest-checkout@example.com',
            'student_phone' => null,
            'total_amount' => 8000,
            'order_status' => Order::STATUS_IN_PROGRESS,
        ]);
        $this->paymentFor($order, 8000.0, Payment::STATUS_PAID, paid: true);

        Livewire::actingAs($this->admin())
            ->test(OrdersRelationManager::class, ['ownerRecord' => $customer, 'pageClass' => ViewCustomer::class])
            ->assertCanSeeTableRecords([$order]);

        $summary = CustomerOrderSummary::forUser($customer);
        $this->assertSame(1, $summary->orderCount);
        $this->assertSame(8000.0, $summary->totalPaid);
    }

    public function test_customer_with_no_orders_has_a_safe_empty_state(): void
    {
        $customer = $this->customer('nobody@example.com', 'Nobody');

        $summary = CustomerOrderSummary::forUser($customer);

        $this->assertSame(0, $summary->orderCount);
        $this->assertSame(0.0, $summary->totalValue);
        $this->assertSame(0.0, $summary->totalPaid);
        $this->assertSame(0.0, $summary->totalDue);

        Livewire::actingAs($this->admin())
            ->test(ViewCustomer::class, ['record' => $customer->id])
            ->assertSee($customer->name)
            ->assertSee('৳ 0.00');

        Livewire::actingAs($this->admin())
            ->test(OrdersRelationManager::class, ['ownerRecord' => $customer, 'pageClass' => ViewCustomer::class])
            ->assertSee('No orders yet');
    }

    public function test_customer_history_page_is_reachable_from_users_resource_url(): void
    {
        $customer = $this->customer('reachable@example.com', 'Reachable Buyer');
        $this->seedHistory($customer);

        $this->actingAs($this->admin())
            ->get(UserResource::getUrl('view', ['record' => $customer]))
            ->assertOk()
            ->assertSee('Lifetime financial summary')
            ->assertSee('Total orders');
    }

    protected function seedHistory(User $customer): array
    {
        $orderA = $this->orderFor($customer, 10000.0);
        $this->paymentFor($orderA, 5000.0, Payment::STATUS_PAID, paid: true);

        $orderB = $this->orderFor($customer, 2000.0, status: 'completed');
        $this->paymentFor($orderB, 2000.0, Payment::STATUS_PAID, paid: true);

        $orderC = $this->orderFor($customer, 50000.0, status: 'in_progress', titles: ['HSK 1']);
        $this->paymentFor($orderC, 20000.0, Payment::STATUS_PAID, paid: true);

        return [$orderA, $orderB, $orderC];
    }

    protected function orderFor(User $user, float $total, string $status = 'pending', array $titles = []): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'student_phone' => $user->phone,
            'total_amount' => $total,
            'order_status' => $status,
        ]);

        if ($titles !== []) {
            $course = Course::create([
                'title' => 'Fixture Course',
                'slug' => 'fixture-'.Str::random(6),
                'price' => 0,
                'is_published' => true,
            ]);

            foreach ($titles as $title) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'purchasable_type' => Course::class,
                    'purchasable_id' => $course->id,
                    'title' => $title,
                    'unit_price' => 0,
                    'quantity' => 1,
                ]);
            }
        }

        return $order;
    }

    protected function paymentFor(Order $order, float $amount, string $status, bool $paid = false, bool $refunded = false): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method' => 'bkash',
            'trx_reference' => 'CUST-'.Str::upper(Str::random(10)),
            'sender_number' => '01712345678',
            'amount' => $amount,
            'status' => $status,
            'paid_at' => $paid ? now() : null,
            'refunded_at' => $refunded ? now() : null,
        ]);
    }

    protected function customer(string $email = 'buyer@example.com', string $name = 'Test Buyer'): User
    {
        return User::factory()->create([
            'role' => 'student',
            'name' => $name,
            'email' => $email,
            'phone' => '01711112222',
            'email_verified_at' => now(),
        ]);
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_admin' => true]);
    }
}
