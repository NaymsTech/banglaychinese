<?php

namespace App\Filament\Pages;

use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Services\EmailService;
use App\Support\EmailTemplatePlaceholders;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use UnitEnum;

class SendManualEmail extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Send Email';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Communication';
    }

    protected string $view = 'filament.pages.send-manual-email';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function mount(): void
    {
        // Filament only applies a field's ->default() when the form is
        // filled, so a bare page load leaves every value null and the
        // "single recipient" field hidden even though it is the default
        // mode. Apply the default state explicitly on mount.
        $email = request()->query('recipient_email');

        $this->form->fill(array_filter([
            'target_audience' => 'single',
            'recipient_email' => is_string($email) && filled($email) ? $email : null,
            'lead_interest' => Lead::INTEREST_GENERAL,
        ], fn (mixed $value): bool => $value !== null));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Emails')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalHeading('Confirm Email Send')
                ->modalDescription('You are about to send this email to the selected recipients. Please verify the details before proceeding.')
                ->modalSubmitActionLabel('Yes, Send Now')
                ->modalCancelActionLabel('Cancel')
                ->action(function (): void {
                    $this->send();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('1. Choose a template')
                    ->description('Every template declares its own placeholders — they appear below once one is selected.')
                    ->columns(2)
                    ->schema([
                        Select::make('template_id')
                            ->label('Email template')
                            ->options(fn (): array => EmailTemplate::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?int $state): void {
                                $this->resetErrorBag();

                                $template = $state === null
                                    ? null
                                    : EmailTemplate::query()->where('is_active', true)->find($state);

                                $variables = collect($template?->variables ?? [])
                                    ->mapWithKeys(fn (string $variable): array => [$variable => ''])
                                    ->all();

                                $set('variables', $variables);
                            }),
                    ]),

                Section::make('2. Choose recipients')
                    ->columns(2)
                    ->schema([
                        Radio::make('target_audience')
                            ->label('Target audience')
                            ->options([
                                'single' => 'Single email address',
                                'segment' => 'Lead segment by interest',
                                'all' => 'All subscribed leads',
                            ])
                            ->default('single')
                            ->live(),
                        TextInput::make('recipient_email')
                            ->label('Recipient email')
                            ->email()
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('target_audience') === 'single'),
                        Select::make('lead_interest')
                            ->label('Lead interest segment')
                            ->options(Lead::INTERESTS)
                            ->default(Lead::INTEREST_GENERAL)
                            ->visible(fn (Get $get): bool => $get('target_audience') === 'segment'),
                    ]),

                Section::make('3. Fill in the variables')
                    ->description(fn (Get $get): string => self::variablesSectionDescription($get))
                    ->schema(function (Get $get): array {
                        $templateId = $get('template_id');

                        if ($templateId === null) {
                            return [];
                        }

                        $template = EmailTemplate::query()->where('is_active', true)->find($templateId);

                        if ($template === null) {
                            return [];
                        }

                        $audience = (string) ($get('target_audience') ?? 'single');
                        $autoMap = self::recipientAutoVariables($audience);
                        $autoVariables = array_values(array_intersect($template->variables ?? [], array_keys($autoMap)));
                        $manualVariables = array_values(array_diff($template->variables ?? [], array_keys($autoMap)));

                        $components = [];

                        // Recipient-specific variables are resolved from each
                        // recipient's record — they are explained, not typed.
                        if ($autoVariables !== []) {
                            $components[] = Grid::make(2)->schema(
                                collect($autoVariables)
                                    ->map(fn (string $variable): TextEntry => TextEntry::make('auto_var_'.$variable)
                                        ->label(Str::headline($variable))
                                        ->state(fn (): string => 'Filled from each recipient’s '.self::autoVariableSource($autoMap[$variable]))
                                        ->color('info'))
                                    ->all()
                            );
                        }

                        $fields = collect($manualVariables)
                            ->map(fn (string $variable): TextInput => TextInput::make('variables.'.$variable)
                                ->label(Str::headline($variable))
                                ->placeholder("Enter the value for {{$variable}}")
                                ->helperText($audience === 'single'
                                    ? 'Personalises the email for this recipient.'
                                    : 'Used identically for every recipient.')
                                ->columnSpan(1))
                            ->all();

                        if ($fields !== []) {
                            $components[] = Grid::make(2)->schema($fields);
                        }

                        return $components;
                    }),

                Section::make('4. Preview and send')
                    ->schema(function (Get $get): array {
                        $templateId = $get('template_id');

                        if ($templateId === null) {
                            return [];
                        }

                        return [
                            TextEntry::make('subject_preview')
                                ->label('Subject preview')
                                ->state(fn (): string => self::previewSubject($templateId, $get)),
                            TextEntry::make('recipients_preview')
                                ->label('Recipients')
                                ->state(fn (): string => self::recipientsSummary($get)),
                        ];
                    }),
            ]);
    }

    public function send(): void
    {
        $data = $this->form->getState();

        Log::debug('send-manual-email: submit', ['data' => $data]);

        // 1. Manual template validation.
        if (blank($data['template_id'] ?? null)) {
            Notification::make()
                ->danger()
                ->title('Template Required')
                ->body('Please select an email template before sending.')
                ->send();

            return;
        }

        $template = EmailTemplate::query()
            ->where('is_active', true)
            ->find($data['template_id']);

        if ($template === null) {
            Notification::make()
                ->danger()
                ->title('Invalid Template')
                ->body('The selected template is no longer active or does not exist.')
                ->send();

            return;
        }

        $audience = (string) ($data['target_audience'] ?? 'single');
        $autoMap = self::recipientAutoVariables($audience);

        // 2. Strict placeholder preflight. A template that references a
        // placeholder nobody can fill is rejected before a single email is
        // queued — an unresolved {token} must never be sent.
        $unresolvable = self::unresolvablePlaceholders($template, $audience);

        if ($unresolvable !== []) {
            $list = implode(', ', array_map(fn (string $variable): string => '{'.$variable.'}', $unresolvable));

            Notification::make()
                ->danger()
                ->title('Unresolved Placeholder')
                ->body("The template references {$list}, which cannot be resolved for this audience. Fix the template or its declared variables first.")
                ->send();

            return;
        }

        // 3. Manually typed variables. Recipient-specific variables are
        // resolved from each recipient instead and are not typed here.
        $manualVariables = array_values(array_diff($template->variables ?? [], array_keys($autoMap)));
        $typed = [];
        $missingVariables = [];

        foreach ($manualVariables as $variable) {
            $value = trim((string) ($data['variables'][$variable] ?? ''));

            if ($value === '') {
                $missingVariables[] = $variable;
            }

            $typed[$variable] = $value;
        }

        if ($missingVariables !== []) {
            $missingList = implode(', ', array_map(fn (string $variable): string => '{'.$variable.'}', $missingVariables));

            Notification::make()
                ->warning()
                ->title('Missing Variables')
                ->body("Please fill in all required variables: {$missingList}")
                ->send();

            return;
        }

        // 4. Resolve and validate every recipient BEFORE queueing anything.
        $recipients = $this->getRecipients($data);

        $invalidEmails = $recipients
            ->keys()
            ->filter(fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) === false);

        $recipients = $recipients->filter(
            fn (?string $name, string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        );

        if ($recipients->isEmpty()) {
            $hasInvalid = $invalidEmails->isNotEmpty();

            Notification::make()
                ->warning()
                ->title($hasInvalid ? 'Invalid Recipient Email' : 'No Recipients Found')
                ->body($hasInvalid
                    ? 'One or more recipient email addresses are invalid.'
                    : 'No valid email addresses were found for the selected criteria.')
                ->send();

            return;
        }

        // 4b. Every recipient must actually have the data the template needs.
        // A recipient whose record is missing it is NOT emailed a guessed
        // value, and the whole batch is aborted before anything is queued.
        $incomplete = [];

        foreach ($recipients as $email => $name) {
            foreach ($template->variables ?? [] as $variable) {
                if (! isset($autoMap[$variable])) {
                    continue;
                }

                $value = $autoMap[$variable] === 'lead email' ? $email : $name;

                if (blank($value)) {
                    $incomplete[] = $email;
                    break;
                }
            }
        }

        if ($incomplete !== []) {
            $unique = array_values(array_unique($incomplete));
            $listed = implode(', ', array_slice($unique, 0, 5)).(count($unique) > 5 ? ' …' : '');

            Notification::make()
                ->danger()
                ->title('Recipient Data Incomplete')
                ->body("Some recipients are missing the data needed to render this email, so nothing was queued. Recipients affected: {$listed}")
                ->send();

            return;
        }

        // 5. Queue one independent, individually rendered delivery per
        // recipient. This request only writes queued EmailLog rows and
        // returns immediately — SendEmailJob performs the provider delivery
        // in the queue worker. A short-lived cache claim stops an accidental
        // repeated submission of the exact same delivery without blocking
        // legitimate future sends.
        $service = app(EmailService::class);
        $sent = 0;
        $failed = 0;
        $duplicates = 0;
        $recipientCount = $recipients->count();

        foreach ($recipients as $email => $name) {
            // Build THIS recipient's context: shared typed values plus any
            // recipient-specific variables resolved from their record. Every
            // recipient is therefore rendered separately inside EmailService.
            $recipientVariables = $typed;

            foreach ($template->variables ?? [] as $variable) {
                if (! isset($autoMap[$variable])) {
                    continue;
                }

                $recipientVariables[$variable] = $autoMap[$variable] === 'lead email' ? $email : $name;
            }

            $dedupeKey = 'manual-email:'.sha1($template->key.'|'.$email.'|'.serialize($recipientVariables));

            // Claim before queueing so two identical submissions cannot both
            // create an EmailLog; a failed queueing clears the claim so the
            // admin can retry the same send after fixing the problem.
            if (! Cache::add($dedupeKey, true, 60)) {
                $duplicates++;

                continue;
            }

            try {
                $result = $service->sendTemplate($template->key, $email, $recipientVariables, $name ?: null);

                if ($result['success'] ?? false) {
                    $sent++;
                } else {
                    $failed++;
                    Cache::forget($dedupeKey);
                }
            } catch (\Exception $exception) {
                $failed++;
                Cache::forget($dedupeKey);
                Log::error("Email send failed to {$email}: ".$exception->getMessage());
            }
        }

        // 6. Final notification.
        if ($failed > 0) {
            Notification::make()
                ->warning()
                ->title('Partially Successful')
                ->body("Sent {$sent} emails successfully, but {$failed} failed. Check Email Logs for details.")
                ->send();

            return;
        }

        if ($sent === 0 && $duplicates > 0) {
            Notification::make()
                ->info()
                ->title('No New Emails Queued')
                ->body('Every selected recipient was already queued for this exact send in the last minute — no duplicates were created.')
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Emails Sent Successfully!')
            ->body("Successfully sent {$sent} emails to {$recipientCount} ".Str::plural('recipient', $recipientCount).'.')
            ->send();

        // Reset the form after a successful send.
        $this->form->fill();
    }

    private static function getRecipientCount(Get $get): int
    {
        return match ($get('target_audience') ?? 'single') {
            'single' => filled($get('recipient_email')) ? 1 : 0,
            'segment' => Lead::query()
                ->where('interest', $get('lead_interest'))
                ->where('is_subscribed', true)
                ->whereNotNull('email')
                ->count(),
            default => Lead::query()
                ->where('is_subscribed', true)
                ->whereNotNull('email')
                ->count(),
        };
    }

    private static function recipientsSummary(Get $get): string
    {
        $count = self::getRecipientCount($get);

        if ($count === 0) {
            return 'No matching recipients found.';
        }

        $target = match ($get('target_audience') ?? 'single') {
            'single' => 'this address',
            'segment' => 'the selected lead segment',
            default => 'all subscribed leads',
        };

        return "This will send {$count} ".Str::plural('email', $count).' to '.$target.'.';
    }

    private static function variablesSectionDescription(Get $get): string
    {
        $templateId = $get('template_id');

        if ($templateId === null) {
            return 'Select a template first — its placeholders will appear here.';
        }

        $template = EmailTemplate::query()->where('is_active', true)->find($templateId);

        if ($template === null) {
            return 'Select a template first — its placeholders will appear here.';
        }

        $audience = (string) ($get('target_audience') ?? 'single');
        $autoMap = self::recipientAutoVariables($audience);
        $autoVariables = array_values(array_intersect($template->variables ?? [], array_keys($autoMap)));
        $settingsUsed = array_values(array_intersect(self::referencedPlaceholders($template), EmailTemplatePlaceholders::SETTINGS_BACKED_VARIABLES));

        $sentences = [];

        if ($autoVariables !== []) {
            $labels = collect($autoVariables)
                ->map(fn (string $variable): string => '{'.$variable.'} (each recipient’s '.self::autoVariableSource($autoMap[$variable]).')')
                ->implode(', ');

            $sentences[] = "Filled automatically from each recipient: {$labels}.";
        }

        $sentences[] = $audience === 'single'
            ? 'Values below personalise the email for this recipient.'
            : 'Values below are used identically for every recipient.';

        if ($settingsUsed !== []) {
            $labels = collect($settingsUsed)->map(fn (string $variable): string => '{'.$variable.'}')->implode(', ');

            $sentences[] = "Filled automatically from your site settings: {$labels}.";
        }

        return implode(' ', $sentences);
    }

    /**
     * Variables that are resolved from each recipient's own record instead of
     * being typed once for the whole batch. A "single" manual address has no
     * record behind it, so everything is typed there.
     *
     * @return array<string, string> variable name => source descriptor
     */
    private static function recipientAutoVariables(string $audience): array
    {
        return $audience === 'single'
            ? []
            : [
                'student_name' => 'lead name',
                'name' => 'lead name',
                'email' => 'lead email',
            ];
    }

    private static function autoVariableSource(string $source): string
    {
        return $source === 'lead email' ? 'email address' : 'name';
    }

    /**
     * Every {placeholder} token referenced by the template's subject/body.
     *
     * @return array<int, string>
     */
    private static function referencedPlaceholders(EmailTemplate $template): array
    {
        return EmailTemplatePlaceholders::referenced($template->subject, $template->body);
    }

    /**
     * Placeholders that can never be filled for the chosen audience — not
     * declared by the template, not settings-backed, and not resolvable from
     * the recipient records.
     *
     * @return array<int, string>
     */
    private static function unresolvablePlaceholders(EmailTemplate $template, string $audience): array
    {
        $allowed = array_unique(array_merge(
            $template->variables ?? [],
            EmailTemplatePlaceholders::SETTINGS_BACKED_VARIABLES,
            array_keys(self::recipientAutoVariables($audience)),
        ));

        return array_values(array_diff(self::referencedPlaceholders($template), $allowed));
    }

    private static function previewSubject(int $templateId, Get $get): string
    {
        $template = EmailTemplate::query()->where('is_active', true)->find($templateId);

        if ($template === null) {
            return '';
        }

        $audience = (string) ($get('target_audience') ?? 'single');
        $autoMap = self::recipientAutoVariables($audience);

        $variables = [];

        foreach ($template->variables ?? [] as $variable) {
            $typed = (string) $get('variables.'.$variable);

            if (filled($typed)) {
                $variables[$variable] = $typed;
            } elseif (isset($autoMap[$variable])) {
                // Preview only — the real value is resolved per recipient.
                $variables[$variable] = $autoMap[$variable] === 'lead email' ? '[recipient email]' : '[recipient name]';
            } else {
                $variables[$variable] = $typed;
            }
        }

        $service = app(EmailService::class);

        $values = array_merge($service->settingsVariables(), $variables);

        return $service->renderTemplate($template, $values)['subject'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<string, string|null>
     */
    private function getRecipients(array $data): Collection
    {
        return match ($data['target_audience'] ?? 'single') {
            'single' => filled($data['recipient_email'] ?? null)
                ? collect([(string) $data['recipient_email'] => null])
                : collect(),
            'segment' => $this->leadRecipients($data['lead_interest'] ?? null),
            default => $this->leadRecipients(),
        };
    }

    /**
     * @return Collection<string, string|null>
     */
    private function leadRecipients(?string $interest = null): Collection
    {
        return Lead::query()
            ->where('is_subscribed', true)
            ->whereNotNull('email')
            ->when($interest !== null, fn ($query) => $query->where('interest', $interest))
            ->pluck('name', 'email')
            ->map(fn (?string $name): ?string => $name);
    }
}
