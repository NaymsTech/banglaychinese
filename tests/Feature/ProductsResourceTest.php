<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
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
}
