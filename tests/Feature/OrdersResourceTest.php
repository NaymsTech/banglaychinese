<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Users\UserResource;
use App\Jobs\SendEmailJob;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\OrderCompletionService;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OrdersResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_list_shows_course_product_and_service_purchases(): void
    {
        [$courseOrder, $productOrder, $serviceOrder] = $this->seedAllTypes();

        Livewire::actingAs($this->admin())
            ->test(ListOrders::class)
            ->assertCanSeeTableRecords([$courseOrder, $productOrder, $serviceOrder])
            ->assertSee('Course Buyer')
            ->assertSee('Product Buyer')
            ->assertSee('Service Buyer');
    }

    public function test_orders_search_finds_customer_by_name_email_phone_and_reference(): void
    {
        [$courseOrder] = $this->seedAllTypes();

        Livewire::actingAs($this->admin())
            ->test(ListOrders::class)
            ->searchTable('Course Buyer')
            ->assertCanSeeTableRecords([$courseOrder]);

        Livewire::actingAs($this->admin())
            ->test(ListOrders::class)
            ->searchTable('course-buyer@example.com')
            ->assertCanSeeTableRecords([$courseOrder]);

        Livewire::actingAs($this->admin())
            ->test(ListOrders::class)
            ->searchTable('01711112222')
            ->assertCanSeeTableRecords([$courseOrder]);

        Livewire::actingAs($this->admin())
            ->test(ListOrders::class)
            ->searchTable('TRXCOURSE123')
            ->assertCanSeeTableRecords([$courseOrder]);
    }

    public function test_payment_status_filter_isolates_paid_orders(): void
    {
        [, $productOrder] = $this->seedAllTypes();

        // Approve the digital product so it is fully paid.
        app(PaymentReviewService::class)->approve(DigitalOrder::firstOrFail());

        Livewire::actingAs($this->admin())
            ->test(ListOrders::class)
            ->filterTable('payment_state', 'paid')
            ->assertCanSeeTableRecords([$productOrder])
            ->assertCanNotSeeTableRecords([Order::where('legacy_source', 'enrollment')->firstOrFail()]);
    }

    public function test_approve_action_works_through_payment_review_service(): void
    {
        [$courseOrder] = $this->seedAllTypes();

        Livewire::actingAs($this->admin())
            ->test(ViewOrder::class, ['record' => $courseOrder->id])
            ->callAction('approve');

        $this->assertSame('paid', Enrollment::firstOrFail()->payment_status);
        $this->assertSame('in_progress', Enrollment::firstOrFail()->enrollment_status);
    }

    public function test_record_payment_action_creates_a_canonical_payment(): void
    {
        [$courseOrder] = $this->seedAllTypes();

        Livewire::actingAs($this->admin())
            ->test(ViewOrder::class, ['record' => $courseOrder->id])
            ->callAction('recordPayment', [
                'amount' => 4000,
                'method' => 'bkash',
                'trx_reference' => 'ADMINREC0001',
                'sender_number' => '01712345678',
            ]);

        $order = Order::firstOrFail();
        $this->assertSame(4000.0, $order->paidTotal());
        $this->assertSame(6000.0, $order->dueTotal());
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'trx_reference' => 'ADMINREC0001',
            'amount' => 4000,
            'status' => 'paid',
        ]);

        $enrollment = Enrollment::firstOrFail();
        $this->assertSame('4000.00', $enrollment->amount_paid);
        $this->assertSame('6000.00', $enrollment->amount_due);
    }

    public function test_course_mark_completed_action_mirrors_both_sides_and_is_idempotent(): void
    {
        [$courseOrder] = $this->seedAllTypes();

        // Fully pay/unlock first, as the existing course completion rule requires.
        app(PaymentReviewService::class)->approve(Enrollment::firstOrFail());

        Livewire::actingAs($this->admin())
            ->test(ViewOrder::class, ['record' => $courseOrder->id])
            ->callAction('markCompleted');

        $this->assertSame('completed', Enrollment::firstOrFail()->enrollment_status);
        $this->assertSame('completed', $courseOrder->fresh()->order_status);

        // Once completed the page no longer offers the action…
        $this->assertHeaderActionAbsent('markCompleted', $courseOrder->id);

        // …and running the domain service again is a safe, idempotent no-op.
        $result = app(OrderCompletionService::class)->complete($courseOrder->fresh());

        $this->assertFalse($result['changed']);
        $this->assertSame('completed', Enrollment::firstOrFail()->enrollment_status);
        $this->assertSame('completed', $courseOrder->fresh()->order_status);
    }

    public function test_send_reminder_action_queues_once_and_is_hidden_when_paid(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        EmailTemplate::factory()->create([
            'key' => 'payment_reminder',
            'subject' => 'Payment reminder',
            'body' => '<p>Please complete your payment.</p>',
            'variables' => ['student_name', 'course_title', 'amount_due', 'due_date'],
        ]);

        [$courseOrder] = $this->seedAllTypes();

        Queue::fake([SendEmailJob::class]);

        $component = Livewire::actingAs($this->admin())
            ->test(ViewOrder::class, ['record' => $courseOrder->id])
            ->callAction('sendReminder');

        $this->assertDatabaseCount('email_logs', 1);
        $this->assertNotNull(Enrollment::firstOrFail()->payment_reminder_sent_at);

        // Second attempt respects the cooldown (no second email).
        $component->callAction('sendReminder');
        $this->assertDatabaseCount('email_logs', 1);

        // Once fully paid, the page no longer offers the reminder action.
        app(PaymentReviewService::class)->approve(Enrollment::firstOrFail());

        $this->assertHeaderActionAbsent('sendReminder', $courseOrder->id);
    }

    public function test_digital_order_has_no_completion_action(): void
    {
        [, $productOrder] = $this->seedAllTypes();

        $this->assertHeaderActionAbsent('markCompleted', $productOrder->id);
    }

    public function test_service_order_mark_completed_mirrors_canonical_and_legacy(): void
    {
        [, , $serviceOrder] = $this->seedAllTypes();

        Livewire::actingAs($this->admin())
            ->test(ViewOrder::class, ['record' => $serviceOrder->id])
            ->callAction('markCompleted');

        $this->assertSame('completed', ServiceOrder::firstOrFail()->enrollment_status);
        $this->assertSame('completed', $serviceOrder->fresh()->order_status);
    }

    public function test_orders_table_customer_name_links_to_customer_history(): void
    {
        [$courseOrder] = $this->seedAllTypes();

        $customerUrl = UserResource::getUrl('view', ['record' => $courseOrder->user_id]);

        $component = Livewire::actingAs($this->admin())
            ->test(ListOrders::class)
            ->assertCanSeeTableRecords([$courseOrder]);

        $this->assertStringContainsString($customerUrl, $component->html());
    }

    protected function assertHeaderActionAbsent(string $actionName, int $orderId): void
    {
        $component = Livewire::actingAs($this->admin())
            ->test(ViewOrder::class, ['record' => $orderId]);

        $headerActionNames = collect($component->instance()->getCachedHeaderActions())
            ->map(fn ($action): string => $action->getName())
            ->all();

        $this->assertNotContains($actionName, $headerActionNames);
    }

    protected function seedAllTypes(): array
    {
        $user = $this->student('course-buyer@example.com', 'Course Buyer');

        $course = Course::create(['title' => 'HSK 1', 'slug' => 'c-'.Str::random(6), 'price' => 10000, 'is_published' => true]);
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => 'Course Buyer',
            'student_email' => 'course-buyer@example.com',
            'student_phone' => '01711112222',
            'amount' => 10000,
            'amount_paid' => 0,
            'amount_due' => 10000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
            'transaction_id' => 'TRXCOURSE123',
            'sender_number' => '01711112222',
        ]);
        $courseOrder = app(OrderMaterializer::class)->materialize($enrollment)['order'];

        $productUser = $this->student('product-buyer@example.com', 'Product Buyer');
        $product = Product::create(['title' => 'E-Book', 'slug' => 'p-'.Str::random(6), 'price' => 800, 'file_path' => 'products/ebook.pdf', 'is_published' => true]);
        $digital = DigitalOrder::create([
            'user_id' => $productUser->id,
            'product_id' => $product->id,
            'student_name' => 'Product Buyer',
            'student_email' => 'product-buyer@example.com',
            'student_phone' => '01722223333',
            'trx_id' => 'TRXPRODUCT1',
            'amount' => 800,
            'status' => 'pending',
        ]);
        $productOrder = app(OrderMaterializer::class)->materialize($digital)['order'];

        $serviceUser = $this->student('service-buyer@example.com', 'Service Buyer');
        $service = Service::create(['name' => 'Application Support', 'slug' => 's-'.Str::random(6), 'price' => 50000]);
        $serviceOrderRecord = ServiceOrder::create([
            'user_id' => $serviceUser->id,
            'service_id' => $service->id,
            'student_name' => 'Service Buyer',
            'student_email' => 'service-buyer@example.com',
            'student_phone' => '01733334444',
            'amount' => 50000,
            'amount_paid' => 0,
            'amount_due' => 50000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);
        $serviceOrder = app(OrderMaterializer::class)->materialize($serviceOrderRecord)['order'];

        return [$courseOrder, $productOrder, $serviceOrder];
    }

    protected function student(string $email, string $name): User
    {
        return User::factory()->create([
            'role' => 'student',
            'name' => $name,
            'email' => $email,
            'phone' => '01712345678',
            'email_verified_at' => now(),
        ]);
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_admin' => true]);
    }
}
