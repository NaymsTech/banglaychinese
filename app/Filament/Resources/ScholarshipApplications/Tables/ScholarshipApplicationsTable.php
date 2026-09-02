<?php

namespace App\Filament\Resources\ScholarshipApplications\Tables;

use App\Filament\Resources\ScholarshipApplications\Schemas\ScholarshipApplicationForm;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScholarshipApplicationsTable
{
    public static function crmStatusColors(): array
    {
        return [
            'new' => 'gray',
            'contacted' => 'sky',
            'consultation_scheduled' => 'warning',
            'application_started' => 'purple',
            'converted' => 'success',
            'closed' => 'danger',
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desired_program')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(),
                TextColumn::make('interestedService.name')
                    ->label('Service')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('CRM stage')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ScholarshipApplicationForm::CRM_STATUSES[$state] ?? $state)
                    ->color(fn ($state): string => self::crmStatusColors()[$state] ?? 'gray'),
                TextColumn::make('application_status')
                    ->label('Scholarship')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        if (blank($state) || $state === 'pending') {
                            return 'Pending';
                        }

                        return ucfirst($state);
                    })
                    ->color(fn ($state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('follow_up_date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('CRM stage')
                    ->options(ScholarshipApplicationForm::CRM_STATUSES),
                SelectFilter::make('application_status')
                    ->label('Scholarship outcome')
                    ->options([
                        'pending' => 'Pending (no decision)',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'pending' => $query->whereNull('application_status'),
                            'approved', 'rejected' => $query->where('application_status', $data['value']),
                            default => $query,
                        };
                    }),
                SelectFilter::make('interested_service_id')
                    ->relationship('interestedService', 'name')
                    ->label('Service')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
