<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ManagesLegalCmsPage;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class FaqCms extends Page
{
    use ManagesLegalCmsPage;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?int $navigationSort = 33;

    protected string $view = 'filament.pages.faq-cms';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content Management';
    }

    protected function pageTitle(): string
    {
        return 'FAQ';
    }

    protected function pageKey(): string
    {
        return 'faq';
    }
}
