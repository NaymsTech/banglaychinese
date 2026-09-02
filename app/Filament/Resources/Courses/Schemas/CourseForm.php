<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
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
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        RichEditor::make('description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Course Details')
                    ->columns(2)
                    ->schema([
                        Select::make('hsk_level')
                            ->options([
                                1 => 'HSK 1',
                                2 => 'HSK 2',
                                3 => 'HSK 3',
                                4 => 'HSK 4',
                            ]),
                        TextInput::make('price')
                            ->label('Price (৳)')
                            ->required()
                            ->numeric()
                            ->prefix('৳')
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('duration_weeks')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('e.g. 12'),
                        DatePicker::make('batch_start_date'),
                        DatePicker::make('batch_end_date'),
                    ]),

                Section::make('Media & Publishing')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->image()
                            ->disk('public')
                            ->directory('courses/thumbnails')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->default(false),
                        Toggle::make('is_featured')
                            ->default(false),
                    ]),
            ]);
    }
}
