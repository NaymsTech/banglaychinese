<?php

namespace App\Filament\Resources\Enrollments\Pages;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use Filament\Resources\Pages\ListRecords;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Enrollments are created by students during checkout — not by admins.
        ];
    }
}
