<?php

namespace App\Filament\Resources\DigitalOrders\Pages;

use App\Filament\Resources\DigitalOrders\DigitalOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDigitalOrders extends ListRecords
{
    protected static string $resource = DigitalOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
