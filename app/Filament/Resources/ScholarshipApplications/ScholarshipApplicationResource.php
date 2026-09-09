<?php

namespace App\Filament\Resources\ScholarshipApplications;

use App\Filament\Resources\ScholarshipApplications\Pages\CreateScholarshipApplication;
use App\Filament\Resources\ScholarshipApplications\Pages\EditScholarshipApplication;
use App\Filament\Resources\ScholarshipApplications\Pages\ListScholarshipApplications;
use App\Filament\Resources\ScholarshipApplications\Schemas\ScholarshipApplicationForm;
use App\Filament\Resources\ScholarshipApplications\Tables\ScholarshipApplicationsTable;
use App\Models\ScholarshipApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ScholarshipApplicationResource extends Resource
{
    protected static ?string $model = ScholarshipApplication::class;

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Admissions & CRM';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Applications & Leads';

    public static function form(Schema $schema): Schema
    {
        return ScholarshipApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScholarshipApplicationsTable::configure($table);
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
            'index' => ListScholarshipApplications::route('/'),
            'create' => CreateScholarshipApplication::route('/create'),
            'edit' => EditScholarshipApplication::route('/{record}/edit'),
        ];
    }
}
