<?php

namespace App\Services;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Small, focused checkout entry point used by the unified checkout page for
 * courses and digital products. Paid purchases are fully guest-accessible.
 *
 * Responsibilities:
 *  - resolving a purchasable by type + slug and rejecting unpublished items,
 *  - computing the authoritative price from the purchasable model,
 *  - finding or creating the customer account by normalized email inside the
 *    purchase transaction (never creating duplicate users),
 *  - creating the legacy fulfillment row and the canonical
 *    Order → OrderItem → Payment in one transaction,
 *  - returning the sale + customer so the controller can run lead capture,
 *    order-received email, and secure account onboarding.
 *
 * Prices are never trusted from the client; nothing here decides payment
 * outcomes; PaymentReviewService and OrderMaterializer are never duplicated.
 */
class UnifiedCheckoutService
{
    public const TYPE_COURSE = 'course';

    public const TYPE_PRODUCT = 'product';

    /**
     * Resolve a purchasable type + slug to a publishable item.
     *
     * @return array{type: string, purchasable: Course|Product, price: float}|null
     */
    public function resolve(string $type, string $slug): ?array
    {
        $purchasable = match ($type) {
            self::TYPE_COURSE => Course::query()->where('slug', $slug)->where('is_published', true)->first(),
            self::TYPE_PRODUCT => Product::query()->where('slug', $slug)->where('is_published', true)->first(),
            default => null,
        };

        if (! $purchasable instanceof Course && ! $purchasable instanceof Product) {
            return null;
        }

        return [
            'type' => $type,
            'purchasable' => $purchasable,
            'price' => (float) $purchasable->price,
        ];
    }

    /**
     * Begin a paid course purchase: find-or-create the customer, create the
     * legacy Enrollment, and create the canonical Order → OrderItem → Payment,
     * all in one transaction.
     *
     * @param  array{name: string, email: string, phone: string, payment_method: string, transaction_id: string, sender_number: string}  $buyer
     * @return array{sale: Enrollment, user: User, created: bool}
     *
     * @throws Throwable when nothing can be persisted (transaction rolls back)
     */
    public function beginCoursePurchase(Course $course, array $buyer): array
    {
        return DB::transaction(function () use ($course, $buyer): array {
            [$user, $created] = $this->findOrCreateCustomer(
                $buyer['name'],
                $buyer['email'],
                $buyer['phone'],
            );

            $alreadyEnrolled = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->exists();

            if ($alreadyEnrolled) {
                throw new \RuntimeException('already-enrolled');
            }

            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'student_name' => $buyer['name'],
                'student_email' => $buyer['email'],
                'student_phone' => $buyer['phone'],
                'amount' => $course->price,
                'amount_paid' => 0,
                'amount_due' => $course->price,
                'payment_status' => Enrollment::PAYMENT_STATUS_PENDING,
                'enrollment_status' => 'pending',
                'status' => 'pending',
                'price_paid' => $course->price,
                'payment_method' => $buyer['payment_method'],
                'transaction_id' => $buyer['transaction_id'],
                'sender_number' => $buyer['sender_number'],
            ]);

            app(CheckoutOrderWriter::class)->recordEnrollmentSale($enrollment);

            return ['sale' => $enrollment, 'user' => $user, 'created' => $created];
        });
    }

    /**
     * Begin a paid digital product purchase: find-or-create the customer,
     * create the legacy DigitalOrder, and create the canonical
     * Order → OrderItem → Payment, all in one transaction.
     *
     * @param  array{name: string, email: string, phone: string, payment_method: string, transaction_id: string, sender_number: string}  $buyer
     * @return array{sale: DigitalOrder, user: User, created: bool}
     */
    public function beginDigitalPurchase(Product $product, array $buyer): array
    {
        return DB::transaction(function () use ($product, $buyer): array {
            [$user, $created] = $this->findOrCreateCustomer(
                $buyer['name'],
                $buyer['email'],
                $buyer['phone'],
            );

            $order = DigitalOrder::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'student_name' => $buyer['name'],
                'student_email' => $buyer['email'],
                'student_phone' => $buyer['phone'],
                'trx_id' => $buyer['transaction_id'],
                'amount' => $product->price,
                'status' => DigitalOrder::STATUS_PENDING,
            ]);

            app(CheckoutOrderWriter::class)->recordDigitalOrderSale($order, [
                'method' => $buyer['payment_method'],
                'sender_number' => $buyer['sender_number'],
            ]);

            return ['sale' => $order, 'user' => $user, 'created' => $created];
        });
    }

    /**
     * Find the user by normalized (case-insensitive) email, or create exactly
     * one new user with a secure random credential. The plaintext credential
     * is only used by the hashed cast and never returned, logged, or exposed.
     *
     * @return array{0: User, 1: bool}
     */
    protected function findOrCreateCustomer(string $name, string $email, string $phone): array
    {
        $normalized = Str::lower(trim($email));

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalized])
            ->first();

        if ($user !== null) {
            return [$user, false];
        }

        $user = User::create([
            'name' => trim($name),
            'email' => $normalized,
            'phone' => $phone,
            'password' => Str::password(24),
        ]);

        return [$user, true];
    }
}
