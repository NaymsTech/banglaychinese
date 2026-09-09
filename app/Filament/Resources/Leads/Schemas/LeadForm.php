<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Lead;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->maxLength(255)
                    ->required()
                    ->helperText('Used to identify the lead — one row per email address.'),
                TextInput::make('whatsapp_number')
                    ->tel()
                    ->maxLength(30)
                    ->placeholder('01XXXXXXXXX'),
                Select::make('source')
                    ->options(Lead::SOURCES)
                    ->default(Lead::SOURCE_WEBSITE)
                    ->helperText('How this lead first reached the site.'),
                Select::make('interest')
                    ->options(Lead::INTERESTS)
                    ->default(Lead::INTEREST_GENERAL),
                Textarea::make('notes')
                    ->rows(3)
                    ->helperText('Internal notes for follow-up.'),
                Toggle::make('is_subscribed')
                    ->label('Subscribed')
                    ->default(true)
                    ->helperText('Whether the lead has opted in to receive updates.')
                    ->columnSpanFull(),
            ]);
    }
}
