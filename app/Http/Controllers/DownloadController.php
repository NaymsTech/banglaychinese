<?php

namespace App\Http\Controllers;

use App\Models\DigitalOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /**
     * Deliver an approved purchase to its buyer.
     *
     * The requested DigitalOrder is the request anchor. Authorization is
     * decided exclusively by the CURRENT canonical Order/OrderItem/Payment
     * state that belongs to THIS DigitalOrder — this controller never makes
     * or overrides an admin payment decision, and it never materializes or
     * repairs canonical data. Legacy `digital_orders.status = approved` is
     * required only as a state-coherence assertion: every sanctioned admin
     * transition mirrors to both structures in one transaction, so a
     * mismatch means drift, which fails closed.
     *
     * Products configured with an external download link (Google Drive,
     * Dropbox, …) redirect the buyer there; everything else streams the
     * privately stored PDF. Guests who bought without an account must log
     * in with the same email they used at checkout.
     */
    public function download(DigitalOrder $order): StreamedResponse|RedirectResponse
    {
        $user = auth()->user();

        abort_unless($this->customerOwnsOrder($order, $user), 403);
        abort_unless($this->isDigitallyAuthorized($order, $user), 403);

        $product = $order->product;

        $externalUrl = trim((string) $product?->external_download_url);

        if ($externalUrl !== '') {
            return redirect()->away($externalUrl);
        }

        $filePath = $product?->file_path;

        abort_if(blank($filePath) || ! Storage::disk('local')->exists($filePath), 404);

        return Storage::disk('local')->download($filePath, $product->title.'.pdf');
    }

    /**
     * Identity semantics preserved from the pre-canonical controller: the
     * buyer owns the DigitalOrder when it is linked to their account or was
     * purchased with their email address (guest-order support). IDOR is
     * blocked because every request must pass through the requested
     * DigitalOrder's own ownership check.
     */
    protected function customerOwnsOrder(DigitalOrder $order, Authenticatable $user): bool
    {
        $requester = $this->requester($user);

        $sameAccount = $order->user_id !== null && $order->user_id === $requester['id'];
        $sameEmail = filled($order->student_email) && $requester['email'] !== '' && strcasecmp((string) $order->student_email, $requester['email']) === 0;

        return $sameAccount || $sameEmail;
    }

    /**
     * Canonical entitlement check for the requested DigitalOrder. Every
     * required component must be present and consistent; anything missing or
     * incoherent denies the download (fail closed).
     */
    protected function isDigitallyAuthorized(DigitalOrder $order, Authenticatable $user): bool
    {
        // Coherence assertion: the sanctioned mirror writes always keep the
        // legacy fulfillment flag and the canonical state in agreement. It is
        // a consistency guard, never a fallback source of entitlement.
        if ($order->status !== DigitalOrder::STATUS_APPROVED) {
            return false;
        }

        $canonical = Order::where('legacy_source', Order::SOURCE_DIGITAL_ORDER)
            ->where('legacy_id', $order->getKey())
            ->first();

        if ($canonical === null) {
            return false;
        }

        if (! $this->canonicalBelongsToRequester($canonical, $user)) {
            return false;
        }

        if ($canonical->order_status !== Order::STATUS_COMPLETED) {
            return false;
        }

        $hasItemForProduct = $canonical->items()
            ->where('purchasable_type', Product::class)
            ->where('purchasable_id', $order->product_id)
            ->exists();

        if (! $hasItemForProduct) {
            return false;
        }

        $payments = $canonical->payments()->get(['status', 'amount']);

        // Digital products are non-refundable and no refund workflow exists.
        // Refunded rows can only be malformed/manual data — treat as
        // inconsistent and fail closed instead of inventing business rules.
        if ($payments->contains(fn (Payment $payment): bool => $payment->status === Payment::STATUS_REFUNDED)) {
            return false;
        }

        $paidTotal = (float) $payments
            ->where('status', Payment::STATUS_PAID)
            ->sum('amount');

        return $paidTotal >= (float) $canonical->total_amount - 0.009;
    }

    protected function canonicalBelongsToRequester(Order $canonical, Authenticatable $user): bool
    {
        $requester = $this->requester($user);

        $sameAccount = $canonical->user_id !== null && $canonical->user_id === $requester['id'];
        $sameEmail = filled($canonical->student_email) && $requester['email'] !== '' && strcasecmp((string) $canonical->student_email, $requester['email']) === 0;

        return $sameAccount || $sameEmail;
    }

    /**
     * @return array{id: int|string|null, email: string}
     */
    protected function requester(Authenticatable $user): array
    {
        return [
            'id' => method_exists($user, 'getAuthIdentifier') ? $user->getAuthIdentifier() : null,
            'email' => method_exists($user, 'getAttribute') ? (string) $user->getAttribute('email') : '',
        ];
    }
}
