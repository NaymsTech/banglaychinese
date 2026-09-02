<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
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
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->helperText('Short summary shown in blog listings. Leave blank to auto-trim the content.')
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('hsk_level')
                            ->options([
                                1 => 'HSK 1',
                                2 => 'HSK 2',
                                3 => 'HSK 3',
                                4 => 'HSK 4',
                            ]),
                        FileUpload::make('featured_image')
                            ->image()
                            ->disk('public')
                            ->directory('posts')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),

                Section::make('Publishing')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false)
                            ->live(),
                        DateTimePicker::make('published_at')
                            ->label('Publish date & time')
                            ->visible(fn (callable $get): bool => (bool) $get('is_published'))
                            ->required(fn (callable $get): bool => (bool) $get('is_published')),
                    ]),
            ]);
    }
}
