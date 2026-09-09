<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ManagesLegalCmsPage;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class TermsAndConditionsCms extends Page
{
    use ManagesLegalCmsPage;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Terms & Conditions';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.terms-and-conditions-cms';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content Management';
    }

    protected function pageTitle(): string
    {
        return 'Terms & Conditions';
    }

    protected function pageKey(): string
    {
        return 'terms-and-conditions';
    }
}
