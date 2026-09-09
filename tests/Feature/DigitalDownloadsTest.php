<?php

namespace Tests\Feature;

use App\Models\DigitalOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DigitalDownloadsTest extends TestCase
{
    use RefreshDatabase;

    protected function student(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'student',
            'is_admin' => false,
            'email_verified_at' => now(),
            'email' => 'student-'.Str::lower(Str::random(6)).'@example.com',
        ], $overrides));
    }

    /**
     * Create a fully sanctioned sale: legacy DigitalOrder approved through
     * PaymentReviewService, so the canonical Order is completed and its paid
     * Payment rows cover the total.
     */
    protected function approvedOrderFor(User $student, array $productOverrides = []): DigitalOrder
    {
        Storage::fake('local');
        Storage::disk('local')->put('products/hsk1.pdf', 'pdf-content');

        $product = Product::create(array_merge([
            'title' => 'HSK 1 Practice Tests',
            'slug' => 'product-'.Str::lower(Str::random(6)),
            'price' => 299,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Practice Tests',
            'is_published' => true,
        ], $productOverrides));

        $order = DigitalOrder::create([
            'user_id' => $student->id,
            'product_id' => $product->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'student_phone' => '01712345678',
            'trx_id' => 'TRX'.Str::upper(Str::random(8)),
            'amount' => $product->price,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);

        app(PaymentReviewService::class)->approve($order);

        return $order;
    }

    protected function canonicalOrder(DigitalOrder $digitalOrder): Order
    {
        return Order::where('legacy_source', Order::SOURCE_DIGITAL_ORDER)
            ->where('legacy_id', $digitalOrder->id)
            ->firstOrFail();
    }

    // ------------------------------------------------------------------
    // Baseline behavior (same as before, but canonical-backed)
    // ------------------------------------------------------------------

    public function test_guests_are_redirected_to_login(): void
    {
        $order = $this->approvedOrderFor($this->student());

        $this->get(route('dashboard.downloads.download', $order))->assertRedirect(route('login'));
    }

    public function test_approved_owner_can_download_the_product_file(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);

        $response = $this->actingAs($student)->get(route('dashboard.downloads.download', $order));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_students_who_did_not_purchase_cannot_download(): void
    {
        $order = $this->approvedOrderFor($this->student());

        $this->actingAs($this->student())
            ->get(route('dashboard.downloads.download', $order))
            ->assertForbidden();
    }

    public function test_external_download_link_is_used_instead_of_the_stored_file(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student, [
            'external_download_url' => 'https://drive.google.com/ebook.pdf',
        ]);

        $this->actingAs($student)
            ->get(route('dashboard.downloads.download', $order))
            ->assertRedirect('https://drive.google.com/ebook.pdf');
    }

    public function test_pending_orders_cannot_be_downloaded(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        DB::table('digital_orders')->where('id', $order->id)->update(['status' => DigitalOrder::STATUS_PENDING]);

        $this->actingAs($student)
            ->get(route('dashboard.downloads.download', $order))
            ->assertForbidden();
    }

    public function test_guest_order_is_downloadable_by_the_student_email_owner(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('products/hsk1.pdf', 'pdf-content');

        $product = Product::create([
            'title' => 'HSK 1 Practice Tests',
            'slug' => 'product-'.Str::lower(Str::random(6)),
            'price' => 299,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Practice Tests',
            'is_published' => true,
        ]);

        $order = DigitalOrder::create([
            'user_id' => null,
            'product_id' => $product->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => 'TRXGUEST99',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);

        app(PaymentReviewService::class)->approve($order);

        $this->actingAs($this->student(['email' => 'rahim@example.com']))
            ->get(route('dashboard.downloads.download', $order))
            ->assertOk();
    }

    // ------------------------------------------------------------------
    // Canonical authorization denial cases
    // ------------------------------------------------------------------

    public function test_pending_payment_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        DB::table('payments')->where('order_id', $this->canonicalOrder($order)->id)->update(['status' => Payment::STATUS_PENDING]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_needs_attention_payment_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        DB::table('payments')->where('order_id', $this->canonicalOrder($order)->id)->update(['status' => Payment::STATUS_NEEDS_ATTENTION]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_rejected_payment_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        DB::table('payments')->where('order_id', $this->canonicalOrder($order)->id)->update(['status' => Payment::STATUS_REJECTED]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_paid_amount_below_total_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        $payment = $this->canonicalOrder($order)->payments()->firstOrFail();
        $payment->update(['amount' => 100]); // 100 < 299

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_multiple_paid_rows_whose_sum_covers_total_allow_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student, ['price' => 800]);
        $canonical = $this->canonicalOrder($order);
        $canonical->payments()->delete();
        $canonical->payments()->create(['amount' => 400, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => now()]);
        $canonical->payments()->create(['amount' => 400, 'status' => Payment::STATUS_PAID, 'method' => 'nagad', 'paid_at' => now()]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertOk();
    }

    public function test_multiple_paid_rows_whose_sum_is_below_total_deny_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student, ['price' => 800]);
        $canonical = $this->canonicalOrder($order);
        $canonical->payments()->delete();
        $canonical->payments()->create(['amount' => 300, 'status' => Payment::STATUS_PAID, 'method' => 'bkash', 'paid_at' => now()]);
        $canonical->payments()->create(['amount' => 300, 'status' => Payment::STATUS_PAID, 'method' => 'nagad', 'paid_at' => now()]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_missing_canonical_order_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        Order::query()->delete();

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_legacy_approved_but_canonical_missing_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        Order::query()->delete();

        $this->assertSame('approved', $order->fresh()->status);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_missing_canonical_order_item_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        $this->canonicalOrder($order)->items()->delete();

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_missing_canonical_payment_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        $this->canonicalOrder($order)->payments()->delete();

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_canonical_order_not_completed_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        DB::table('orders')->where('id', $this->canonicalOrder($order)->id)->update(['order_status' => Order::STATUS_PENDING]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_canonical_order_belonging_to_another_user_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        $other = $this->student(['email' => 'other-owner@example.com']);

        DB::table('orders')->where('id', $this->canonicalOrder($order)->id)->update([
            'user_id' => $other->id,
            'student_email' => $other->email,
        ]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_canonical_order_pointing_to_another_digital_order_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        DB::table('orders')->where('id', $this->canonicalOrder($order)->id)->update(['legacy_id' => $order->id + 1000]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_canonical_item_pointing_to_another_product_denies_download(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);

        $otherProduct = Product::create([
            'title' => 'Another Product',
            'slug' => 'product-'.Str::lower(Str::random(6)),
            'price' => 100,
            'file_path' => 'products/other.pdf',
            'is_published' => true,
        ]);

        $this->canonicalOrder($order)->items()->update(['purchasable_id' => $otherProduct->id]);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_canonical_valid_but_legacy_not_approved_fails_closed(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        DB::table('digital_orders')->where('id', $order->id)->update(['status' => DigitalOrder::STATUS_PENDING]);

        // Canonical side is fully paid/completed — the sanctioned mirror would
        // never produce this split, so it is drift and must fail closed.
        $this->assertSame('paid', $this->canonicalOrder($order)->payments()->first()->status);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_refunded_payment_rows_fail_closed(): void
    {
        $student = $this->student();
        $order = $this->approvedOrderFor($student);
        $this->canonicalOrder($order)->payments()->create(['amount' => 299, 'status' => Payment::STATUS_REFUNDED, 'method' => 'bkash']);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_idor_attempt_using_another_customers_order_is_forbidden(): void
    {
        $owner = $this->student();
        $order = $this->approvedOrderFor($owner);

        $this->actingAs($this->student())
            ->get(route('dashboard.downloads.download', $order))
            ->assertForbidden();
    }

    public function test_own_paid_order_for_product_a_cannot_authorize_another_digital_order(): void
    {
        $student = $this->student();
        $approved = $this->approvedOrderFor($student);

        // A second DigitalOrder for the SAME product that is only legacy-approved
        // (canonical missing) must not inherit the paid order's entitlement.
        $second = DigitalOrder::create([
            'user_id' => $student->id,
            'product_id' => $approved->product_id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'student_phone' => '01712345678',
            'trx_id' => 'TRXSECOND99',
            'amount' => $approved->amount,
            'status' => DigitalOrder::STATUS_APPROVED,
        ]);

        $this->assertDatabaseCount('orders', 1);

        $this->actingAs($student)
            ->get(route('dashboard.downloads.download', $second))
            ->assertForbidden();
    }

    public function test_missing_local_file_still_returns_the_existing_response(): void
    {
        $student = $this->student();
        Storage::fake('local');

        $product = Product::create([
            'title' => 'Missing File PDF',
            'slug' => 'product-'.Str::lower(Str::random(6)),
            'price' => 299,
            'file_path' => 'products/does-not-exist.pdf',
            'is_published' => true,
        ]);

        $order = DigitalOrder::create([
            'user_id' => $student->id,
            'product_id' => $product->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'student_phone' => '01712345678',
            'trx_id' => 'TRXMISSFILE',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);
        app(PaymentReviewService::class)->approve($order);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertNotFound();
    }

    public function test_changing_only_legacy_status_to_approved_never_grants_access(): void
    {
        $student = $this->student();
        $product = Product::create([
            'title' => 'No Canonical PDF',
            'slug' => 'product-'.Str::lower(Str::random(6)),
            'price' => 299,
            'file_path' => 'products/none.pdf',
            'is_published' => true,
        ]);

        $order = DigitalOrder::create([
            'user_id' => $student->id,
            'product_id' => $product->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'student_phone' => '01712345678',
            'trx_id' => 'TRXONLYLEGACY',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);

        $order->update(['status' => DigitalOrder::STATUS_APPROVED]);

        $this->assertDatabaseCount('orders', 0);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();
    }

    public function test_sanctioned_admin_approval_flips_download_authorization(): void
    {
        $student = $this->student();
        $product = Product::create([
            'title' => 'Approve Flow PDF',
            'slug' => 'product-'.Str::lower(Str::random(6)),
            'price' => 299,
            'file_path' => 'products/none.pdf',
            'is_published' => true,
        ]);

        Storage::fake('local');
        Storage::disk('local')->put('products/none.pdf', 'pdf-content');

        $order = DigitalOrder::create([
            'user_id' => $student->id,
            'product_id' => $product->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'student_phone' => '01712345678',
            'trx_id' => 'TRXAPPROVE1',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);
        app(OrderMaterializer::class)->materialize($order);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertForbidden();

        app(PaymentReviewService::class)->approve($order);

        $this->actingAs($student)->get(route('dashboard.downloads.download', $order))->assertOk();
    }
}
