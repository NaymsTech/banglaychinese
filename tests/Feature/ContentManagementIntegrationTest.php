<?php

namespace Tests\Feature;

use App\Filament\Pages\CheckoutPageCms;
use App\Filament\Pages\FaqCms;
use App\Filament\Pages\PrivacyPolicyCms;
use App\Filament\Pages\RefundPolicyCms;
use App\Filament\Pages\TermsAndConditionsCms;
use App\Http\Controllers\StaticPageController;
use App\Models\Course;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\LegalPagesContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Stage 6F: legal/FAQ pages and checkout informational copy are managed
 * through the existing settings-backed Content Management system, with no
 * duplicate content model and no change to checkout logic.
 */
class ContentManagementIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_public_pages_still_work_at_the_same_urls(): void
    {
        foreach ([
            'terms-and-conditions',
            'privacy-policy',
            'refund-and-returns-policy',
            'faq',
        ] as $slug) {
            $this->get(route('pages.show', $slug))->assertOk();
        }
    }

    public function test_terms_page_content_can_be_saved_from_the_cms_and_updates_the_public_page(): void
    {
        Livewire::actingAs($this->admin())
            ->test(TermsAndConditionsCms::class)
            ->fillForm([
                'title' => 'Terms & Conditions',
                'intro' => 'Welcome to Banglay Chinese.',
                'updated_at' => 'January 2026',
                'sections' => [
                    ['heading' => '1. CMS Managed Section', 'body' => "First paragraph.\n\nSecond paragraph."],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $stored = json_decode((string) Setting::where('key', 'legal_page_terms-and-conditions')->value('value'), true);

        $this->assertSame('1. CMS Managed Section', $stored['sections'][0]['heading']);
        $this->assertSame(['First paragraph.', 'Second paragraph.'], $stored['sections'][0]['body']);

        // Exactly one authoritative record for the page.
        $this->assertSame(1, Setting::where('key', 'legal_page_terms-and-conditions')->count());

        $this->get(route('pages.show', 'terms-and-conditions'))
            ->assertOk()
            ->assertSee('1. CMS Managed Section')
            ->assertSee('First paragraph.')
            ->assertSee('Second paragraph.');
    }

    public function test_faq_cms_save_updates_the_public_accordion(): void
    {
        Livewire::actingAs($this->admin())
            ->test(FaqCms::class)
            ->fillForm([
                'title' => 'Frequently Asked Questions',
                'intro' => 'Quick answers.',
                'updated_at' => 'January 2026',
                'sections' => [
                    ['heading' => 'CMS Question One?', 'body' => 'The answer body.'],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('pages.show', 'faq'))
            ->assertOk()
            ->assertSee('CMS Question One?')
            ->assertSee('The answer body.');
    }

    public function test_all_four_legal_pages_are_editable_in_content_management(): void
    {
        $admin = $this->admin();

        foreach ([
            TermsAndConditionsCms::class,
            PrivacyPolicyCms::class,
            RefundPolicyCms::class,
            FaqCms::class,
        ] as $page) {
            $this->actingAs($admin)->get($page::getUrl())->assertOk();
        }
    }

    public function test_saving_faq_after_editing_only_the_title_keeps_and_persists_the_default_sections(): void
    {
        // Regression: saving untouched repeatable sections used to fail with a
        // TypeError because their body values stayed paragraph arrays.
        Livewire::actingAs($this->admin())
            ->test(FaqCms::class)
            ->fillForm(['title' => 'FAQ — Edited Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $defaults = StaticPageController::defaultContent('faq');
        $stored = json_decode((string) Setting::where('key', 'legal_page_faq')->value('value'), true);

        $this->assertSame('FAQ — Edited Title', $stored['title']);
        $this->assertSame($defaults['sections'], $stored['sections']);

        // A fresh visit hydrates the stored paragraph arrays back into the
        // editable plain-text bodies.
        $reload = Livewire::actingAs($this->admin())->test(FaqCms::class);

        $this->assertSame('FAQ — Edited Title', $reload->get('data.title'));
        $this->assertCount(count($defaults['sections']), $reload->get('data.sections'));
        $this->assertTrue(collect($reload->get('data.sections'))
            ->every(fn (array $section): bool => is_string($section['body'] ?? null) && $section['body'] !== ''));

        // The public resolver returns the override on top of the defaults.
        $resolved = LegalPagesContent::resolve('faq', $defaults);

        $this->assertSame('FAQ — Edited Title', $resolved['title']);
        $this->assertSame($defaults['sections'], $resolved['sections']);
    }

    public function test_repeatable_sections_save_and_reload_for_all_four_legal_pages(): void
    {
        $pages = [
            TermsAndConditionsCms::class => 'terms-and-conditions',
            PrivacyPolicyCms::class => 'privacy-policy',
            RefundPolicyCms::class => 'refund-and-returns-policy',
            FaqCms::class => 'faq',
        ];

        foreach ($pages as $page => $slug) {
            $key = 'legal_page_'.$slug;
            $marker = 'CMS Heading '.$slug;
            $second = 'CMS Second Heading '.$slug;

            Livewire::actingAs($this->admin())
                ->test($page)
                ->fillForm([
                    'title' => 'Saved Title '.$slug,
                    'intro' => 'Saved intro '.$slug,
                    'updated_at' => 'Updated '.$slug,
                    'sections' => [
                        ['heading' => $marker, 'body' => "First paragraph.\n\nSecond paragraph."],
                        ['heading' => $second, 'body' => 'Single paragraph body.'],
                    ],
                ])
                ->call('save')
                ->assertHasNoFormErrors();

            $stored = json_decode((string) Setting::where('key', $key)->value('value'), true);

            $this->assertSame('Saved Title '.$slug, $stored['title']);
            $this->assertSame('Saved intro '.$slug, $stored['intro']);
            $this->assertSame('Updated '.$slug, $stored['updated_at']);
            $this->assertSame(
                [
                    ['heading' => $marker, 'body' => ['First paragraph.', 'Second paragraph.']],
                    ['heading' => $second, 'body' => ['Single paragraph body.']],
                ],
                $stored['sections']
            );
            $this->assertSame(1, Setting::where('key', $key)->count());

            // Reloading the CMS page keeps the saved values, including every
            // repeatable row (Filament Repeater items use UUID keys, so match
            // by content, not by array index).
            $reload = Livewire::actingAs($this->admin())->test($page);

            $this->assertSame('Saved Title '.$slug, $reload->get('data.title'));
            $sections = collect($reload->get('data.sections'));
            $this->assertCount(2, $sections);
            $this->assertSame([$marker, $second], $sections->pluck('heading')->values()->all());
            $this->assertSame("First paragraph.\n\nSecond paragraph.", $sections->first()['body']);

            // The public resolver and the public page use the saved override.
            $defaults = StaticPageController::defaultContent($slug);
            $resolved = LegalPagesContent::resolve($slug, $defaults);

            $this->assertSame('Saved Title '.$slug, $resolved['title']);
            $this->assertSame($marker, $resolved['sections'][0]['heading']);

            $this->get(route('pages.show', $slug))
                ->assertOk()
                ->assertSee($marker)
                ->assertSee('First paragraph.')
                ->assertSee('Second paragraph.');
        }
    }

    public function test_default_checkout_copy_appears_on_course_and_product_pages(): void
    {
        $course = $this->course(9500);
        $product = $this->product(299);

        $this->get(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->assertOk()
            ->assertSee('Pay for your course with bKash or Nagad');

        $this->get(route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]))
            ->assertOk()
            ->assertSee('Pay for your digital product with bKash or Nagad');
    }

    public function test_cms_checkout_copy_can_be_edited_but_price_and_payment_logic_stay_server_controlled(): void
    {
        $course = $this->course(9500);
        $product = $this->product(299);

        Livewire::actingAs($this->admin())
            ->test(CheckoutPageCms::class)
            ->fillForm(['checkout_intro' => 'Custom guidance for {item} purchases.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->assertOk()
            ->assertSee('Custom guidance for course purchases.')
            ->assertSee('৳9,500');

        $this->get(route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]))
            ->assertOk()
            ->assertSee('Custom guidance for digital product purchases.')
            ->assertSee('৳299');

        // The stored CMS value is only the informational string.
        $this->assertSame('Custom guidance for {item} purchases.', Setting::where('key', 'checkout_intro_help')->value('value'));
        $this->assertSame(1, Setting::where('key', 'checkout_intro_help')->count());
    }

    public function test_cms_content_cannot_change_the_authoritative_price(): void
    {
        $course = $this->course(9500);

        Setting::updateOrCreate(['key' => 'checkout_intro_help'], ['value' => 'Trying to change {item} price to 1']);

        $response = $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), [
            'student_name' => 'Buyer',
            'student_email' => 'buyer-'.uniqid().'@example.com',
            'student_phone' => '01712345678',
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
            'amount' => 1,
        ]);

        $this->assertDatabaseHas('orders', [
            'legacy_source' => 'enrollment',
            'total_amount' => '9500.00',
        ]);

        $this->assertDatabaseMissing('orders', ['total_amount' => '1.00']);
        $this->assertTrue($response->isRedirect());
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_admin' => true]);
    }

    protected function course(int $price): Course
    {
        return Course::create([
            'title' => 'Course '.uniqid(),
            'slug' => 'course-'.uniqid(),
            'price' => $price,
            'is_published' => true,
        ]);
    }

    protected function product(int $price): Product
    {
        return Product::create([
            'title' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'price' => $price,
            'file_path' => 'products/test.pdf',
            'category' => 'Tests',
            'is_published' => true,
        ]);
    }
}
