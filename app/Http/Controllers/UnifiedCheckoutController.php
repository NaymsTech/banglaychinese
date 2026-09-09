<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Rules\BangladeshiPhone;
use App\Services\DigitalOrderApprovalService;
use App\Services\EnrollmentApprovalService;
use App\Services\LeadCaptureService;
use App\Services\SettingsService;
use App\Services\UnifiedCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Throwable;

/**
 * The single customer-facing checkout for paid courses and digital products.
 * Paid checkout is fully guest-accessible; the customer account is found or
 * created by normalized email inside the purchase transaction, and the
 * submitted buyer snapshot is always retained on the canonical Order.
 *
 * The amount is always taken from the purchasable model server-side. Legacy
 * fulfillment rows (Enrollment / DigitalOrder) are linked to the found/created
 * user and created together with the canonical Order → OrderItem → Payment in
 * one transaction.
 */
class UnifiedCheckoutController extends Controller
{
    /**
     * Default informational intro shown on both course and product checkout.
     * {item} is replaced at render time with the lowercase item type. This
     * copy can be overridden in Filament → Content Management → Checkout Page
     * CMS; it can never affect pricing or payment behaviour.
     */
    public const CHECKOUT_INTRO_DEFAULT = 'Pay for your {item} with bKash or Nagad, then our team verifies your payment and confirms your order.';

    public function show(Request $request, UnifiedCheckoutService $checkout, string $type, string $slug): View|RedirectResponse
    {
        $item = $checkout->resolve($type, $slug);

        if ($item === null) {
            abort(404);
        }

        /** @var Course|Product $purchasable */
        $purchasable = $item['purchasable'];
        $price = $item['price'];

        if ($price <= 0) {
            return $this->freeItemRedirect($type, $purchasable, $request);
        }

        $typeLabel = $type === UnifiedCheckoutService::TYPE_COURSE ? 'Course' : 'Digital Product';

        $checkoutIntro = str_replace(
            '{item}',
            strtolower($typeLabel),
            (string) (Setting::where('key', 'checkout_intro_help')->value('value') ?: self::CHECKOUT_INTRO_DEFAULT)
        );

        return view('checkout.unified', [
            'type' => $type,
            'typeLabel' => $typeLabel,
            'purchasable' => $purchasable,
            'price' => $price,
            'displayPrice' => $this->formatPrice($price),
            'bkashNumber' => SettingsService::get('bkash_number'),
            'nagadNumber' => SettingsService::get('nagad_number'),
            'whatsappNumber' => SettingsService::get('whatsapp_number', '8618223249514'),
            'checkoutIntro' => $checkoutIntro,
            'metaTitle' => $purchasable->title.' | Checkout',
        ]);
    }

    public function store(Request $request, UnifiedCheckoutService $checkout, string $type, string $slug): RedirectResponse
    {
        $item = $checkout->resolve($type, $slug);

        if ($item === null) {
            abort(404);
        }

        /** @var Course|Product $purchasable */
        $purchasable = $item['purchasable'];

        if ((float) $purchasable->price <= 0) {
            return $this->freeItemRedirect($type, $purchasable, $request);
        }

        $validated = $this->validatePurchase($request);
        $buyer = [
            'name' => $validated['student_name'],
            'email' => $validated['student_email'],
            'phone' => $validated['student_phone'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'],
            'sender_number' => $validated['sender_number'],
        ];

        try {
            if ($type === UnifiedCheckoutService::TYPE_COURSE) {
                $result = $checkout->beginCoursePurchase($purchasable, $buyer);
                /** @var Enrollment $sale */
                $sale = $result['sale'];

                $this->captureLead($result['user'], $buyer, UnifiedCheckoutService::TYPE_COURSE, $purchasable);
                app(EnrollmentApprovalService::class)->notifyOrderReceived($sale);
                $this->onboardNewCustomer($result);

                // Guests must be able to see the outcome without logging in:
                // a short-lived signed URL is issued only after the purchase
                // has committed, and the page is display-only.
                return redirect(URL::temporarySignedRoute(
                    'checkout.course.confirmation',
                    now()->addMinutes(60),
                    ['enrollment' => $sale->getKey()],
                ));
            }

            $result = $checkout->beginDigitalPurchase($purchasable, $buyer);
            /** @var DigitalOrder $sale */
            $sale = $result['sale'];

            $this->captureLead($result['user'], $buyer, UnifiedCheckoutService::TYPE_PRODUCT, $purchasable);
            app(DigitalOrderApprovalService::class)->notifyOrderReceived($sale);
            $this->onboardNewCustomer($result);

            return redirect()->route('shop.thank-you')
                ->with('product_title', $purchasable->title)
                ->with('order_id', $sale->id)
                ->with('student_email', $sale->student_email);
        } catch (Throwable $exception) {
            Log::warning('Unified checkout failed.', [
                'type' => $type,
                'purchasable_id' => $purchasable->getKey(),
                'error' => $exception->getMessage(),
            ]);

            if ($type === UnifiedCheckoutService::TYPE_COURSE && $exception->getMessage() === 'already-enrolled') {
                return redirect()->route('courses.show', $purchasable->slug)
                    ->with('status', 'আপনি ইতিমধ্যে এই কোর্সে এনরোল করেছেন।');
            }

            return back()->withInput()->withErrors(['checkout' => 'অর্ডারটি সম্পন্ন করা যায়নি। আবার চেষ্টা করুন।']);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function validatePurchase(Request $request): array
    {
        return $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'student_email' => ['required', 'email', 'max:255'],
            'student_phone' => ['required', new BangladeshiPhone],
            'payment_method' => ['required', 'in:bkash,nagad'],
            'transaction_id' => ['required', 'string', 'min:8', 'max:64'],
            'sender_number' => ['required', new BangladeshiPhone],
        ], $this->validationMessages());
    }

    /**
     * @return array<string, string>
     */
    protected function validationMessages(): array
    {
        return [
            'payment_method.required' => 'Please choose a payment method (bKash or Nagad).',
            'payment_method.in' => 'Only bKash or Nagad are accepted at checkout.',
            'transaction_id.required' => 'Please enter the Transaction ID from your payment app.',
            'transaction_id.min' => 'The Transaction ID must be at least 8 characters.',
            'transaction_id.max' => 'The Transaction ID must be at most 64 characters.',
            'sender_number.required' => 'Please enter the mobile number you paid from.',
            'student_name.required' => 'Please enter your full name.',
            'student_email.required' => 'Please enter your email address.',
            'student_email.email' => 'Please enter a valid email address.',
            'student_phone.required' => 'Please enter your mobile number (WhatsApp).',
        ];
    }

    /**
     * New accounts receive the existing secure onboarding emails only: a
     * password-reset link (to set a password) and the standard email
     * verification mail. No plaintext credentials are ever generated into
     * responses or logs.
     *
     * @param  array{sale: object, user: User, created: bool}  $result
     */
    protected function onboardNewCustomer(array $result): void
    {
        if (! $result['created']) {
            return;
        }

        try {
            Password::broker()->sendResetLink(['email' => $result['user']->email]);
            $result['user']->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::warning('Customer onboarding email could not be queued.', [
                'user_id' => $result['user']->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  array{name: string, email: string, phone: string}  $buyer
     */
    protected function captureLead(User $user, array $buyer, string $type, Course|Product $purchasable): void
    {
        app(LeadCaptureService::class)->capture([
            'user_id' => $user->id,
            'email' => $buyer['email'],
            'name' => $buyer['name'],
            'whatsapp_number' => $buyer['phone'],
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => $type === UnifiedCheckoutService::TYPE_COURSE
                ? Lead::INTEREST_COURSES
                : Lead::INTEREST_DIGITAL_PRODUCTS,
            'notes' => ($type === UnifiedCheckoutService::TYPE_COURSE ? 'Course: ' : 'Product: ').$purchasable->title,
        ]);
    }

    /**
     * Display-only confirmation for a completed paid course purchase.
     *
     * The route is signed and unguessable; the enrollment id alone grants
     * nothing. Only the minimum safe information is rendered — course title,
     * total, order reference and review state. No sender number, transaction
     * reference, payment history or account-existence detail is exposed, and
     * nothing is written or emailed from this page.
     */
    public function courseConfirmation(Enrollment $enrollment): View
    {
        $enrollment->loadMissing(['course', 'unifiedOrder']);

        $order = $enrollment->unifiedOrder;
        $amount = (float) ($order?->total_amount ?? $enrollment->amount);
        $reviewStatus = $order?->reviewStatus() ?? (string) $enrollment->payment_status;

        return view('checkout.course-confirmation', [
            'courseTitle' => $enrollment->course?->title ?? 'your course',
            'orderNumber' => $enrollment->getKey(),
            'displayAmount' => '৳ '.number_format($amount, 2),
            'reviewStatus' => $reviewStatus,
            'metaTitle' => 'Order Submitted | Banglay Chinese',
        ]);
    }

    protected function freeItemRedirect(string $type, Course|Product $purchasable, Request $request): RedirectResponse
    {
        if ($type === UnifiedCheckoutService::TYPE_COURSE) {
            if (! $request->user()) {
                return redirect()->guest(route('login', ['redirect' => route('courses.show', $purchasable->slug)]));
            }

            return redirect()->route('courses.show', $purchasable->slug);
        }

        // There is no free-product fulfillment mechanism; paid checkout must
        // not be used for price-0 products.
        return redirect()->route('shop.index');
    }

    protected function formatPrice(float $price): string
    {
        $decimals = $price === floor($price) ? 0 : 2;

        return number_format($price, $decimals);
    }
}
