<?php

namespace Tests\Feature;

use App\Filament\Resources\DigitalOrders\Pages\CreateDigitalOrder;
use App\Filament\Resources\DigitalOrders\Pages\EditDigitalOrder;
use App\Filament\Resources\DigitalOrders\Pages\ListDigitalOrders;
use App\Jobs\SendEmailJob;
use App\Models\DigitalOrder;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalOrdersResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'title' => 'HSK 1 Practice Tests',
            'slug' => 'hsk-1-practice-tests',
            'price' => 299,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Practice Tests',
            'is_published' => true,
        ], $overrides));
    }

    protected function order(Product $product, array $overrides = []): DigitalOrder
    {
        return DigitalOrder::create(array_merge([
            'product_id' => $product->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => '9JQ2A3B4C5',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ], $overrides));
    }

    /**
     * A pending digital order with a canonical Order whose claim Payment
     * carries the given canonical transaction reference.
     */
    protected function orderWithCanonicalReference(string $trxReference, array $orderOverrides = []): DigitalOrder
    {
        $order = $this->order($this->product(), array_merge([
            'trx_id' => $trxReference,
        ], $orderOverrides));

        app(OrderMaterializer::class)->materialize($order);

        return $order;
    }

    protected function approvedTemplate(): EmailTemplate
    {
        return EmailTemplate::factory()->create([
            'key' => 'product_approved',
            'subject' => 'Your copy of {product_title} is ready',
            'body' => '<p>Hi {student_name}, your copy of {product_title} is ready — download it here: {download_link}</p>',
            'variables' => ['student_name', 'product_title', 'download_link'],
            'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
        ]);
    }

    public function test_admin_can_open_digital_order_list_and_create_page(): void
    {
        $admin = $this->admin();
        $this->order($this->product());

        $this->actingAs($admin)->get(ListDigitalOrders::getUrl())->assertOk();
        $this->actingAs($admin)->get(CreateDigitalOrder::getUrl())->assertOk();
    }

    public function test_admin_can_create_a_digital_order_through_the_form(): void
    {
        $product = $this->product();

        Livewire::actingAs($this->admin())
            ->test(CreateDigitalOrder::class)
            ->fillForm([
                'product_id' => (string) $product->id,
                'student_name' => 'Karim Ahmed',
                'student_email' => 'karim@example.com',
                'student_phone' => '01812345678',
                'trx_id' => 'ABC123XYZ',
                'amount' => 299,
                'status' => DigitalOrder::STATUS_PENDING,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('digital_orders', [
            'product_id' => $product->id,
            'student_name' => 'Karim Ahmed',
            'student_email' => 'karim@example.com',
            'trx_id' => 'ABC123XYZ',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);
    }

    public function test_approving_a_pending_order_queues_the_download_link_email(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->approvedTemplate();

        $order = $this->order($this->product(['title' => 'HSK 4 Mock Tests']));

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->callTableAction('approve', $order)
            ->assertNotified('Order approved.');

        $fresh = $order->fresh();

        $this->assertSame(DigitalOrder::STATUS_APPROVED, $fresh->status);
        $this->assertNotNull($fresh->approval_email_sent_at);
        $this->assertDatabaseCount('email_logs', 1);
        $this->assertDatabaseHas('email_logs', [
            'provider_id' => null,
            'template_key' => 'product_approved',
            'recipient_email' => 'rahim@example.com',
            'recipient_name' => 'Rahim Uddin',
            'subject' => 'Your copy of HSK 4 Mock Tests is ready',
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        Queue::assertPushed(SendEmailJob::class, function (SendEmailJob $job) use ($order): bool {
            return $job->templateKey === 'product_approved'
                && $job->to === 'rahim@example.com'
                && $job->recipientName === 'Rahim Uddin'
                && str_contains($job->htmlContent, 'Hi Rahim Uddin')
                && str_contains($job->htmlContent, 'HSK 4 Mock Tests')
                && str_contains($job->htmlContent, route('dashboard.downloads.download', $order->fresh()));
        });
    }

    public function test_approving_an_already_approved_order_does_not_resend_the_email(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->approvedTemplate();

        $order = $this->order($this->product(), [
            'status' => DigitalOrder::STATUS_APPROVED,
            'approval_email_sent_at' => now(),
        ]);

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->assertTableActionHidden('approve', $order);

        Queue::assertNothingPushed();
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_an_approved_order_without_an_email_marker_can_be_retried(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->approvedTemplate();

        // State approved but the email marker is missing (e.g. a previous
        // queue failure) — approve must be able to send the email again.
        $order = $this->order($this->product(), ['status' => DigitalOrder::STATUS_APPROVED]);

        Queue::fake([SendEmailJob::class]);

        $result = app(PaymentReviewService::class)->approve($order);

        $this->assertSame(['changed' => false, 'emailed' => true], $result);
        $this->assertNotNull($order->fresh()->approval_email_sent_at);
        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_repeated_approval_never_duplicates_the_email(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        $this->approvedTemplate();

        $order = $this->order($this->product());

        Queue::fake([SendEmailJob::class]);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->callTableAction('approve', $order);

        // A second (e.g. double-submitted) approval attempt must not email again.
        $second = app(PaymentReviewService::class)->approve($order->fresh());

        $this->assertSame(['changed' => false, 'emailed' => true], $second);
        $this->assertDatabaseCount('email_logs', 1);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_edit_page_cannot_change_the_order_status(): void
    {
        $order = $this->order($this->product());

        Livewire::actingAs($this->admin())
            ->test(EditDigitalOrder::class, ['record' => $order->getRouteKey()])
            ->assertFormFieldDoesNotExist('status');
    }

    public function test_table_search_finds_order_by_canonical_payment_reference(): void
    {
        $order = $this->orderWithCanonicalReference('CANON-TRX-777');

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->searchTable('CANON-TRX-777')
            ->assertCanSeeTableRecords([$order]);
    }

    public function test_legacy_trx_value_never_drives_the_transaction_search(): void
    {
        $order = $this->orderWithCanonicalReference('CANON-TRX-888');

        DB::table('digital_orders')->where('id', $order->id)->update(['trx_id' => 'LEGACY-ONLY-888']);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->searchTable('CANON-TRX-888')
            ->assertCanSeeTableRecords([$order]);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->searchTable('LEGACY-ONLY-888')
            ->assertCanNotSeeTableRecords([$order]);
    }

    public function test_legacy_only_order_without_canonical_records_is_not_found_or_materialized(): void
    {
        $order = $this->order($this->product(), [
            'trx_id' => 'ONLY-LEGACY-1',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->searchTable('ONLY-LEGACY-1')
            ->assertCanNotSeeTableRecords([$order]);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_multi_payment_search_uses_active_references_only_and_returns_the_order_once(): void
    {
        $order = $this->orderWithCanonicalReference('FIND-ME-9', [
            'student_name' => 'Unique Search Buyer',
        ]);

        $canonical = Order::where('legacy_source', 'digital_order')->where('legacy_id', $order->id)->firstOrFail();
        $canonical->payments()->delete();
        $canonical->payments()->create(['amount' => 299, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'trx_reference' => 'FIND-ME-9', 'paid_at' => now()]);
        $canonical->payments()->create(['amount' => 1, 'status' => Payment::STATUS_PAID, 'method' => 'nagad', 'trx_reference' => 'FIND-ME-9', 'paid_at' => now()]);
        $canonical->payments()->create(['amount' => 299, 'status' => Payment::STATUS_REJECTED, 'method' => 'bkash', 'trx_reference' => 'STALE-REJECT-9']);

        $html = Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->searchTable('STALE-REJECT-9')
            ->assertCanNotSeeTableRecords([$order])
            ->searchTable('FIND-ME-9')
            ->assertCanSeeTableRecords([$order])
            ->html();

        $this->assertSame(1, substr_count($html, 'Unique Search Buyer'));
    }

    public function test_student_and_product_search_still_work(): void
    {
        $order = $this->orderWithCanonicalReference('REGR-TRX-1', [
            'student_name' => 'Searchable Student Name',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->searchTable('Searchable Student Name')
            ->assertCanSeeTableRecords([$order]);

        Livewire::actingAs($this->admin())
            ->test(ListDigitalOrders::class)
            ->searchTable('HSK 1 Practice Tests')
            ->assertCanSeeTableRecords([$order]);
    }
}
