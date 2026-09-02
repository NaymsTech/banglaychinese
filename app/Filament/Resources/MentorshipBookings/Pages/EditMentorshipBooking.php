<?php

namespace App\Filament\Resources\MentorshipBookings\Pages;

use App\Filament\Resources\MentorshipBookings\MentorshipBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMentorshipBooking extends EditRecord
{
    protected static string $resource = MentorshipBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
