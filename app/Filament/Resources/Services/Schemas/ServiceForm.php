<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Info')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $get, callable $set): void {
                                if (filled($state) && blank($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('short_description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        RichEditor::make('description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Pricing & Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')
                            ->label('Price (৳)')
                            ->required()
                            ->numeric()
                            ->prefix('৳')
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('duration')
                            ->placeholder('e.g. 1 year, 8 weeks'),
                        TextInput::make('cta_label')
                            ->maxLength(50)
                            ->placeholder('Book Consultation'),
                    ]),

                Section::make('Features')
                    ->description('Type a feature and press Enter to add it to the checklist.')
                    ->schema([
                        TagsInput::make('features')
                            ->placeholder('Add a feature'),
                    ]),

                Section::make('Publishing')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options([
                                '1' => 'Active',
                                '0' => 'Inactive',
                            ])
                            ->default('1')
                            ->required(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }
}
