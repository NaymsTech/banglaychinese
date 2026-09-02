<?php

namespace App\Filament\Resources\Mentors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MentorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('expertise')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. HSK 4 Prep, University Application'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Linked user')
                    ->helperText('Optional — link this mentor profile to a registered user account.')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('hourly_rate')
                    ->label('Hourly rate (৳)')
                    ->required()
                    ->numeric()
                    ->prefix('৳')
                    ->minValue(0)
                    ->default(0),
                RichEditor::make('bio')
                    ->columnSpanFull(),
                Toggle::make('is_available')
                    ->label('Available for bookings')
                    ->default(true),
                FileUpload::make('profile_image')
                    ->image()
                    ->disk('public')
                    ->directory('mentors')
                    ->maxSize(2048)
                    ->columnSpanFull(),
            ]);
    }
}
