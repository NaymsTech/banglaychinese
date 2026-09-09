<?php

namespace App\Filament\Resources\FreeResources\Pages;

use App\Filament\Resources\FreeResources\FreeResourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFreeResources extends ListRecords
{
    protected static string $resource = FreeResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
