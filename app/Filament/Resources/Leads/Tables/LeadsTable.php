<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Filament\Pages\SendManualEmail;
use App\Filament\Resources\Users\UserResource;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->placeholder('—'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('whatsapp_number')
                    ->label('WhatsApp')
                    ->searchable()
                    ->toggleable()
                    ->url(fn (Lead $record): ?string => filled($record->whatsapp_number)
                        ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $record->whatsapp_number)
                        : null)
                    ->openUrlInNewTab(),
                TextColumn::make('user.name')
                    ->label('Account')
                    ->placeholder('Guest')
                    ->toggleable()
                    ->url(fn (Lead $record): ?string => $record->user_id
                        ? UserResource::getUrl('edit', ['record' => $record->user_id])
                        : null),
                TextColumn::make('source')
                    ->badge()
                    ->color(fn (string $state): string => Lead::SOURCE_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => Lead::SOURCES[$state] ?? Str::headline($state))
                    ->searchable(),
                TextColumn::make('interest')
                    ->badge()
                    ->color(fn (string $state): string => Lead::INTEREST_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => Lead::INTERESTS[$state] ?? Str::headline($state))
                    ->searchable(),
                TextColumn::make('notes')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_subscribed')
                    ->label('Subscribed')
                    ->onColor('success')
                    ->offColor('warning')
                    ->tooltip('Opted in to updates'),
                TextColumn::make('created_at')
                    ->label('Captured')
                    ->dateTime('d M Y, g:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options(Lead::SOURCES),
                SelectFilter::make('interest')
                    ->options(Lead::INTERESTS),
                TernaryFilter::make('is_subscribed')
                    ->label('Subscription')
                    ->trueLabel('Subscribed')
                    ->falseLabel('Unsubscribed'),
                Filter::make('captured_at')
                    ->label('Captured between')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('From'),
                        DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, string $from): Builder => $query->whereDate('created_at', '>=', $from),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, string $until): Builder => $query->whereDate('created_at', '<=', $until),
                            );
                    }),
            ])
            ->selectable()
            ->recordActions([
                Action::make('sendEmail')
                    ->label('Send email')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->url(fn (Lead $record): string => SendManualEmail::getUrl([
                        'recipient_email' => $record->email,
                        'target_audience' => 'single',
                    ])),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete this lead?')
                    ->modalDescription('The lead will be permanently removed.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected leads?')
                        ->modalDescription('The selected leads will be permanently removed.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
