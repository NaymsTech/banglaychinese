<?php

namespace App\Filament\Resources\FreeResources\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FreeResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Info')
                ->description('The title and category shown to students on the Free Resources page.')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. HSK 1 Core Vocabulary List')
                        ->columnSpanFull(),
                    Select::make('category')
                        ->options([
                            'HSK 1' => 'HSK 1',
                            'HSK 2' => 'HSK 2',
                            'HSK 3' => 'HSK 3',
                            'HSK 4' => 'HSK 4',
                            'Grammar' => 'Grammar',
                            'Vocabulary' => 'Vocabulary',
                            'Study Tips' => 'Study Tips',
                        ])
                        ->searchable()
                        ->required()
                        ->helperText('Resources are grouped under this heading on the public page.'),
                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('A short summary of what students will get…')
                        ->columnSpanFull()
                        ->helperText('Optional — shown under the title on the resource card.'),
                ]),

            Section::make('Resource Type')
                ->description('Choose what this resource provides, then fill in the matching details below.')
                ->schema([
                    Select::make('resource_type')
                        ->options([
                            'pdf' => 'PDF download',
                            'video' => 'Embedded video',
                            'link' => 'External link',
                        ])
                        ->required()
                        ->default('pdf')
                        ->live()
                        ->helperText('Determines how the resource is presented on the public page.'),
                ]),

            Section::make('Resource Details')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Upload PDF')
                        ->acceptedFileTypes(['application/pdf'])
                        ->disk('public')
                        ->directory('resources/pdfs')
                        ->maxSize(10240)
                        ->visibility('public')
                        ->columnSpanFull()
                        ->helperText('This will generate a direct download link.')
                        ->hidden(fn (Get $get): bool => $get('resource_type') !== 'pdf')
                        ->required(fn (Get $get): bool => $get('resource_type') === 'pdf'),
                    TextInput::make('embed_url')
                        ->label(fn (Get $get): string => $get('resource_type') === 'video' ? 'Video embed URL' : 'External link URL')
                        ->helperText(fn (Get $get): string => $get('resource_type') === 'video'
                            ? 'Paste the YouTube embed URL (e.g., https://www.youtube.com/embed/VIDEO_ID) — it must use the /embed/ form.'
                            : 'Paste the full URL students will open (e.g., https://example.com/guide).')
                        ->placeholder(fn (Get $get): string => $get('resource_type') === 'video'
                            ? 'https://www.youtube.com/embed/VIDEO_ID'
                            : 'https://…')
                        ->url()
                        ->maxLength(500)
                        ->columnSpanFull()
                        ->hidden(fn (Get $get): bool => ! in_array($get('resource_type'), ['video', 'link'], true))
                        ->required(fn (Get $get): bool => in_array($get('resource_type'), ['video', 'link'], true)),
                ]),

            Section::make('Settings')
                ->columns(2)
                ->schema([
                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(true)
                        ->helperText('Unpublished resources are hidden from students.'),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Lower numbers appear first within their category.'),
                ]),
        ]);
    }
}
