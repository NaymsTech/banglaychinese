<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\ScholarshipApplication;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class LeadsChart extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string
    {
        return 'Inquiries & Applications';
    }

    public function getDescription(): string
    {
        return 'Daily volume over the last 30 days';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $inquiriesByDay = ContactMessage::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $applicationsByDay = ScholarshipApplication::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $inquiries = [];
        $applications = [];

        foreach (CarbonPeriod::create($start, now()) as $date) {
            $key = $date->toDateString();

            $labels[] = $date->format('d M');
            $inquiries[] = (int) ($inquiriesByDay[$key] ?? 0);
            $applications[] = (int) ($applicationsByDay[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Inquiries',
                    'data' => $inquiries,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Applications',
                    'data' => $applications,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ];
    }
}
