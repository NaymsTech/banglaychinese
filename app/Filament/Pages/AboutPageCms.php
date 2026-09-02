<?php

namespace App\Filament\Pages;

use App\Models\AboutSection;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AboutPageCms extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'About Page CMS';

    protected string $view = 'filament.pages.about-page-cms';

    protected static array $groupTitles = [
        'hero' => 'Hero',
        'story' => 'Story',
        'mission' => 'Mission',
        'vision' => 'Vision',
        'why' => 'Why Section',
        'experience' => 'Experience',
        'commitment' => 'Commitment',
        'choose_us' => 'Choose Us',
        'faq' => 'FAQ',
        'cta' => 'CTA',
        'timeline' => 'Timeline',
    ];

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
        return AboutSection::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildInitialData(): array
    {
        $data = [];

        foreach ($this->sectionsByGroup()->flatten(1) as $section) {
            $data["v_{$section->id}"] = $this->valueForField($section);
            $data["l_{$section->id}"] = $section->label;
            $data["o_{$section->id}"] = $section->sort_order;
        }

        return $data;
    }

    protected function valueForField(AboutSection $section): ?string
    {
        if (blank($section->value)) {
            return null;
        }

        if ($section->type === 'json') {
            $decoded = json_decode($section->value, true);

            return json_last_error() === JSON_ERROR_NONE
                ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : $section->value;
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
                $label = $section->label ?? Str::headline($section->key);

                $fields[] = TextInput::make("l_{$section->id}")
                    ->label('Label');
                $fields[] = TextInput::make("o_{$section->id}")
                    ->label('Order')
                    ->numeric()
                    ->default(0);

                $fields[] = match ($section->type) {
                    'string' => TextInput::make("v_{$section->id}")
                        ->label('Value')
                        ->helperText("Key: {$section->key}"),
                    'json' => Textarea::make("v_{$section->id}")
                        ->label('Value (JSON)')
                        ->rows(6)
                        ->helperText("Key: {$section->key} — JSON is validated & reformatted on save.")
                        ->columnSpanFull(),
                    'image' => FileUpload::make("v_{$section->id}")
                        ->label('Image')
                        ->image()
                        ->disk('public')
                        ->directory('about')
                        ->maxSize(5120)
                        ->columnSpanFull(),
                    default => Textarea::make("v_{$section->id}")
                        ->label('Value')
                        ->rows(4)
                        ->helperText("Key: {$section->key}")
                        ->columnSpanFull(),
                };

                // String values stay on the 2-column grid; longer content spans full width.
                if ($section->type === 'string') {
                    end($fields)->columnSpan(1);
                }
            }

            $components[] = Section::make(self::$groupTitles[$group] ?? Str::headline($group))
                ->description(count($sections) . ' content ' . Str::plural('item', count($sections)))
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema($fields);
        }

        return $schema->schema($components);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $rows = $this->sectionsByGroup()->flatten(1)->keyBy(fn ($row) => $row->id);

        // Pass 1: validate every JSON value before persisting anything.
        foreach ($rows as $section) {
            if ($section->type !== 'json') {
                continue;
            }

            $value = $data["v_{$section->id}"] ?? null;

            if (blank($value)) {
                continue;
            }

            json_decode((string) $value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Notification::make()
                    ->danger()
                    ->title('Invalid JSON in "'.($section->label ?? $section->key).'".')
                    ->body('Check the value and try again — nothing was saved.')
                    ->send();

                return;
            }
        }

        // Pass 2: persist.
        foreach ($rows as $section) {
            $value = $data["v_{$section->id}"] ?? null;

            if ($section->type === 'json') {
                $section->value = blank($value)
                    ? null
                    : json_encode(json_decode((string) $value, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } elseif ($section->type === 'image') {
                if (filled($value)) {
                    $section->value = $value;
                }
            } else {
                $section->value = blank($value) ? null : trim((string) $value);
            }

            $section->label = trim((string) ($data["l_{$section->id}"] ?? $section->label));
            $section->sort_order = (int) ($data["o_{$section->id}"] ?? $section->sort_order);
            $section->save();
        }

        Notification::make()
            ->success()
            ->title('About page content saved successfully.')
            ->send();
    }
}
