<?php

namespace App\Filament\Resources\FreeResources;

use App\Filament\Resources\FreeResources\Pages\CreateFreeResource;
use App\Filament\Resources\FreeResources\Pages\EditFreeResource;
use App\Filament\Resources\FreeResources\Pages\ListFreeResources;
use App\Filament\Resources\FreeResources\Schemas\FreeResourceForm;
use App\Filament\Resources\FreeResources\Tables\FreeResourcesTable;
use App\Models\FreeResource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FreeResourceResource extends Resource
{
    protected static ?string $model = FreeResource::class;

    protected static ?int $navigationSort = 60;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Courses & Learning';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return FreeResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FreeResourcesTable::configure($table);
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
            'index' => ListFreeResources::route('/'),
            'create' => CreateFreeResource::route('/create'),
            'edit' => EditFreeResource::route('/{record}/edit'),
        ];
    }
}
