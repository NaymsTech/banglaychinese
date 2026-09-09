<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentContactMessagesWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Inquiries')
            ->query(
                ContactMessage::query()
                    ->latest('created_at')
                    ->limit(5),
            )
            ->columns([
                TextColumn::make('name')
                    ->weight('medium')
                    ->description(fn (ContactMessage $record): string => $record->phone ?? ''),
                TextColumn::make('topic')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('message')
                    ->limit(50)
                    ->tooltip(fn (string $state): string => $state),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M, g:i A'),
            ])
            ->paginated(false)
            ->recordUrl(fn (ContactMessage $record): string => ContactMessageResource::getUrl('edit', ['record' => $record]));
    }
}
