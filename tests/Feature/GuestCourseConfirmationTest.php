<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\Course;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Stage 6C: a guest paid-course purchase lands on a safe, signed, public
 * confirmation page — no login wall, no sensitive data, no replay side
 * effects — while existing-user onboarding and digital checkout stay intact.
 */
class GuestCourseConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_purchase_a_paid_course_and_view_the_public_confirmation(): void
    {
        $course = $this->course(9500);
        $email = 'guest-'.Str::lower(Str::random(6)).'@example.com';

        $response = $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('Guest Buyer', $email));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith(url('/checkout/confirmation/'), $location);

        // The confirmation page is public: no authentication required.
        $this->get($location)
            ->assertOk()
            ->assertSee($course->title)
            ->assertSee('৳ 9,500.00')
            ->assertSee('#'.Enrollment::firstOrFail()->id)
            ->assertSee('পেমেন্ট যাচাই চলছে');
    }

    public function test_confirmation_does_not_expose_sensitive_payment_fields(): void
    {
        $course = $this->course(9500);
        $email = 'guest-'.Str::lower(Str::random(6)).'@example.com';

        $response = $this->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('Secret Buyer', $email));
        $location = (string) $response->headers->get('Location');

        $html = $this->get($location)->assertOk()->getContent();

        $this->assertStringNotContainsString('9JQ2A3B4C5', $html);       // transaction reference
        $this->assertStringNotContainsString('01712345678', $html);      // sender number / phone
        $this->assertStringNotContainsString($email, $html);             // buyer email
        $this->assertStringNotContainsString('reset-password', $html);   // no password-set link/token
    }

    public function test_missing_or_invalid_signature_cannot_open_confirmation(): void
    {
        $course = $this->course(9500);
        $guest = $this->guestPurchase($course);

        // No signature at all.
        $this->get(route('checkout.course.confirmation', ['enrollment' => $guest->id]))
            ->assertForbidden();

        // Valid signature for THIS enrollment cannot be replayed on another order.
        $url = URL::temporarySignedRoute('checkout.course.confirmation', now()->addMinutes(60), ['enrollment' => $guest->id]);
        $query = parse_url($url, PHP_URL_QUERY);

        $other = $this->enrollmentFor($this->course(5000));

        $this->get(route('checkout.course.confirmation', ['enrollment' => $other->id]).'?'.$query)
            ->assertForbidden();

        // A non-existent enrollment id fails safely (404 via route binding).
        $this->get(URL::temporarySignedRoute('checkout.course.confirmation', now()->addMinutes(60), ['enrollment' => 9999999]))
            ->assertNotFound();
    }

    public function test_expired_confirmation_link_fails_safely(): void
    {
        $course = $this->course(9500);
        $guest = $this->guestPurchase($course);

        $expired = URL::temporarySignedRoute('checkout.course.confirmation', now()->subMinutes(5), ['enrollment' => $guest->id]);

        $this->get($expired)->assertForbidden();
    }

    public function test_confirmation_is_display_only_and_replay_creates_nothing(): void
    {
        $course = $this->course(9500);
        $email = 'guest-'.Str::lower(Str::random(6)).'@example.com';

        $location = (string) $this->post(
            route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]),
            $this->payload('Replay Buyer', $email)
        )->headers->get('Location');

        $this->get($location)->assertOk();
        $this->get($location)->assertOk();
        $this->get($location)->assertOk();

        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('email_logs', 0); // nothing is sent from the page
    }

    public function test_existing_user_is_reused_without_disclosing_account_status(): void
    {
        $existing = $this->student();
        $course = $this->course(9500);

        $this->post(
            route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]),
            $this->payload('Existing Buyer', $existing->email)
        );

        $this->assertSame(1, User::where('email', $existing->email)->count());
        $this->assertSame($existing->id, Enrollment::firstOrFail()->user_id);

        $url = $this->confirmationUrl(Enrollment::firstOrFail());

        $html = $this->get($url)->assertOk()->getContent();

        // Neutral language only — never reveals that an account already existed.
        $this->assertStringNotContainsString('already had an account', $html);
        $this->assertStringNotContainsString('আগে থেকে', $html);
    }

    public function test_new_guest_still_receives_the_existing_onboarding_emails(): void
    {
        EmailProvider::factory()->create(['name' => 'Brevo']);

        EmailTemplate::factory()->create([
            'key' => 'order_received_payment_pending',
            'subject' => 'Order received',
            'body' => '<p>Hi {student_name}, order {order_number}: {product_title} — {amount}. {order_url}</p>',
            'variables' => ['student_name', 'order_number', 'product_title', 'amount', 'order_url'],
            'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
        ]);

        foreach (['password_reset', 'email_verification'] as $key) {
            EmailTemplate::factory()->create([
                'key' => $key,
                'subject' => 'Action needed',
                'body' => '<p>Hi {name}, visit {url} within {expireMinutes} minutes on {appName}.</p>',
                'variables' => ['name', 'appName', 'url', 'expireMinutes'],
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
            ]);
        }

        $course = $this->course(9500);
        $email = 'guest-'.Str::lower(Str::random(6)).'@example.com';

        Queue::fake([SendEmailJob::class]);

        $this->post(
            route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]),
            $this->payload('Onboarding Buyer', $email)
        )->assertRedirect();

        Queue::assertPushed(SendEmailJob::class, 3);

        $keys = EmailLog::query()->pluck('template_key')->sort()->values()->all();
        $this->assertSame(['email_verification', 'order_received_payment_pending', 'password_reset'], $keys);
    }

    public function test_logged_in_course_purchase_keeps_working(): void
    {
        $user = $this->student();
        $course = $this->course(9500);

        $this->actingAs($user)
            ->post(route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]), $this->payload('Logged In', $user->email))
            ->assertRedirect();

        $enrollment = Enrollment::firstOrFail();
        $this->assertSame($user->id, $enrollment->user_id);

        // The signed confirmation is public for logged-in buyers too.
        $this->get($this->confirmationUrl($enrollment))->assertOk();

        // Existing dashboard behavior is untouched.
        $this->actingAs($user)->get(route('dashboard.index'))->assertOk();
    }

    public function test_guest_digital_checkout_still_uses_its_public_thank_you(): void
    {
        $product = $this->product(299);
        $email = 'guest-'.Str::lower(Str::random(6)).'@example.com';

        $this->post(
            route('checkout.unified.store', ['type' => 'product', 'slug' => $product->slug]),
            $this->payload('Digital Guest', $email)
        )->assertRedirect(route('shop.thank-you'));

        $this->get(route('shop.thank-you'))
            ->assertOk()
            ->assertSee($product->title);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

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

    protected function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
            'email' => 'student-'.Str::lower(Str::random(6)).'@example.com',
            'phone' => '01712345678',
        ]);
    }

    protected function course(int $price): Course
    {
        return Course::create([
            'title' => 'Course '.Str::random(3),
            'slug' => 'course-'.Str::lower(Str::random(8)),
            'price' => $price,
            'is_published' => true,
        ]);
    }

    protected function product(int $price): Product
    {
        return Product::create([
            'title' => 'Practice Tests',
            'slug' => 'product-'.Str::lower(Str::random(8)),
            'price' => $price,
            'file_path' => 'products/hsk1.pdf',
            'category' => 'Practice Tests',
            'is_published' => true,
        ]);
    }

    protected function guestPurchase(Course $course): Enrollment
    {
        $this->post(
            route('checkout.unified.store', ['type' => 'course', 'slug' => $course->slug]),
            $this->payload('Buyer', 'guest-'.Str::lower(Str::random(6)).'@example.com')
        )->assertRedirect();

        return Enrollment::firstOrFail();
    }

    protected function enrollmentFor(Course $course): Enrollment
    {
        $user = $this->student();

        return Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => 'Other Buyer',
            'student_email' => $user->email,
            'student_phone' => '01812345678',
            'amount' => 5000,
            'amount_paid' => 0,
            'amount_due' => 5000,
            'payment_status' => 'pending',
            'enrollment_status' => 'pending',
        ]);
    }

    protected function confirmationUrl(Enrollment $enrollment): string
    {
        return URL::temporarySignedRoute('checkout.course.confirmation', now()->addMinutes(60), ['enrollment' => $enrollment->getKey()]);
    }
}
