<?php

namespace App\Filament\Exports;

use App\Models\Lead;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class LeadExporter extends Exporter
{
    protected static ?string $model = Lead::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('whatsapp_number')
                ->label('WhatsApp'),
            ExportColumn::make('source')
                ->label('Source')
                ->formatStateUsing(fn (Lead $record): string => Lead::SOURCES[$record->source] ?? $record->source),
            ExportColumn::make('interest')
                ->label('Interest')
                ->formatStateUsing(fn (Lead $record): string => Lead::INTERESTS[$record->interest] ?? $record->interest),
            ExportColumn::make('notes')
                ->label('Notes'),
            ExportColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn (Lead $record): ?string => $record->created_at?->format('Y-m-d H:i:s')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return Number::format($export->successful_rows).' of '.Number::format($export->total_rows).' leads exported.';
    }
}
