<?php

namespace App\Filament\Resources\EmailProviders;

use App\Filament\Resources\EmailProviders\Pages\CreateEmailProvider;
use App\Filament\Resources\EmailProviders\Pages\EditEmailProvider;
use App\Filament\Resources\EmailProviders\Pages\ListEmailProviders;
use App\Filament\Resources\EmailProviders\Schemas\EmailProviderForm;
use App\Filament\Resources\EmailProviders\Tables\EmailProvidersTable;
use App\Models\EmailProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EmailProviderResource extends Resource
{
    protected static ?string $model = EmailProvider::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Communication';
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Email Providers';

    public static function form(Schema $schema): Schema
    {
        return EmailProviderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailProvidersTable::configure($table);
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
            'index' => ListEmailProviders::route('/'),
            'create' => CreateEmailProvider::route('/create'),
            'edit' => EditEmailProvider::route('/{record}/edit'),
        ];
    }
}
