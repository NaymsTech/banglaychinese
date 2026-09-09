<?php

namespace App\Filament\Resources\FreeResources\Pages;

use App\Filament\Resources\FreeResources\FreeResourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFreeResource extends EditRecord
{
    protected static string $resource = FreeResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
