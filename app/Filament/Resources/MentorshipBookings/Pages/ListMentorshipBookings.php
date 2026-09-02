<?php

namespace App\Filament\Resources\MentorshipBookings\Pages;

use App\Filament\Resources\MentorshipBookings\MentorshipBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMentorshipBookings extends ListRecords
{
    protected static string $resource = MentorshipBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
