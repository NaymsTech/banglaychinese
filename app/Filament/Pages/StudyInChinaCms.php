<?php

namespace App\Filament\Pages;

use App\Models\StudyInChinaSection;
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

class StudyInChinaCms extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-asia-australia';

    protected static ?string $navigationLabel = 'Study in China CMS';

    protected string $view = 'filament.pages.study-in-china-cms';

    protected static array $groupTitles = [
        'hero' => 'Hero Section',
        'why_china' => 'Why Study in China',
        'why_us' => 'Why Choose BanglayChinese',
        'roadmap' => 'Study Abroad Roadmap',
        'comparison' => 'Comparison Table',
        'scholarships' => 'Scholarship Opportunities',
        'quote' => 'Success Philosophy',
        'faqs' => 'Frequently Asked Questions',
        'booking' => 'Consultation Booking',
        'final_cta' => 'Final Call to Action',
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
        // Service packages are managed under Admin → Services; the redundant
        // legacy 'services' group is excluded (same as the legacy editor).
        return StudyInChinaSection::query()
            ->where('group', '!=', 'services')
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

    protected function valueForField(StudyInChinaSection $section): ?string
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

        if ($section->type === 'image') {
            // Stored values keep a 'storage/' prefix; the upload component needs
            // the bare disk-relative path for its preview.
            return Str::startsWith($section->value, 'storage/')
                ? Str::after($section->value, 'storage/')
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
                    'json' => Textarea::make("v_{$section->id}")
                        ->label('Value (JSON)')
                        ->rows(6)
                        ->helperText("Key: {$section->key} — JSON is validated & reformatted on save.")
                        ->columnSpanFull(),
                    'image' => FileUpload::make("v_{$section->id}")
                        ->label('Image')
                        ->image()
                        ->disk('public')
                        ->directory('study-in-china')
                        ->maxSize(5120)
                        ->columnSpanFull(),
                    default => Textarea::make("v_{$section->id}")
                        ->label('Value')
                        ->rows(filled($section->value) && mb_strlen((string) $section->value) > 300 ? 6 : 3)
                        ->helperText("Key: {$section->key}")
                        ->columnSpanFull(),
                };
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
                    $section->value = 'storage/' . ltrim((string) $value, '/');
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
            ->title('Study in China content saved successfully.')
            ->send();
    }
}
