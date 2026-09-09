<?php

namespace App\Filament\Resources\FreeResources\Tables;

use App\Models\FreeResource;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class FreeResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('resource_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'video' => 'success',
                        default => 'info',
                    })
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Published')
                    ->onColor('success')
                    ->offColor('gray')
                    ->tooltip('Quick toggle between hidden and published'),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('resource_type')
                    ->label('Type')
                    ->options([
                        'pdf' => 'PDF',
                        'video' => 'Video',
                        'link' => 'Link',
                    ]),
                SelectFilter::make('category')
                    ->options(fn (): array => FreeResource::query()
                        ->pluck('category', 'category')
                        ->all()),
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
                        ->modalHeading('Toggle publish on selected resources?')
                        ->modalDescription('Published resources become hidden and hidden resources become published.')
                        ->modalSubmitActionLabel('Toggle publish')
                        ->action(function (Collection $records): void {
                            $records->each(function (FreeResource $resource): void {
                                $resource->update(['is_published' => ! $resource->is_published]);
                            });

                            Notification::make()
                                ->success()
                                ->title('Selected resources updated.')
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
