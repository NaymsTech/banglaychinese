<?php

namespace App\Filament\Resources\DigitalOrders;

use App\Filament\Resources\DigitalOrders\Pages\CreateDigitalOrder;
use App\Filament\Resources\DigitalOrders\Pages\EditDigitalOrder;
use App\Filament\Resources\DigitalOrders\Pages\ListDigitalOrders;
use App\Filament\Resources\DigitalOrders\Schemas\DigitalOrderForm;
use App\Filament\Resources\DigitalOrders\Tables\DigitalOrdersTable;
use App\Models\DigitalOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DigitalOrderResource extends Resource
{
    protected static ?string $model = DigitalOrder::class;

    protected static ?int $navigationSort = 34;

    public static function getEloquentQuery(): Builder
    {
        // Canonical Order/Payment rows are loaded with every legacy row so the
        // admin table can display canonical money without per-row queries.
        return parent::getEloquentQuery()->with('unifiedOrder.payments');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Admissions & CRM';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $recordTitleAttribute = 'student_name';

    public static function form(Schema $schema): Schema
    {
        return DigitalOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DigitalOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDigitalOrders::route('/'),
            'create' => CreateDigitalOrder::route('/create'),
            'edit' => EditDigitalOrder::route('/{record}/edit'),
        ];
    }
}
