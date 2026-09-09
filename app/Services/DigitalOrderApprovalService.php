<?php

namespace App\Services;

use App\Models\DigitalOrder;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Legacy digital-order home for the order-received notification.
 *
 * Payment review (approve/reject/requestCorrection/resubmit) is owned by
 * PaymentReviewService and updates this order's columns by mirror.
 */
class DigitalOrderApprovalService
{
    public const TEMPLATE_ORDER_RECEIVED = 'order_received_payment_pending';

    /**
     * Queue the "order received — payment pending" email right after the
     * public order is committed. Best-effort and marker-guarded.
     */
    public function notifyOrderReceived(DigitalOrder $order): bool
    {
        $order->loadMissing('product');

        $sent = $this->sendMarked(
            $order->id,
            'order_received_email_sent_at',
            self::TEMPLATE_ORDER_RECEIVED,
            $order->student_email,
            $order->student_name,
            [
                'student_name' => $order->student_name ?? 'there',
                'order_number' => (string) $order->id,
                'product_title' => $order->product?->title ?? 'your product',
                'amount' => number_format((float) $order->amount, 2),
                'order_url' => route('dashboard.index'),
            ],
        );

        $this->mirrorOrderReceivedMarker($order->id);

        return $sent;
    }

    /**
     * The canonical Order marker mirrors the legacy marker after a successful
     * (or previously completed) send, so parity never sees a legacy-only
     * order_received marker on records created from this point forward.
     */
    protected function mirrorOrderReceivedMarker(int $orderId): void
    {
        $marker = DB::table('digital_orders')
            ->where('id', $orderId)
            ->value('order_received_email_sent_at');

        if ($marker === null) {
            return;
        }

        Order::where('legacy_source', Order::SOURCE_DIGITAL_ORDER)
            ->where('legacy_id', $orderId)
            ->update(['order_received_email_sent_at' => $marker]);
    }

    /**
     * Claim the marker, attempt the queued send, and release the marker again
     * if the queue attempt fails — a marker is only a durable "email done"
     * once EmailService has accepted the job.
     *
     * @param  array<string, string>  $variables
     */
    protected function sendMarked(int $orderId, string $marker, string $templateKey, ?string $to, ?string $name, array $variables): bool
    {
        if (! filled($to)) {
            Log::warning('Digital order email skipped: no recipient.', [
                'digital_order_id' => $orderId,
                'template_key' => $templateKey,
            ]);

            return false;
        }

        $claimed = DB::table('digital_orders')
            ->where('id', $orderId)
            ->whereNull($marker)
            ->update([$marker => now()]) === 1;

        if (! $claimed) {
            return true; // Another request is already sending this email.
        }

        try {
            $result = app(EmailService::class)->sendTemplate($templateKey, $to, $variables, $name);

            if ($result['success'] ?? false) {
                return true;
            }

            Log::warning('Digital order email not queued.', [
                'digital_order_id' => $orderId,
                'template_key' => $templateKey,
                'recipient_email' => $to,
                'error' => $result['error'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Digital order email failed — marker released for retry.', [
                'digital_order_id' => $orderId,
                'template_key' => $templateKey,
                'recipient_email' => $to,
                'error' => $exception->getMessage(),
            ]);
        }

        DB::table('digital_orders')->where('id', $orderId)->update([$marker => null]);

        return false;
    }
}
