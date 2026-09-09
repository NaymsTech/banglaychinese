<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Mentor;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    // Render directly on page load: these are four lightweight COUNT queries.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Published Courses', Course::where('is_published', true)->count())
                ->description('Live on the public site')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('success'),

            Stat::make('Student Inquiries', ContactMessage::count())
                ->description(ContactMessage::unread()->count().' unread')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('warning'),

            Stat::make('Registered Users', User::count())
                ->description('Students & staff accounts')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),

            Stat::make('Active Mentors', Mentor::available()->count())
                ->description('Accepting new bookings')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
        ];
    }
}
