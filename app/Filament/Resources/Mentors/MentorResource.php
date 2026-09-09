<?php

namespace App\Filament\Resources\Mentors;

use App\Filament\Resources\Mentors\Pages\CreateMentor;
use App\Filament\Resources\Mentors\Pages\EditMentor;
use App\Filament\Resources\Mentors\Pages\ListMentors;
use App\Filament\Resources\Mentors\RelationManagers\AvailabilitiesRelationManager;
use App\Filament\Resources\Mentors\Schemas\MentorForm;
use App\Filament\Resources\Mentors\Tables\MentorsTable;
use App\Models\Mentor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MentorResource extends Resource
{
    protected static ?string $model = Mentor::class;

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Courses & Learning';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MentorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AvailabilitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMentors::route('/'),
            'create' => CreateMentor::route('/create'),
            'edit' => EditMentor::route('/{record}/edit'),
        ];
    }
}
