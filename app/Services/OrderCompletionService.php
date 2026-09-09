<?php

namespace App\Services;

use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;

/**
 * Small domain dispatcher for the unified Order detail page’s
 * “Mark Completed” action. It reuses the existing domain completion rules
 * instead of re-implementing them in Filament:
 *
 *  - Course order  → EnrollmentApprovalService::complete() (mirrors canonical)
 *  - Service order → the ServiceOrder completion rule + canonical mirror
 *  - Digital order → no separate completion concept (already completed on
 *    full payment); never unlocks anything here.
 */
class OrderCompletionService
{
    /**
     * @return array{changed: bool, message: string}
     */
    public function complete(Order $order): array
    {
        $anchor = $this->resolveAnchor($order);

        if ($anchor instanceof Enrollment) {
            return $this->completeCourse($order, $anchor);
        }

        if ($anchor instanceof ServiceOrder) {
            return $this->completeService($order, $anchor);
        }

        if ($anchor instanceof DigitalOrder) {
            if ($order->order_status === Order::STATUS_COMPLETED) {
                return ['changed' => false, 'message' => 'This order is already completed.'];
            }

            return ['changed' => false, 'message' => 'Digital orders complete automatically on approval — no manual completion is needed.'];
        }

        return ['changed' => false, 'message' => 'No fulfillment record could be resolved for this order.'];
    }

    protected function completeCourse(Order $order, Enrollment $enrollment): array
    {
        if ($order->order_status === Order::STATUS_COMPLETED) {
            return ['changed' => false, 'message' => 'This course order is already completed.'];
        }

        $changed = app(EnrollmentApprovalService::class)->complete($enrollment);

        return [
            'changed' => $changed,
            'message' => $changed
                ? 'Course marked as completed.'
                : 'Completion is only allowed once the payment is fully paid and the course is in progress.',
        ];
    }

    protected function completeService(Order $order, ServiceOrder $serviceOrder): array
    {
        $changed = DB::transaction(function () use ($order, $serviceOrder): bool {
            $updated = DB::table('service_orders')
                ->where('id', $serviceOrder->id)
                ->whereNotIn('enrollment_status', ['completed', 'cancelled'])
                ->update(['enrollment_status' => 'completed']) === 1;

            if ($updated) {
                DB::table('orders')
                    ->where('id', $order->id)
                    ->where('order_status', '!=', Order::STATUS_COMPLETED)
                    ->update(['order_status' => Order::STATUS_COMPLETED]);
            }

            return $updated;
        });

        return [
            'changed' => $changed,
            'message' => $changed ? 'Service order marked as completed.' : 'This service order is already completed or cancelled.',
        ];
    }

    protected function resolveAnchor(Order $order): Enrollment|DigitalOrder|ServiceOrder|null
    {
        return match ($order->legacy_source) {
            Order::SOURCE_ENROLLMENT => Enrollment::find($order->legacy_id),
            Order::SOURCE_DIGITAL_ORDER => DigitalOrder::find($order->legacy_id),
            Order::SOURCE_SERVICE_ORDER => ServiceOrder::find($order->legacy_id),
            default => null,
        };
    }
}
