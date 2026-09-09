<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')
                ->description('The course name, public URL, category and target HSK band.')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $get, callable $set): void {
                            if (filled($state) && blank($get('slug'))) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Auto-generated from the title — adjust only when necessary.'),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->label('Category')
                        ->searchable()
                        ->preload()
                        ->helperText('Groups this course with others on the site.'),
                    Select::make('hsk_level')
                        ->label('HSK level')
                        ->options([
                            1 => 'HSK 1',
                            2 => 'HSK 2',
                            3 => 'HSK 3',
                            4 => 'HSK 4',
                        ])
                        ->helperText('The HSK band this course prepares students for.'),
                ]),

            Section::make('Course Details')
                ->description('Length, fee and the start date of the next batch.')
                ->columns(2)
                ->schema([
                    TextInput::make('duration_months')
                        ->label('Duration (months)')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('e.g. 3')
                        ->helperText('How long the course runs, in months.'),
                    TextInput::make('price')
                        ->label('Price (৳)')
                        ->required()
                        ->numeric()
                        ->prefix('৳')
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Course fee in Bangladeshi Taka — 0 means free.'),
                    DatePicker::make('batch_start_date')
                        ->label('Start date')
                        ->helperText('Optional — when the next batch begins.')
                        ->columnSpanFull(),
                ]),

            Section::make('Content')
                ->description('Long description shown on the public course page. The syllabus is managed as Modules below.')
                ->schema([
                    RichEditor::make('description')
                        ->label('Course description')
                        ->columnSpanFull()
                        ->helperText('What students learn and why this course fits them. Supports rich text.'),
                ]),

            Section::make('Media')
                ->schema([
                    self::imageField(
                        'thumbnail',
                        'Thumbnail',
                        'courses/thumbnails',
                        'Recommended size: 1280×720px (16:9). Max 5 MB. JPG, PNG or WebP.'
                    ),
                ]),

            Section::make('Publishing')
                ->description('Draft courses are hidden; featured courses are highlighted on the homepage.')
                ->columns(2)
                ->schema([
                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(false),
                    Toggle::make('is_featured')
                        ->label('Featured')
                        ->default(false),
                ]),

            Section::make('SEO & Social Sharing')
                ->description('Optional overrides — blank fields fall back to the course title, description and thumbnail.')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->maxLength(60)
                        ->helperText('Shown in search results & social shares. Max 60 characters.'),
                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->maxLength(160)
                        ->helperText('Short summary shown under the title in search results (~160 characters).'),
                    self::imageField(
                        'og_image',
                        'Social share image (OG)',
                        'courses/og',
                        'Recommended size: 1200×630px. Max 5 MB. Falls back to the thumbnail.'
                    ),
                ]),
        ]);
    }

    protected static function imageField(string $name, string $label, string $directory, string $helper): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->disk('public')
            ->image()
            ->maxSize(5120)
            ->visibility('public')
            ->directory($directory)
            ->columnSpanFull()
            ->helperText($helper);
    }
}
