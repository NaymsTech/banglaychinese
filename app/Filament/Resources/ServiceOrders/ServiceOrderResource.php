<?php

namespace App\Filament\Resources\ServiceOrders;

use App\Filament\Resources\ServiceOrders\Pages\CreateServiceOrder;
use App\Filament\Resources\ServiceOrders\Pages\EditServiceOrder;
use App\Filament\Resources\ServiceOrders\Pages\ListServiceOrders;
use App\Filament\Resources\ServiceOrders\Schemas\ServiceOrderForm;
use App\Filament\Resources\ServiceOrders\Tables\ServiceOrdersTable;
use App\Models\ServiceOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationLabel = 'Service Orders';

    protected static ?string $recordTitleAttribute = 'student_name';

    protected static ?int $navigationSort = 35;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Admissions & CRM';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    public static function form(Schema $schema): Schema
    {
        return ServiceOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceOrdersTable::configure($table);
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
            'index' => ListServiceOrders::route('/'),
            'create' => CreateServiceOrder::route('/create'),
            'edit' => EditServiceOrder::route('/{record}/edit'),
        ];
    }
}
