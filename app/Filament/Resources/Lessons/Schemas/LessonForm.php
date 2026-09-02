<?php

namespace App\Filament\Resources\Lessons\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('module_id')
                    ->relationship('module', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
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
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('video_url')
                    ->label('Video URL')
                    ->url()
                    ->placeholder('https://www.youtube.com/watch?v=...'),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_free_preview')
                    ->default(false),
            ]);
    }
}
