<?php

namespace App\Filament\Resources\DigitalOrders\Pages;

use App\Filament\Resources\DigitalOrders\DigitalOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDigitalOrder extends EditRecord
{
    protected static string $resource = DigitalOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
