<?php

namespace App\Filament\Resources\MentorshipBookings\Schemas;

use App\Models\MentorshipBooking;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MentorshipBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->relationship('student', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                Select::make('mentor_id')
                    ->relationship('mentor', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                DateTimePicker::make('scheduled_at')
                    ->label('Scheduled at')
                    ->seconds(false)
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                Select::make('status')
                    ->options(MentorshipBooking::STATUS_OPTIONS)
                    ->default(MentorshipBooking::STATUS_PENDING)
                    ->required(),
                TextInput::make('meeting_link')
                    ->label('Meeting link')
                    ->url()
                    ->placeholder('https://meet.google.com/...')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }
}
