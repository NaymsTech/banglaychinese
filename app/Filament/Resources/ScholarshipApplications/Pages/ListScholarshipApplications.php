<?php

namespace App\Filament\Resources\ScholarshipApplications\Pages;

use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListScholarshipApplications extends ListRecords
{
    protected static string $resource = ScholarshipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Applications/leads are submitted through the public website.
        ];
    }
}
