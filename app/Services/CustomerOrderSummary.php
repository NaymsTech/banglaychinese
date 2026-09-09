<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;

/**
 * Read-only lifetime aggregate of one customer's canonical orders.
 *
 * Money and payment state always come from the canonical spine (orders +
 * payments) through the existing Order calculation methods — paidTotal(),
 * refundedTotal(), dueTotal() and reviewStatus() — never from legacy
 * enrollments / digital_orders / service_orders columns.
 *
 * The customer's orders are loaded once (with their payments) and folded in
 * memory, so no per-order queries are issued and the result is a single
 * consistent snapshot of the customer's lifetime history.
 */
class CustomerOrderSummary
{
    public int $orderCount = 0;

    public float $totalValue = 0.0;

    public float $totalPaid = 0.0;

    public float $totalRefunded = 0.0;

    public float $totalDue = 0.0;

    public int $pendingCount = 0;

    public int $partiallyPaidCount = 0;

    public int $completedCount = 0;

    public int $needsAttentionCount = 0;

    public static function forUser(User $user): self
    {
        $orders = $user->orders()->with('payments')->get();

        $summary = new self;

        foreach ($orders as $order) {
            $summary->orderCount++;

            $summary->totalValue += (float) $order->total_amount;
            $summary->totalPaid += $order->paidTotal();
            $summary->totalRefunded += $order->refundedTotal();
            $summary->totalDue += $order->dueTotal();

            $reviewStatus = $order->reviewStatus();

            if ($reviewStatus === Enrollment::PAYMENT_STATUS_PENDING) {
                $summary->pendingCount++;
            } elseif ($reviewStatus === Enrollment::PAYMENT_STATUS_PARTIALLY_PAID) {
                $summary->partiallyPaidCount++;
            } elseif ($reviewStatus === Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION) {
                $summary->needsAttentionCount++;
            }

            if ($order->order_status === Order::STATUS_COMPLETED) {
                $summary->completedCount++;
            }
        }

        $summary->totalValue = round($summary->totalValue, 2);
        $summary->totalPaid = round($summary->totalPaid, 2);
        $summary->totalRefunded = round($summary->totalRefunded, 2);
        $summary->totalDue = round($summary->totalDue, 2);

        return $summary;
    }
}
