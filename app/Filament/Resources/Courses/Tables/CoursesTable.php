<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Models\Course;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hsk_level')
                    ->label('HSK level')
                    ->formatStateUsing(fn ($state): string => $state ? "HSK {$state}" : '—')
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Price (৳)')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Published')
                    ->onColor('success')
                    ->offColor('gray')
                    ->tooltip('Quick toggle between draft and published'),
                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->onColor('warning')
                    ->offColor('gray')
                    ->tooltip('Quick toggle for the homepage highlight'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Publishing status')
                    ->trueLabel('Published')
                    ->falseLabel('Draft'),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('hsk_level')
                    ->label('HSK level')
                    ->options([
                        1 => 'HSK 1',
                        2 => 'HSK 2',
                        3 => 'HSK 3',
                        4 => 'HSK 4',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('togglePublish')
                        ->label('Toggle publish')
                        ->icon('heroicon-o-eye')
                        ->requiresConfirmation()
                        ->modalHeading('Toggle publish on selected courses?')
                        ->modalDescription('Published courses become drafts and drafts become published.')
                        ->modalSubmitActionLabel('Toggle publish')
                        ->action(function (Collection $records): void {
                            $records->each(function (Course $course): void {
                                $course->update(['is_published' => ! $course->is_published]);
                            });

                            Notification::make()
                                ->success()
                                ->title('Selected courses updated.')
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
