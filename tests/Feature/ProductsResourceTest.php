<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\DigitalOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsResourceTest extends TestCase
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

    public function test_admin_can_open_product_list_and_create_page(): void
    {
        $admin = $this->admin();
        $this->product();

        $this->actingAs($admin)->get(ListProducts::getUrl())->assertOk();
        $this->actingAs($admin)->get(CreateProduct::getUrl())->assertOk();
    }

    public function test_admin_can_create_a_product_with_cover_and_pdf(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateProduct::class)
            ->fillForm([
                'title' => 'HSK 2 Vocabulary Book',
                'slug' => 'hsk-2-vocabulary-book',
                'price' => '499',
                'category' => 'E-book',
                'cover_image' => UploadedFile::fake()->image('cover.png', 40, 40),
                'file_path' => UploadedFile::fake()->create('book.pdf', 500, 'application/pdf'),
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'title' => 'HSK 2 Vocabulary Book',
            'slug' => 'hsk-2-vocabulary-book',
            'price' => 499,
            'category' => 'E-book',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_create_a_product_delivered_by_external_link_only(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateProduct::class)
            ->fillForm([
                'title' => 'Google Drive Cheat Sheet',
                'slug' => 'google-drive-cheat-sheet',
                'price' => '99',
                'category' => 'Study Notes',
                'external_download_url' => 'https://drive.google.com/cheat-sheet.pdf',
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'title' => 'Google Drive Cheat Sheet',
            'slug' => 'google-drive-cheat-sheet',
            'price' => 99,
            'file_path' => null,
            'external_download_url' => 'https://drive.google.com/cheat-sheet.pdf',
        ]);
    }

    public function test_products_are_listed_with_order_counts(): void
    {
        $product = $this->product();

        $this->actingAs($this->admin())
            ->get(ListProducts::getUrl())
            ->assertOk()
            ->assertSee($product->title);
    }

    public function test_product_with_digital_orders_cannot_be_deleted_and_admin_is_informed(): void
    {
        $product = $this->product();
        $order = $this->digitalOrder($product);

        Livewire::actingAs($this->admin())
            ->test(ListProducts::class)
            ->callTableAction('delete', $product)
            ->assertNotified('Product could not be deleted');

        $this->assertModelExists($product);
        $this->assertModelExists($order);
    }

    public function test_product_without_digital_orders_can_be_deleted_from_the_list(): void
    {
        $product = $this->product();

        Livewire::actingAs($this->admin())
            ->test(ListProducts::class)
            ->callTableAction('delete', $product);

        $this->assertModelMissing($product);
    }

    public function test_product_with_digital_orders_cannot_be_deleted_from_the_edit_page(): void
    {
        $product = $this->product();
        $order = $this->digitalOrder($product);

        Livewire::actingAs($this->admin())
            ->test(EditProduct::class, ['record' => $product->getKey()])
            ->callAction('delete')
            ->assertNotified('Product could not be deleted');

        $this->assertModelExists($product);
        $this->assertModelExists($order);
    }

    public function test_product_without_digital_orders_can_be_deleted_from_the_edit_page(): void
    {
        $product = $this->product();

        Livewire::actingAs($this->admin())
            ->test(EditProduct::class, ['record' => $product->getKey()])
            ->callAction('delete');

        $this->assertModelMissing($product);
    }

    protected function digitalOrder(Product $product, array $overrides = []): DigitalOrder
    {
        return DigitalOrder::create(array_merge([
            'product_id' => $product->id,
            'student_name' => 'Rahim Uddin',
            'student_email' => 'rahim@example.com',
            'student_phone' => '01712345678',
            'trx_id' => 'TRX987654321',
            'amount' => 800,
            'status' => DigitalOrder::STATUS_PENDING,
        ], $overrides));
    }
}
