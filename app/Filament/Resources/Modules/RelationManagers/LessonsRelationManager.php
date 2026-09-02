<?php

namespace App\Filament\Resources\Modules\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('video_url')
                    ->label('Video')
                    ->url(fn ($state): ?string => filled($state) ? $state : null)
                    ->openUrlInNewTab()
                    ->placeholder('—'),
                TextColumn::make('order')
                    ->alignCenter(),
                IconColumn::make('is_free_preview')
                    ->label('Free')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-eye' : 'heroicon-o-lock-closed')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order');
    }
}
