<?php

namespace App\Filament\Pages\Concerns;

use App\Http\Controllers\StaticPageController;
use App\Models\Setting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

/**
 * Shared CMS editing behavior for the Terms / Privacy / Refund / FAQ pages.
 *
 * Content is stored as one JSON blob in the existing `settings` table under
 * `legal_page_{slug}`. The canonical defaults (StaticPageController) remain the
 * single source of truth until an administrator saves an override, so the
 * public pages never lose their existing copy.
 */
trait ManagesLegalCmsPage
{
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->loadContent());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('title')
                ->label('Page title')
                ->required()
                ->maxLength(255),
            Textarea::make('intro')
                ->label('Introductory text')
                ->rows(3),
            TextInput::make('updated_at')
                ->label('Last updated')
                ->maxLength(60),
            Repeater::make('sections')
                ->label('Content sections')
                ->addActionLabel('Add section')
                ->schema([
                    TextInput::make('heading')
                        ->label('Heading / question')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('body')
                        ->label('Body')
                        ->rows(6)
                        ->helperText('Separate paragraphs with a blank line.'),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $sections = [];

        foreach ($data['sections'] as $section) {
            if (blank($section['heading'] ?? null)) {
                continue;
            }

            $sections[] = [
                'heading' => trim((string) $section['heading']),
                'body' => self::paragraphsToArray($section['body'] ?? ''),
            ];
        }

        $payload = [
            'title' => trim((string) ($data['title'] ?? '')),
            'intro' => trim((string) ($data['intro'] ?? '')),
            'updated_at' => trim((string) ($data['updated_at'] ?? '')),
            'sections' => $sections,
        ];

        Setting::updateOrCreate(
            ['key' => $this->settingsKey()],
            ['value' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)]
        );

        Notification::make()
            ->success()
            ->title($this->pageTitle().' content saved successfully.')
            ->send();
    }

    abstract protected function pageTitle(): string;

    abstract protected function pageKey(): string;

    protected function settingsKey(): string
    {
        return 'legal_page_'.$this->pageKey();
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadContent(): array
    {
        $defaults = StaticPageController::defaultContent($this->pageKey()) ?? [];

        $raw = Setting::where('key', $this->settingsKey())->value('value');
        $stored = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;

        if (! is_array($stored)) {
            $stored = [];
        }

        $merged = array_replace($defaults, $stored);

        foreach ($merged['sections'] ?? [] as &$section) {
            if (! is_array($section)) {
                continue;
            }
            $section['body'] = self::paragraphsToString($section['body'] ?? []);
        }
        unset($section);

        return $merged;
    }

    /**
     * @param  array<int, string>|null  $paragraphs
     */
    protected static function paragraphsToString(mixed $paragraphs): string
    {
        if (! is_array($paragraphs)) {
            return (string) ($paragraphs ?? '');
        }

        return implode("\n\n", array_map('trim', $paragraphs));
    }

    protected static function paragraphsToArray(?string $body): array
    {
        $body = trim((string) $body);

        if ($body === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\R\R+/', $body) ?: [])));
    }
}
