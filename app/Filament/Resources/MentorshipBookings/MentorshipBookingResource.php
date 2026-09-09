<?php

namespace App\Filament\Resources\MentorshipBookings;

use App\Filament\Resources\MentorshipBookings\Pages\CreateMentorshipBooking;
use App\Filament\Resources\MentorshipBookings\Pages\EditMentorshipBooking;
use App\Filament\Resources\MentorshipBookings\Pages\ListMentorshipBookings;
use App\Filament\Resources\MentorshipBookings\Schemas\MentorshipBookingForm;
use App\Filament\Resources\MentorshipBookings\Tables\MentorshipBookingsTable;
use App\Models\MentorshipBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MentorshipBookingResource extends Resource
{
    protected static ?string $model = MentorshipBooking::class;

    protected static ?int $navigationSort = 40;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Admissions & CRM';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function form(Schema $schema): Schema
    {
        return MentorshipBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentorshipBookingsTable::configure($table);
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
            'index' => ListMentorshipBookings::route('/'),
            'create' => CreateMentorshipBooking::route('/create'),
            'edit' => EditMentorshipBooking::route('/{record}/edit'),
        ];
    }
}
