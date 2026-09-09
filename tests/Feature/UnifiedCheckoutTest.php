<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutOrderWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UnifiedCheckoutTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------
    // Course checkout
    // -------------------------------------------------------------

    public function test_guest_can_open_the_paid_course_checkout(): void
    {
        $course = $this->course(9500);

        $this->get(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->assertOk()
            ->assertSee($course->title)
            ->assertSee('Full Name')
            ->assertSee('Transaction ID');
    }

    public function test_course_checkout_page_displays_for_a_logged_in_student(): void
    {
        $user = $this->student();
        $course = $this->course(9500);

        $this->actingAs($user)
            ->get(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->assertOk()
            ->assertSee($course->title)
            ->assertSee('৳9,500')
            ->assertSee('Transaction ID')
            ->assertSee($user->email);
    }

    public function test_guest_paid_course_purchase_creates_account_and_all_records(): void
    {
        $course = $this->course(9500);
        $email = 'guest-'.Str::lower(Str::random(6)).'@example.com';

        $this->assertRedirectToCourseConfirmation(
            $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('Guest Buyer', $email))
        );

        $this->assertSame(1, User::where('email', $email)->count());

        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame('Guest Buyer', $user->name);
        $this->assertSame('01712345678', $user->phone);
        $this->assertStringStartsWith('$2y$', $user->password);

        $enrollment = Enrollment::firstOrFail();
        $this->assertSame($user->id, $enrollment->user_id);
        $this->assertSame($email, $enrollment->student_email);
        $this->assertSame('pending', $enrollment->payment_status);

        $order = Order::where('legacy_source', 'enrollment')->firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame($email, $order->student_email);
        $this->assertSame('9500.00', $order->total_amount);
        $this->assertSame(Course::class, $order->items()->firstOrFail()->purchasable_type);
        $this->assertSame($course->id, $order->items()->firstOrFail()->purchasable_id);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame('bkash', $payment->method);
        $this->assertSame('9JQ2A3B4C5', $payment->trx_reference);
        $this->assertSame('01712345678', $payment->sender_number);
        $this->assertSame('pending', $payment->status);

        $this->assertDatabaseHas('leads', [
            'user_id' => $user->id,
            'source' => Lead::SOURCE_CHECKOUT,
        ]);
    }

    public function test_existing_user_is_reused_for_guest_course_purchase(): void
    {
        $existing = $this->student();
        $course = $this->course(9500);

        $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('Existing Buyer', $existing->email));

        $this->assertSame(1, User::where('email', $existing->email)->count());
        $this->assertSame($existing->id, Enrollment::firstOrFail()->user_id);
    }

    public function test_duplicate_paid_course_purchase_remains_blocked(): void
    {
        $user = $this->student();
        $course = $this->course(9500);
        $payload = $this->payload('Same Buyer', $user->email);

        $this->assertRedirectToCourseConfirmation(
            $this->actingAs($user)->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $payload)
        );

        $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $payload)
            ->assertRedirect(route('courses.show', $course->slug));

        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_course_purchase_rejects_invalid_payment_and_buyer_data(): void
    {
        $course = $this->course(9500);

        $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), [
            'student_name' => '',
            'student_email' => 'bad',
            'student_phone' => '123',
            'payment_method' => 'cash',
            'transaction_id' => 'short',
            'sender_number' => '123',
        ])->assertSessionHasErrors(['student_name', 'student_email', 'student_phone', 'payment_method', 'transaction_id', 'sender_number']);

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_course_rolls_back_when_canonical_creation_fails(): void
    {
        $course = $this->course(9500);
        $email = 'rollback-'.Str::lower(Str::random(6)).'@example.com';

        $this->app->instance(CheckoutOrderWriter::class, new class extends CheckoutOrderWriter
        {
            public function recordEnrollmentSale(Enrollment $enrollment): Order
            {
                throw new \RuntimeException('canonical write failed');
            }
        });

        $this->from(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('Rollback Buyer', $email))
            ->assertSessionHasErrors('checkout');

        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_price_zero_course_cannot_use_paid_checkout(): void
    {
        $user = $this->student();
        $course = $this->course(0);

        $coursePage = route('courses.show', $course->slug);

        // Guest first (a later actingAs would keep the session).
        $this->get(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->assertRedirect(route('login', ['redirect' => $coursePage]));

        $this->actingAs($user)
            ->get(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))
            ->assertRedirect($coursePage);
    }

    // -------------------------------------------------------------
    // Digital checkout
    // -------------------------------------------------------------

    public function test_guest_can_open_and_submit_the_paid_product_checkout(): void
    {
        $product = $this->product(299);
        $email = 'guest-digital-'.Str::lower(Str::random(6)).'@example.com';

        $this->get(route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]))
            ->assertOk()
            ->assertSee($product->title)
            ->assertSee('Full Name');

        $this->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), $this->payload('Digital Guest', $email))
            ->assertRedirect(route('shop.thank-you'))
            ->assertSessionHas('order_id');

        $user = User::where('email', $email)->firstOrFail();
        $order = Order::where('legacy_source', 'digital_order')->firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('299.00', $order->total_amount);
        $this->assertSame(Product::class, $order->items()->firstOrFail()->purchasable_type);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame('bkash', $payment->method);
        $this->assertSame('9JQ2A3B4C5', $payment->trx_reference);
        $this->assertSame('01712345678', $payment->sender_number);

        $this->assertSame($user->id, DigitalOrder::firstOrFail()->user_id);
    }

    public function test_existing_user_is_reused_for_guest_product_purchase(): void
    {
        $existing = $this->student();
        $product = $this->product(299);

        $this->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), $this->payload('Digital Existing', $existing->email));

        $this->assertSame(1, User::where('email', $existing->email)->count());
        $this->assertSame($existing->id, DigitalOrder::firstOrFail()->user_id);
        $this->assertSame($existing->id, Order::firstOrFail()->user_id);
    }

    public function test_double_digital_submission_creates_two_independent_orders_for_the_same_user(): void
    {
        $existing = $this->student();
        $product = $this->product(299);
        $payload = $this->payload('Twice Buyer', $existing->email);

        $this->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), $payload)->assertSessionHas('order_id');
        $this->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), $payload)->assertSessionHas('order_id');

        $this->assertSame(1, User::count());
        $this->assertDatabaseCount('digital_orders', 2);
        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_price_zero_product_cannot_use_paid_checkout(): void
    {
        $product = $this->product(0);

        $this->get(route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]))
            ->assertRedirect(route('shop.index'));

        $this->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), $this->payload('Free Buyer', 'free@example.com'))
            ->assertRedirect(route('shop.index'));
    }

    public function test_unpublished_course_and_product_are_rejected(): void
    {
        $course = $this->course(9500, false);
        $product = $this->product(299, false);

        $this->get(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]))->assertNotFound();
        $this->get(route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]))->assertNotFound();
        $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('A', 'a@example.com'))->assertNotFound();
        $this->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), $this->payload('A', 'a@example.com'))->assertNotFound();
    }

    // -------------------------------------------------------------
    // Price integrity, security, redirects, types
    // -------------------------------------------------------------

    public function test_client_submitted_amount_never_changes_the_order_total(): void
    {
        $course = $this->course(9500);
        $product = $this->product(299);
        $payload = $this->payload('Hacker', 'hacker@example.com');
        $payload['amount'] = 1;
        $payload['total_amount'] = 1;

        $this->assertRedirectToCourseConfirmation(
            $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $payload)
        );

        $this->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), $payload)
            ->assertRedirect(route('shop.thank-you'));

        $this->assertSame('9500.00', Order::where('legacy_source', 'enrollment')->firstOrFail()->total_amount);
        $this->assertSame('299.00', Order::where('legacy_source', 'digital_order')->firstOrFail()->total_amount);
    }

    public function test_credentials_are_never_exposed_in_responses(): void
    {
        $course = $this->course(9500);
        $email = 'noexpose-'.Str::lower(Str::random(6)).'@example.com';

        $response = $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('Secret Buyer', $email));

        $this->assertRedirectToCourseConfirmation($response);
        $this->assertStringNotContainsString('Password', $response->getContent());
    }

    public function test_invalid_purchasable_type_is_rejected(): void
    {
        $this->get(url('/checkout/car/anything'))->assertNotFound();
        $this->post(url('/checkout/car/anything'))->assertNotFound();
    }

    public function test_old_checkout_urls_redirect_to_the_unified_checkout(): void
    {
        $course = $this->course(9500);
        $product = $this->product(299);

        $this->get(route('checkout.show', $course->slug))
            ->assertRedirect(route('checkout.unified', ['type' => 'course', 'slug' => $course->slug]));

        $this->get(route('shop.checkout', $product->slug))
            ->assertRedirect(route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]));
    }

    public function test_digital_validation_errors_are_displayed_inline(): void
    {
        $product = $this->product(299);

        $this->followingRedirects()
            ->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), [
                'student_name' => '',
                'student_email' => 'not-an-email',
                'student_phone' => '123',
                'payment_method' => 'bkash',
                'transaction_id' => 'short',
                'sender_number' => '123',
            ])
            ->assertOk()
            ->assertSee('Please enter your full name.')
            ->assertSee('Please enter a valid email address.')
            ->assertSee('must be a valid Bangladeshi phone number');
    }

    // -------------------------------------------------------------
    // Order-received email + markers
    // -------------------------------------------------------------

    public function test_course_unified_checkout_queues_exactly_one_order_received_email_and_syncs_markers(): void
    {
        $this->seedEmailInfrastructure();
        User::factory()->create(['email' => 'course-existing@example.com', 'email_verified_at' => now()]);
        $course = $this->course(9500);

        Queue::fake([SendEmailJob::class]);

        $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('Course Buyer', 'course-existing@example.com'));

        $this->assertSame(1, EmailLog::where('template_key', 'order_received_payment_pending')->count());
        Queue::assertPushed(SendEmailJob::class, 1);

        $legacyMarker = DB::table('enrollments')->value('order_received_email_sent_at');
        $canonicalMarker = DB::table('orders')->value('order_received_email_sent_at');

        $this->assertNotNull($legacyMarker);
        $this->assertNotNull($canonicalMarker);
        $this->assertSame($legacyMarker, $canonicalMarker);
    }

    public function test_digital_unified_checkout_queues_exactly_one_order_received_email_and_syncs_markers(): void
    {
        $this->seedEmailInfrastructure();
        User::factory()->create(['email' => 'digital-existing@example.com', 'email_verified_at' => now()]);
        $product = $this->product(299);

        Queue::fake([SendEmailJob::class]);

        $this->post(route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]), $this->payload('Digital Buyer', 'digital-existing@example.com'));

        $this->assertSame(1, EmailLog::where('template_key', 'order_received_payment_pending')->count());
        Queue::assertPushed(SendEmailJob::class, 1);

        $legacyMarker = DB::table('digital_orders')->value('order_received_email_sent_at');
        $canonicalMarker = DB::table('orders')->value('order_received_email_sent_at');

        $this->assertNotNull($legacyMarker);
        $this->assertNotNull($canonicalMarker);
        $this->assertSame($legacyMarker, $canonicalMarker);
    }

    // -------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------

    protected function payload(string $name, string $email): array
    {
        return [
            'student_name' => $name,
            'student_email' => $email,
            'student_phone' => '01712345678',
            'payment_method' => 'bkash',
            'transaction_id' => '9JQ2A3B4C5',
            'sender_number' => '01712345678',
        ];
    }

    protected function seedEmailInfrastructure(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);
        EmailTemplate::factory()->create([
            'key' => 'order_received_payment_pending',
            'subject' => 'Order received',
            'body' => '<p>We received your order.</p>',
            'variables' => ['student_name', 'order_number', 'product_title', 'amount', 'order_url'],
        ]);
    }

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
            'email' => 'student-'.Str::lower(Str::random(6)).'@example.com',
            'phone' => '01712345678',
        ]);
    }

    protected function course(int $price, bool $published = true): Course
    {
        return Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => $price,
            'is_published' => $published,
        ]);
    }

    protected function product(int $price, bool $published = true): Product
    {
        return Product::create([
            'title' => 'HSK Vocabulary PDF',
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => $price,
            'file_path' => 'products/hsk-vocabulary.pdf',
            'is_published' => $published,
        ]);
    }

    /**
     * Assert a course checkout redirects to the signed public confirmation
     * page (path prefix + signature), never to the authenticated route.
     */
    protected function assertRedirectToCourseConfirmation(TestResponse $response): void
    {
        $response->assertRedirect();

        $location = (string) $response->headers->get('Location');

        $this->assertStringStartsWith(url('/checkout/confirmation/'), $location);
        $this->assertStringContainsString('signature=', $location);
    }
}
