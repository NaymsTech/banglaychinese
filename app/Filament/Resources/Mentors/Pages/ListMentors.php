<?php

namespace App\Filament\Resources\Mentors\Pages;

use App\Filament\Resources\Mentors\MentorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMentors extends ListRecords
{
    protected static string $resource = MentorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
