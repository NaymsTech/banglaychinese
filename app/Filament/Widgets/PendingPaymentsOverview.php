<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingPaymentsOverview extends StatsOverviewWidget
{
    // Render the stat cards directly on the dashboard page (no lazy-loading),
    // since these are three lightweight COUNT/SUM queries.
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $pending = Enrollment::where('status', 'pending')->count();

        $approvedThisMonth = Enrollment::where('status', 'active')
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->count();

        $revenueThisMonth = (float) Enrollment::where('status', 'active')
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->sum('price_paid');

        return [
            Stat::make('Pending Payments', $pending)
                ->description('Awaiting your approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Approved This Month', $approvedThisMonth)
                ->description('Since ' . $startOfMonth->format('j M Y'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Total Revenue This Month', '৳ ' . number_format($revenueThisMonth, 2))
                ->description('From approved enrollments')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
