<?php

namespace Tests\Feature;

use App\Models\DigitalOrder;
use App\Models\Product;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopStorefrontTest extends TestCase
{
    use RefreshDatabase;

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

    protected function student(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'student',
            'is_admin' => false,
        ], $overrides));
    }

    protected function order(User $student, Product $product, array $overrides = []): DigitalOrder
    {
        return DigitalOrder::create(array_merge([
            'user_id' => $student->id,
            'product_id' => $product->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'student_phone' => '01712345678',
            'amount' => $product->price,
            'status' => DigitalOrder::STATUS_APPROVED,
        ], $overrides));
    }

    public function test_shop_lists_only_published_products(): void
    {
        $this->product();
        $this->product([
            'title' => 'Hidden Draft Book',
            'slug' => 'hidden-draft-book',
            'is_published' => false,
        ]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('HSK 1 Practice Tests')
            ->assertDontSee('Hidden Draft Book');
    }

    public function test_checkout_page_shows_product_details_and_payment_numbers(): void
    {
        SettingsService::set('bkash_number', '01711111111');
        SettingsService::set('nagad_number', '01822222222');

        $product = $this->product();

        $this->get(route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]))
            ->assertOk()
            ->assertSee('HSK 1 Practice Tests')
            ->assertSee('৳299')
            ->assertSee('01711111111')
            ->assertSee('01822222222');
    }

    public function test_guest_can_submit_an_order_and_gets_a_pending_order(): void
    {
        $product = $this->product();

        $this->post(route('shop.checkout.store', $product->slug), [
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => '9JQ2A3B4C5',
        ])
            ->assertRedirect(route('shop.thank-you'))
            ->assertSessionHas('order_id');

        $this->assertDatabaseHas('digital_orders', [
            'product_id' => $product->id,
            'user_id' => null,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'trx_id' => '9JQ2A3B4C5',
            'amount' => 299,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);
    }

    public function test_logged_in_buyer_gets_their_order_linked_to_their_account(): void
    {
        $buyer = $this->student();
        $product = $this->product();

        $this->actingAs($buyer)->post(route('shop.checkout.store', $product->slug), [
            'student_name' => $buyer->name,
            'student_email' => $buyer->email,
            'student_phone' => '01712345678',
            'trx_id' => 'ABC123XYZ',
        ])->assertRedirect(route('shop.thank-you'));

        $this->assertDatabaseHas('digital_orders', [
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'status' => DigitalOrder::STATUS_PENDING,
        ]);
    }

    public function test_submitting_an_invalid_order_fails_validation(): void
    {
        $product = $this->product();

        $this->post(route('shop.checkout.store', $product->slug), [
            'student_name' => '',
            'student_email' => 'not-an-email',
            'student_phone' => '123',
            'trx_id' => '',
        ])->assertSessionHasErrors(['student_name', 'student_email', 'student_phone', 'trx_id']);

        $this->assertDatabaseCount('digital_orders', 0);
    }

    public function test_unpublished_products_cannot_be_checked_out(): void
    {
        $product = $this->product(['is_published' => false]);

        $this->get(route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]))->assertNotFound();
        $this->post(route('shop.checkout.store', $product->slug), [
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => '9JQ2A3B4C5',
        ])->assertNotFound();

        $this->assertDatabaseCount('digital_orders', 0);
    }

    public function test_thank_you_page_requires_a_just_submitted_order(): void
    {
        $this->get(route('shop.thank-you'))->assertRedirect(route('shop.index'));
    }

    public function test_dashboard_lists_approved_purchases_with_download_links(): void
    {
        $buyer = $this->student();
        $order = $this->order($buyer, $this->product());

        $this->actingAs($buyer)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('My Downloads')
            ->assertSee('HSK 1 Practice Tests')
            ->assertSee('Download PDF')
            ->assertSee(route('dashboard.downloads.download', $order));
    }

    public function test_dashboard_hides_other_people_purchases(): void
    {
        $buyer = $this->student(['email' => 'buyer@example.com']);
        $stranger = $this->student(['email' => 'stranger@example.com']);

        $this->order($buyer, $this->product());

        $this->actingAs($stranger)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertDontSee('HSK 1 Practice Tests');
    }
}
