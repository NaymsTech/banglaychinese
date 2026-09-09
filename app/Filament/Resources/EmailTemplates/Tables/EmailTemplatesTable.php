<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use App\Models\EmailTemplate;
use App\Services\EmailService;
use App\Support\EmailTemplatePlaceholders;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EmailTemplatesTable
{
    protected static function categoryColor(string $category): string
    {
        return match ($category) {
            EmailTemplate::CATEGORY_TRANSACTIONAL => 'info',
            EmailTemplate::CATEGORY_MARKETING => 'success',
            EmailTemplate::CATEGORY_REMINDER => 'warning',
            EmailTemplate::CATEGORY_NOTIFICATION => 'purple',
            default => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (EmailTemplate $record): ?string => $record->description),
                TextColumn::make('key')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable()
                    ->tooltip('Key used in code — click to copy'),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => EmailTemplate::CATEGORIES[$state] ?? $state)
                    ->color(fn (string $state): string => self::categoryColor($state)),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('variables')
                    ->badge()
                    ->color('info'),
                TextColumn::make('from_address')
                    ->label('Sender')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (EmailTemplate $record): string => filled($record->from_address)
                        ? trim(($record->from_name ?? '').' <'.$record->from_address.'>')
                        : 'Provider default')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y, g:i A')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->options(EmailTemplate::CATEGORIES),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->where('is_active', $data['value'] === '1'),
                    )),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (EmailTemplate $record): string => "Preview “{$record->name}”")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema(fn (EmailTemplate $record): array => self::previewSchema($record)),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading(fn (EmailTemplate $record): string => "Duplicate “{$record->name}”")
                    ->modalDescription('Creates an inactive copy with a unique key. Enable it after reviewing the copy.')
                    ->modalSubmitActionLabel('Duplicate template')
                    ->action(function (EmailTemplate $record): void {
                        $copy = $record->replicate();
                        $copy->name = $record->name.' (Copy)';
                        $copy->key = self::uniqueCopyKey($record->key);
                        $copy->is_active = false;
                        $copy->save();

                        Notification::make()
                            ->success()
                            ->title('Template duplicated')
                            ->body("Created “{$copy->name}” as an inactive copy.")
                            ->send();
                    }),
                Action::make('testSend')
                    ->label('Send Test')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->modalHeading(fn (EmailTemplate $record): string => "Send test of “{$record->name}”")
                    ->modalDescription('Queues this template to the address you enter through the normal email pipeline (EmailService → EmailLog → queue → SendEmailJob). No email is sent unless a provider is active.')
                    ->schema([
                        TextInput::make('recipient_email')
                            ->label('Test recipient')
                            ->email()
                            ->required()
                            ->placeholder('admin@banglaychinese.com'),
                    ])
                    ->action(function (EmailTemplate $record, array $data): void {
                        $variables = [];

                        foreach (EmailTemplatePlaceholders::referenced($record->subject, $record->body) as $variable) {
                            if (! in_array($variable, EmailTemplatePlaceholders::SETTINGS_BACKED_VARIABLES, true)) {
                                $variables[$variable] = EmailTemplatePlaceholders::sampleValue($variable);
                            }
                        }

                        $result = app(EmailService::class)->sendTemplate(
                            $record->key,
                            $data['recipient_email'],
                            $variables,
                        );

                        if ($result['success'] ?? false) {
                            Notification::make()
                                ->success()
                                ->title('Test email queued')
                                ->body("A test of “{$record->name}” was queued for {$data['recipient_email']}.")
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title('Test email failed')
                            ->body($result['error'] ?? 'The test email could not be queued. Check the email providers.')
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (EmailTemplate $record): bool => $record->isRequired())
                    ->requiresConfirmation()
                    ->modalHeading('Delete this template?')
                    ->modalDescription('Email logs that used this template keep their history.'),
            ])
            ->defaultSort('name');
    }

    private static function uniqueCopyKey(string $original): string
    {
        $candidate = Str::limit($original, 90, '').'-copy';

        for ($i = 2; EmailTemplate::query()->where('key', $candidate)->exists(); $i++) {
            $candidate = Str::limit($original, 85, '').'-copy-'.$i;
        }

        return $candidate;
    }

    /**
     * @return array<int, object>
     */
    private static function previewSchema(EmailTemplate $record): array
    {
        /** @var array<int, string> $variables */
        $variables = array_values(array_unique($record->variables ?? []));

        return [
            Section::make('Fill in the variables')
                ->description('The preview below updates as you type. Blank variables use safe sample data.')
                ->columns(2)
                ->schema(collect($variables)
                    ->map(fn (string $variable): TextInput => TextInput::make('value_'.$variable)
                        ->label(Str::headline($variable))
                        ->placeholder('{'.$variable.'}')
                        ->live())
                    ->all()),
            TextEntry::make('preview')
                ->label('Rendered email')
                ->state(fn (Get $get): string => self::renderPreview($record, $variables, $get))
                ->html()
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  array<int, string>  $variables
     */
    private static function renderPreview(EmailTemplate $record, array $variables, Get $get): string
    {
        $emailService = app(EmailService::class);

        $values = $emailService->settingsVariables();

        foreach ($variables as $variable) {
            $value = $get('value_'.$variable);

            $values[$variable] = filled($value)
                ? (string) $value
                : EmailTemplatePlaceholders::sampleValue($variable);
        }

        return self::previewHtml($record, $values);
    }

    /**
     * Compose the exact branded document a real send would deliver, so the
     * Filament preview and the delivery seam share one canonical shell path
     * (EmailService::renderForDelivery). Complete-document rows still pass
     * through untouched, mirroring the delivery seam exactly.
     *
     * @param  array<string, mixed>  $values
     */
    public static function previewHtml(EmailTemplate $record, array $values): string
    {
        $emailService = app(EmailService::class);

        $rendered = $emailService->renderTemplate($record, $values);

        return '<p><strong>Subject:</strong> '.e($rendered['subject']).'</p><hr>'.$emailService->renderForDelivery($rendered['body']);
    }
}
