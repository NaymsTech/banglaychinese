<?php

namespace App\Filament\Resources\Mentors\Pages;

use App\Filament\Resources\Mentors\MentorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMentor extends EditRecord
{
    protected static string $resource = MentorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
