<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SettingsService;
use App\Support\HomePageContent;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class HomePageCms extends Page
{
    /**
     * Settings keys backed by FileUpload fields; stored as a plain path string
     * but hydrated as an array for the upload component.
     */
    protected const FILE_FIELDS = ['hero_image', 'founder_image', 'award_image', 'home_og_image'];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Home Page CMS';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content Management';
    }

    protected string $view = 'filament.pages.home-page-cms';

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
     * even on first visit.
     *
     * @return array<string, mixed>
     */
    protected function storedValues(): array
    {
        $stored = Setting::whereIn('key', array_keys(HomePageContent::defaults()))
            ->pluck('value', 'key')
            ->toArray();

        $values = array_replace(HomePageContent::defaults(), $stored);

        foreach (self::FILE_FIELDS as $key) {
            if (! blank($values[$key] ?? null)) {
                $values[$key] = is_array($values[$key]) ? $values[$key] : [$values[$key]];
            }
        }

        return $values;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            $this->heroSection(),
            $this->serviceCardsSection(),
            $this->whyChooseUsSection(),
            $this->whatsappSection(),
            $this->mentorSection(),
            $this->awardsSection(),
            $this->finalCtaSection(),
            $this->seoSection(),
        ]);
    }

    protected function heroSection(): Section
    {
        return Section::make('Hero')
            ->description('Top banner: badge, headline, subtitle, the two call-to-action buttons and the photo.')
            ->columns(2)
            ->schema([
                TextInput::make('hero_badge')
                    ->label('Badge text')
                    ->helperText('Shown in the pill above the headline, e.g. "New Batch Open · ভর্তি চলছে".'),
                Textarea::make('hero_title')
                    ->label('Headline')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('One sentence per line — the last line is highlighted in the brand colour.'),
                Textarea::make('hero_subtitle')
                    ->label('Subtitle')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('hero_cta1_text')
                    ->label('Primary button text'),
                TextInput::make('hero_cta1_link')
                    ->label('Primary button link')
                    ->helperText('Internal path (e.g. /courses) or full URL.'),
                TextInput::make('hero_cta2_text')
                    ->label('Secondary button text'),
                TextInput::make('hero_cta2_link')
                    ->label('Secondary button link')
                    ->helperText('Internal path or full URL — opens in a new tab.'),
                $this->imageField('hero_image', 'Hero image', 'Recommended size: 1600×900px. Max 5 MB.')
                    ->columnSpanFull(),
            ]);
    }

    protected function serviceCardsSection(): Section
    {
        return Section::make('3-Card Service Grid')
            ->description('The three cards that float over the hero. The Bengali tagline on each card and the "Coming Soon" badge on Card 3 are fixed in the template.')
            ->schema([
                $this->serviceCardSection(1, true),
                $this->serviceCardSection(2, true),
                $this->serviceCardSection(3, false),
            ]);
    }

    protected function serviceCardSection(int $number, bool $withLink): Section
    {
        $fields = [
            $this->iconField("card_{$number}_icon", 'Icon', 'Emoji shown in the card chip.'),
            TextInput::make("card_{$number}_title")
                ->label('Title'),
            Textarea::make("card_{$number}_desc")
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),
        ];

        if ($withLink) {
            $fields[] = TextInput::make("card_{$number}_link")
                ->label('Link')
                ->helperText('Internal path or full URL.');
        }

        return Section::make("Card {$number}")
            ->columns(2)
            ->schema($fields);
    }

    protected function whyChooseUsSection(): Section
    {
        $points = [];

        foreach (range(1, 4) as $number) {
            $points[] = Section::make("Point {$number}")
                ->columns(2)
                ->schema([
                    $this->iconField("why_{$number}_icon", 'Icon', 'Emoji shown in the circular badge above the point.'),
                    TextInput::make("why_{$number}_title")
                        ->label('Title'),
                    Textarea::make("why_{$number}_desc")
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]);
        }

        return Section::make('Why Choose Us')
            ->description('The four trust points under the "Why Choose Banglay Chinese?" heading. The heading itself is fixed in the template.')
            ->schema($points);
    }

    protected function whatsappSection(): Section
    {
        return Section::make('WhatsApp CTA')
            ->description('The green quick-response band above the services grid.')
            ->columns(2)
            ->schema([
                TextInput::make('wa_heading')
                    ->label('Heading'),
                Textarea::make('wa_subtext')
                    ->label('Subtext')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('wa_number')
                    ->label('WhatsApp number')
                    ->helperText('Full international number without "+" or spaces — e.g. 8618223249514.'),
            ]);
    }

    protected function mentorSection(): Section
    {
        return Section::make('Meet Your Mentor')
            ->description('The dark founder band: headline, big Bengali quote, photo, bio and CTA. The last word of the quote is coloured red automatically.')
            ->columns(2)
            ->schema([
                TextInput::make('mentor_heading')
                    ->label('Heading'),
                TextInput::make('mentor_subtitle')
                    ->label('Subtitle (optional)'),
                $this->imageField('founder_image', 'Founder photo', 'Recommended size: 800×800px portrait. Max 5 MB.')
                    ->columnSpanFull(),
                Textarea::make('founder_quote')
                    ->label('Big Bengali quote')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('The last word of this line is highlighted red on the page.'),
                RichEditor::make('founder_bio')
                    ->label('Bio')
                    ->columnSpanFull(),
                TextInput::make('founder_name')
                    ->label('Founder name'),
                TextInput::make('founder_role')
                    ->label('Founder role'),
                TextInput::make('founder_cta_text')
                    ->label('Button text'),
                TextInput::make('founder_cta_link')
                    ->label('Button link')
                    ->helperText('Internal path or full URL.'),
            ]);
    }

    protected function awardsSection(): Section
    {
        return Section::make('Awards & Recognition')
            ->description('Awards band: bordered label, split-colour heading and the award card. The photo on the card (left side) can be replaced here; when empty a default image is shown.')
            ->columns(2)
            ->schema([
                TextInput::make('award_eyebrow')
                    ->label('Bordered label'),
                TextInput::make('award_subheading')
                    ->label('Small subheading'),
                TextInput::make('award_heading')
                    ->label('Heading')
                    ->columnSpanFull()
                    ->helperText('The last word of the heading is highlighted in green.'),
                $this->imageField('award_image', 'Award / Recognition photo', 'Recommended: high-quality photo of the award certificate or the stage performance. Max 5 MB.', 'home/awards')
                    ->columnSpanFull(),
                TextInput::make('award_title')
                    ->label('Award title')
                    ->columnSpanFull(),
                RichEditor::make('award_description')
                    ->label('Description')
                    ->columnSpanFull(),
                Textarea::make('award_quote')
                    ->label('Quote')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    protected function finalCtaSection(): Section
    {
        return Section::make('Final CTA')
            ->description('Closing band at the bottom of the page.')
            ->columns(2)
            ->schema([
                TextInput::make('final_heading')
                    ->label('Heading')
                    ->columnSpanFull(),
                Textarea::make('final_subtext')
                    ->label('Subtext')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('final_btn1_text')
                    ->label('Button 1 text'),
                TextInput::make('final_btn1_link')
                    ->label('Button 1 link')
                    ->helperText('Internal path or full URL — opens in a new tab.'),
                TextInput::make('final_btn2_text')
                    ->label('Button 2 text'),
                TextInput::make('final_btn2_link')
                    ->label('Button 2 link')
                    ->helperText('Internal path or full URL.'),
            ]);
    }

    protected function seoSection(): Section
    {
        return Section::make('SEO & Social Sharing')
            ->description('Search-engine title and description, plus the image used when the homepage is shared. Not visible on the page itself.')
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('home_meta_title')
                    ->label('Meta title')
                    ->columnSpanFull(),
                Textarea::make('home_meta_description')
                    ->label('Meta description')
                    ->rows(3)
                    ->columnSpanFull(),
                $this->imageField('home_og_image', 'Social share image (OG)', 'Recommended size: 1200×630px. Max 5 MB.'),
            ]);
    }

    protected function iconField(string $key, string $label, string $helper): Select
    {
        return Select::make($key)
            ->label($label)
            ->options(HomePageContent::icons())
            ->helperText($helper);
    }

    protected function imageField(string $key, string $label, string $helper, string $directory = 'home'): FileUpload
    {
        return FileUpload::make($key)
            ->label($label)
            ->disk('public')
            ->directory($directory)
            ->image()
            ->maxSize(5120)
            ->visibility('public')
            ->helperText($helper);
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            SettingsService::set($key, is_array($value) ? ($value[0] ?? '') : trim((string) ($value ?? '')));
        }

        SettingsService::flush();

        Notification::make()
            ->success()
            ->title('Home page content saved successfully.')
            ->send();
    }
}
