<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(30)
                            ->helperText('Optional — used for WhatsApp/SMS contact.'),
                        TextInput::make('password')
                            ->label(fn (string $operation): string => $operation === 'create' ? 'Password' : 'New password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->minLength(8)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'Minimum 8 characters. The user can change it later.'
                                : 'Leave blank to keep the current password.'),
                    ]),

                Section::make('Access')
                    ->columns(2)
                    ->schema([
                        Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'student' => 'Student',
                            ])
                            ->default('student')
                            ->required()
                            ->helperText('Admin role grants dashboard access. Students only use the public site.')
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                        Toggle::make('is_admin')
                            ->label('Full dashboard access')
                            ->helperText('Grants admin panel access regardless of role.')
                            ->default(false)
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                    ]),
            ]);
    }
}
