<?php

namespace App\Filament\Pages;

use App\Models\AboutSection;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class AboutPageCms extends Page
{
    /** Section groups in the order they appear on the public About page. */
    protected const GROUP_ORDER = [
        'hero', 'story', 'timeline', 'experience', 'why', 'mission',
        'commitment', 'vision', 'choose_us', 'faq', 'cta',
    ];

    protected const GROUP_TITLES = [
        'hero' => 'Founder Hero',
        'story' => 'Story',
        'timeline' => 'Timeline',
        'experience' => 'Experience & Recognition',
        'why' => 'Why',
        'mission' => 'Mission',
        'commitment' => 'Commitment',
        'vision' => 'Vision',
        'choose_us' => 'Why Choose Us',
        'faq' => 'FAQ',
        'cta' => 'Final CTA',
    ];

    /**
     * Plain multi-paragraph rows that are edited as rich text; the blade
     * renders them as HTML.
     */
    protected const RICH_TEXT_KEYS = ['story_content', 'why_content', 'vision_content'];

    /** Rows rendered as image uploads, with their recommended size hint. */
    protected const IMAGE_HINTS = [
        'hero_image' => 'Portrait photo — recommended size: 800×1000px (3:4). Max 5 MB.',
    ];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'About Page CMS';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content Management';
    }

    protected string $view = 'filament.pages.about-page-cms';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->buildInitialData());
    }

    protected function sectionsByGroup(): Collection
    {
        $grouped = AboutSection::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        $ordered = [];

        foreach (self::GROUP_ORDER as $group) {
            if (isset($grouped[$group])) {
                $ordered[$group] = $grouped[$group];
            }
        }

        foreach ($grouped->keys() as $group) {
            if (! isset($ordered[$group])) {
                $ordered[$group] = $grouped[$group];
            }
        }

        return collect($ordered);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildInitialData(): array
    {
        $data = [];

        foreach ($this->sectionsByGroup()->flatten(1) as $section) {
            $data["v_{$section->id}"] = $this->valueForField($section);
        }

        return $data;
    }

    protected function valueForField(AboutSection $section): mixed
    {
        if ($section->type === 'json') {
            $decoded = json_decode((string) $section->value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return $section->value;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $components = [];

        foreach ($this->sectionsByGroup() as $group => $sections) {
            $fields = [];

            foreach ($sections as $section) {
                $fields[] = $this->fieldFor($section);
            }

            $components[] = Section::make(self::GROUP_TITLES[$group] ?? Str::headline($group))
                ->description(count($sections).' content '.Str::plural('item', count($sections)))
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema($fields);
        }

        return $schema->schema($components);
    }

    protected function fieldFor(AboutSection $section)
    {
        $key = "v_{$section->id}";
        $label = $section->label ?? Str::headline($section->key);

        return match (true) {
            $section->type === 'image' => FileUpload::make($key)
                ->label($label)
                ->disk('public')
                ->image()
                ->maxSize(5120)
                ->visibility('public')
                ->directory('about')
                ->helperText(self::IMAGE_HINTS[$section->key] ?? 'Max 5 MB. JPG, PNG or WebP.')
                ->columnSpanFull(),

            $section->type === 'json' => $this->repeaterFor($section),

            in_array($section->key, self::RICH_TEXT_KEYS, true) => RichEditor::make($key)
                ->label($label)
                ->columnSpanFull(),

            str_ends_with((string) $section->key, '_url') => TextInput::make($key)
                ->label($label)
                ->helperText('Internal path (e.g. /courses) or full URL.'),

            $section->type === 'string' => TextInput::make($key)
                ->label($label),

            default => Textarea::make($key)
                ->label($label)
                ->rows(4)
                ->columnSpanFull(),
        };
    }

    protected function repeaterFor(AboutSection $section): Repeater
    {
        $itemFields = match ($section->key) {
            'hero_highlights' => [
                TextInput::make('icon')->label('Icon (emoji)'),
                TextInput::make('text')->label('Text'),
            ],
            'timeline_items' => [
                TextInput::make('year')->label('Year'),
                TextInput::make('title')->label('Title'),
                Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
            ],
            'faq_items' => [
                Textarea::make('question')->label('Question')->rows(2)->columnSpanFull(),
                Textarea::make('answer')->label('Answer')->rows(3)->columnSpanFull(),
            ],
            default => [
                TextInput::make('icon')->label('Icon (emoji)'),
                TextInput::make('title')->label('Title'),
                Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
            ],
        };

        return Repeater::make("v_{$section->id}")
            ->label(trim((string) preg_replace('/\s*\(.*?\)\s*$/', '', (string) ($section->label ?? ''))))
            ->schema($itemFields)
            ->grid($section->key === 'faq_items' ? 1 : 2)
            ->addActionLabel('Add item')
            ->columnSpanFull();
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $rows = $this->sectionsByGroup()->flatten(1);

        foreach ($rows as $section) {
            if (! array_key_exists("v_{$section->id}", $data)) {
                continue;
            }

            $value = $data["v_{$section->id}"];

            if ($section->type === 'json') {
                $section->value = blank($value)
                    ? null
                    : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } elseif ($section->type === 'image') {
                $section->value = filled($value)
                    ? (is_array($value) ? ($value[0] ?? '') : $value)
                    : null;
            } else {
                $section->value = blank($value)
                    ? null
                    : trim(is_array($value) ? ($value[0] ?? '') : (string) $value);
            }

            $section->save();
        }

        // About content is read straight from the DB on every request; flush
        // anyway so no settings-driven fallback ever serves stale values.
        SettingsService::flush();

        Notification::make()
            ->success()
            ->title('About page content saved successfully.')
            ->send();
    }
}
