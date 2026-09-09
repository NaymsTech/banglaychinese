<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Product Info')
                ->description('Title, price and category — shown to students on the shop page.')
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
                        ->helperText('URL-friendly identifier — auto-filled from the title.'),
                    TextInput::make('price')
                        ->label('Price (৳)')
                        ->required()
                        ->numeric()
                        ->prefix('৳')
                        ->minValue(0)
                        ->maxLength(10),
                    TextInput::make('category')
                        ->placeholder('e.g. E-book, Practice Tests, Study Notes')
                        ->maxLength(255)
                        ->helperText('Optional — used to group products on the shop page.'),
                ]),

            Section::make('Product Files')
                ->description('The cover image is public and shown on the shop card.')
                ->schema([
                    FileUpload::make('cover_image')
                        ->label('Cover image')
                        ->disk('public')
                        ->directory('products/covers')
                        ->image()
                        ->imageEditor()
                        ->maxSize(2048)
                        ->visibility('public')
                        ->columnSpanFull()
                        ->helperText('Shown on the shop card and order confirmation. Max 2 MB.'),
                ]),

            Section::make('Product Delivery')
                ->description('Deliver the file by uploading a PDF (stored privately, streamed after approval) or by providing an external download link. Fill one of the two — the external link wins when both are set.')
                ->columns(2)
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Product file (PDF)')
                        ->disk('local')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(20480)
                        ->columnSpanFull()
                        ->helperText('Optional — stored privately and only delivered upon approval. Leave empty when using an external link below.'),
                    TextInput::make('external_download_url')
                        ->label('External download URL')
                        ->url()
                        ->placeholder('https://drive.google.com/…')
                        ->maxLength(500)
                        ->columnSpanFull()
                        ->helperText('Optional — e.g. a Google Drive / Dropbox link. Used only when no PDF is uploaded, unless you want the external link to take priority.'),
                ]),

            Section::make('Publishing')
                ->schema([
                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(true)
                        ->helperText('Unpublished products are hidden from the shop.'),
                ]),
        ]);
    }
}
