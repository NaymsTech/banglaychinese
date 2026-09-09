<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * Admin payment/collection overview, calculated with database aggregates over
 * the canonical Order → Payment ledger — never from the legacy fulfillment
 * mirrors (enrollments.amount_paid / amount_due, service_orders.amount_paid,
 * digital_orders legacy fields).
 *
 * All canonical Orders are included, so course enrollments, digital product
 * orders and service orders all count — ServiceOrders moved into canonical
 * Order creation, and their money must appear here like every other sale.
 *
 * Money semantics (Payment rows are the source of truth):
 *   collected  = Σ payments.status = paid (money that actually came in)
 *   refunded   = Σ payments.status = refunded (reduces net, never collected)
 *   net        = collected − refunded
 *   due        = max(0, order.total_amount − net)
 *
 * "Collected This Month" is gross money received in the window — a received
 * row is counted even when it was later refunded, because refunded_at is not
 * recorded historically and a retroactive month re-attribution cannot be
 * reproduced faithfully. Refunded rows never add to the collected total.
 *
 * "Paid This Month" counts orders that are currently fully settled and that
 * received a paid Payment inside the window (an order counts once).
 *
 * "Payments Due" counts orders still awaiting money: those carrying an
 * awaiting-review claim (status = pending) or partially paid. Pending review
 * states that do not represent money received (needs_attention, rejected,
 * refunded) follow the existing report semantics.
 *
 * @return array<int, Stat>
 */
class PendingPaymentsOverview extends StatsOverviewWidget
{
    // Render the stat cards directly on the dashboard page (no lazy-loading);
    // each card is a lightweight aggregate query.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // Per-order payment totals grouped once and reused by both order-level
        // cards. The database aggregates; no Payment row is loaded into PHP.
        $ledger = DB::table('payments')
            ->selectRaw('order_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'paid' THEN amount END), 0) AS paid_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'refunded' THEN amount END), 0) AS refunded_total")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count")
            ->groupBy('order_id');

        $net = '(COALESCE(p.paid_total, 0) - COALESCE(p.refunded_total, 0))';

        $outstanding = (int) DB::table('orders')
            ->leftJoinSub($ledger, 'p', 'p.order_id', '=', 'orders.id')
            ->where(function ($query) use ($net): void {
                $query->where('p.pending_count', '>', 0)
                    ->orWhere(function ($query) use ($net): void {
                        $query->whereRaw("({$net}) > 0.009")
                            ->whereRaw("({$net}) < orders.total_amount - 0.009");
                    });
            })
            ->count();

        $paidInMonth = DB::table('payments')
            ->where('status', Payment::STATUS_PAID)
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->selectRaw('order_id')
            ->selectRaw('COALESCE(SUM(amount), 0) AS month_paid')
            ->groupBy('order_id');

        $paidThisMonth = (int) DB::table('orders')
            ->leftJoinSub($paidInMonth, 'm', 'm.order_id', '=', 'orders.id')
            ->leftJoinSub($ledger, 'p', 'p.order_id', '=', 'orders.id')
            ->where('m.month_paid', '>', 0)
            ->whereRaw("({$net}) >= orders.total_amount - 0.009")
            ->count();

        $collectedThisMonth = (float) DB::table('payments')
            ->where('status', Payment::STATUS_PAID)
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');

        return [
            Stat::make('Payments Due', $outstanding)
                ->description('Orders awaiting review or partially paid')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Paid This Month', $paidThisMonth)
                ->description('Since '.$monthStart->format('j M Y'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Collected This Month', '৳ '.number_format($collectedThisMonth, 2))
                ->description('Money received via canonical payments this month')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
