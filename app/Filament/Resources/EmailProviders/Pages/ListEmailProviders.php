<?php

namespace App\Filament\Resources\EmailProviders\Pages;

use App\Filament\Resources\EmailProviders\EmailProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailProviders extends ListRecords
{
    protected static string $resource = EmailProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
