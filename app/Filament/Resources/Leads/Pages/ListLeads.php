<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Exports\LeadExporter;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Export')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->exporter(LeadExporter::class)
                ->columnMapping(false),
            CreateAction::make(),
        ];
    }
}
