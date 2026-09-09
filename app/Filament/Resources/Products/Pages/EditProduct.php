<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Actions\DeleteProductAction;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteProductAction::make(),
        ];
    }
}
