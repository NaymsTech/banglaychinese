<?php

namespace App\Http\Controllers;

use App\Models\DigitalOrder;
use App\Models\Lead;
use App\Models\Product;
use App\Rules\BangladeshiPhone;
use App\Services\CheckoutOrderWriter;
use App\Services\DigitalOrderApprovalService;
use App\Services\LeadCaptureService;
use App\Services\UnifiedCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DigitalCheckoutController extends Controller
{
    /**
     * The old shop checkout URL now points to the unified checkout page.
     */
    public function checkout(Product $product): RedirectResponse
    {
        abort_unless($product->is_published, 404);

        return redirect()->route('checkout.unified', [
            'type' => UnifiedCheckoutService::TYPE_PRODUCT,
            'slug' => $product->slug,
        ]);
    }

    /**
     * Register a pending order once the buyer has sent the manual payment.
     */
    public function store(Request $request, Product $product, LeadCaptureService $leads): RedirectResponse
    {
        abort_unless($product->is_published, 404);

        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'student_email' => ['required', 'email', 'max:255'],
            'student_phone' => ['required', new BangladeshiPhone],
            'trx_id' => ['required', 'string', 'max:255'],
        ]);

        $order = DB::transaction(function () use ($request, $product, $validated): DigitalOrder {
            $order = DigitalOrder::create([
                'user_id' => $request->user()?->id,
                'product_id' => $product->id,
                'student_name' => $validated['student_name'],
                'student_email' => $validated['student_email'],
                'student_phone' => $validated['student_phone'],
                'trx_id' => $validated['trx_id'],
                'amount' => $product->price,
                'status' => DigitalOrder::STATUS_PENDING,
            ]);

            // Shadow-write the unified sale in the same transaction: a
            // failure rolls the digital order back with it.
            app(CheckoutOrderWriter::class)->recordDigitalOrderSale($order);

            return $order;
        });

        $leads->capture([
            'user_id' => $request->user()?->id,
            'email' => $validated['student_email'],
            'name' => $validated['student_name'],
            'whatsapp_number' => $validated['student_phone'],
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => Lead::INTEREST_DIGITAL_PRODUCTS,
            'notes' => 'Product: '.$product->title,
        ]);

        // Queued after the order row has committed; a failed queue attempt is
        // logged by the service and never blocks the order itself.
        app(DigitalOrderApprovalService::class)->notifyOrderReceived($order);

        return redirect()
            ->route('shop.thank-you')
            ->with('product_title', $product->title)
            ->with('order_id', $order->id)
            ->with('student_email', $order->student_email);
    }

    /**
     * Thank-you page shown after a manual payment is submitted.
     */
    public function thankYou(Request $request): View|RedirectResponse
    {
        $productTitle = $request->session()->pull('product_title');
        $orderId = $request->session()->pull('order_id');
        $studentEmail = $request->session()->pull('student_email');

        if (! $orderId) {
            return redirect()->route('shop.index');
        }

        $metaTitle = 'Order Submitted | Banglay Chinese';

        return view('shop.thank-you', compact('productTitle', 'orderId', 'studentEmail', 'metaTitle'));
    }
}
