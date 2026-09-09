<?php

namespace App\Filament\Resources\Enrollments\Pages;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Models\Enrollment;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use App\Support\Concerns\FillsBuyerSnapshotFromUser;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateEnrollment extends CreateRecord
{
    use FillsBuyerSnapshotFromUser;

    protected static string $resource = EnrollmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->fillBuyerSnapshotFromUser($data);

        // A sale recorded as already fully paid is an actual received
        // payment: timestamp it so the mirrored canonical Payment carries
        // the real paid_at instead of a null.
        if (($data['payment_status'] ?? null) === Enrollment::PAYMENT_STATUS_PAID && blank($data['paid_at'] ?? null)) {
            $data['paid_at'] = Carbon::now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Admin-recorded sales must exist in the canonical Order/Payment
        // structure too. Materialization is idempotent on the legacy key and
        // never touches the legacy row created by the form.
        app(OrderMaterializer::class)->materialize($this->record);

        // An offline sale created as fully paid is a settled sale: queue the
        // outcome email through the existing canonical marker exactly once.
        // approve() is a state no-op here (the payment claim is already paid
        // and the order already in progress) and only drives that email.
        if ($this->record->payment_status === Enrollment::PAYMENT_STATUS_PAID) {
            app(PaymentReviewService::class)->approve($this->record);
        }
    }
}
