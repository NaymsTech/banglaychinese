<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SettingsService;
use App\Support\StudyInChinaContent;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use UnitEnum;

class StudyInChinaCms extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-asia-australia';

    protected static ?string $navigationLabel = 'Study in China CMS';

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content Management';
    }

    protected string $view = 'filament.pages.study-in-china-cms';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->storedValues());
    }

    /**
     * Stored settings on top of the code defaults, so every field is populated
     * even on first visit. List keys are stored as JSON and decoded back here.
     *
     * @return array<string, mixed>
     */
    protected function storedValues(): array
    {
        $defaults = StudyInChinaContent::defaults();
        $stored = Setting::whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->toArray();

        $values = $defaults;

        foreach ($stored as $key => $value) {
            if (blank($value)) {
                continue;
            }

            $values[$key] = is_array($defaults[$key] ?? null)
                ? (json_decode((string) $value, true) ?? [])
                : $value;
        }

        return $values;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $components = [];

        foreach (StudyInChinaContent::GROUPS as $group => $keys) {
            $fields = [];

            foreach ($keys as $key) {
                $fields[] = $this->fieldFor($key);
            }

            $components[] = Section::make($group)
                ->description(count($fields).' content '.Str::plural('item', count($fields)))
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema($fields);
        }

        return $schema->schema($components);
    }

    protected function fieldFor(string $key)
    {
        $definition = StudyInChinaContent::fields()[$key] ?? ['label' => Str::headline($key), 'type' => 'text'];
        $label = $definition['label'];

        return match ($definition['type']) {
            'list' => $this->repeaterFor($key, $label),
            'rich' => RichEditor::make($key)
                ->label($label)
                ->columnSpanFull(),
            'area' => Textarea::make($key)
                ->label($label)
                ->rows(4)
                ->columnSpanFull(),
            default => TextInput::make($key)
                ->label($label),
        };
    }

    protected function repeaterFor(string $key, string $label): Repeater
    {
        $itemFields = match ($key) {
            'sic_hero_checklist' => [
                TextInput::make('title')->label('Title'),
                TextInput::make('sub')->label('Sub (Bangla)'),
            ],
            'sic_trust_values', 'sic_why_china_cards' => [
                TextInput::make('icon')->label('Icon (emoji)'),
                TextInput::make('title')->label('Title'),
                Textarea::make('desc')->label('Description')->rows(2)->columnSpanFull(),
            ],
            'sic_programs_cards' => [
                TextInput::make('deg')->label('Degree'),
                TextInput::make('for')->label('For whom'),
                Textarea::make('desc')->label('Description')->rows(2)->columnSpanFull(),
            ],
            'sic_scholarships_cards', 'sic_human_steps', 'sic_diff_rows' => [
                TextInput::make('title')->label('Title'),
                Textarea::make('desc')->label('Description')->rows(2)->columnSpanFull(),
            ],
            'sic_eligibility_cards' => [
                TextInput::make('t')->label('Title'),
                Textarea::make('d')->label('Description')->rows(2)->columnSpanFull(),
            ],
            'sic_eligibility_factors', 'sic_next_steps_list' => [
                Textarea::make($key === 'sic_eligibility_factors' ? 'label' : 'text')
                    ->label($key === 'sic_eligibility_factors' ? 'Factor' : 'Sentence')
                    ->rows(2),
            ],
            'sic_roadmap_steps', 'sic_why_banglay_pillars' => [
                TextInput::make('num')->label('Number'),
                TextInput::make('title')->label('Title'),
                Textarea::make('desc')->label('Description')->rows(2)->columnSpanFull(),
            ],
            'sic_decision_quotes' => [
                Textarea::make('quote')->label('Quote')->rows(2),
            ],
            'sic_faq_items' => [
                Textarea::make('question')->label('Question')->rows(2)->columnSpanFull(),
                RichEditor::make('answer')->label('Answer')->columnSpanFull(),
            ],
            default => [
                TextInput::make('text')->label('Text'),
            ],
        };

        return Repeater::make($key)
            ->label($label)
            ->schema($itemFields)
            ->grid($key === 'sic_faq_items' ? 1 : 2)
            ->addActionLabel('Add item')
            ->columnSpanFull();
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            SettingsService::set(
                $key,
                is_array($value)
                    ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    : trim((string) ($value ?? ''))
            );
        }

        SettingsService::flush();

        Notification::make()
            ->success()
            ->title('Study in China content saved successfully.')
            ->send();
    }
}
