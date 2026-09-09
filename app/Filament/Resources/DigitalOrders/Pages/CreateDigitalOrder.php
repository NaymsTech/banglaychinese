<?php

namespace App\Filament\Resources\DigitalOrders\Pages;

use App\Filament\Resources\DigitalOrders\DigitalOrderResource;
use App\Services\OrderMaterializer;
use App\Support\Concerns\FillsBuyerSnapshotFromUser;
use Filament\Resources\Pages\CreateRecord;

class CreateDigitalOrder extends CreateRecord
{
    use FillsBuyerSnapshotFromUser;

    protected static string $resource = DigitalOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->fillBuyerSnapshotFromUser($data);
    }

    protected function afterCreate(): void
    {
        // Admin-recorded sales must exist in the canonical Order/Payment
        // structure too. Materialization is idempotent on the legacy key and
        // never touches the legacy row created by the form.
        app(OrderMaterializer::class)->materialize($this->record);
    }
}
