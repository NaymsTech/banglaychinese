<?php

namespace App\Filament\Resources\EmailProviders\Pages;

use App\Filament\Resources\EmailProviders\EmailProviderResource;
use App\Filament\Resources\EmailProviders\Schemas\EmailProviderForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailProvider extends EditRecord
{
    protected static string $resource = EmailProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['config'] = EmailProviderForm::preserveExistingSecrets($this->record, $data['config'] ?? []);

        return $data;
    }
}
