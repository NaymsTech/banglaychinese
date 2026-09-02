<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->disabled(),
                TextInput::make('phone')
                    ->disabled(),
                TextInput::make('email')
                    ->label('Email address')
                    ->disabled(),
                TextInput::make('topic')
                    ->disabled(),
                Textarea::make('message')
                    ->rows(6)
                    ->disabled()
                    ->columnSpanFull(),
                Toggle::make('is_read')
                    ->label('Marked as read')
                    ->disabled(),
            ]);
    }
}
