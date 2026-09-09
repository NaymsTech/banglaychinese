<?php

namespace App\Filament\Resources\ServiceOrders\Pages;

use App\Filament\Resources\ServiceOrders\ServiceOrderResource;
use App\Services\OrderMaterializer;
use App\Support\Concerns\FillsBuyerSnapshotFromUser;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceOrder extends CreateRecord
{
    use FillsBuyerSnapshotFromUser;

    protected static string $resource = ServiceOrderResource::class;

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
