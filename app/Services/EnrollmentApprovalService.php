<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Legacy enrollment lifecycle home for the two transitions that are not
 * payment review: order-received notification and manual course completion.
 *
 * Payment review (approve/reject/requestCorrection/resubmit) is owned by
 * PaymentReviewService and updates this enrollment's columns by mirror.
 */
class EnrollmentApprovalService
{
    public const TEMPLATE_ORDER_RECEIVED = 'order_received_payment_pending';

    /**
     * Manual completion is only allowed once the payment is fully paid, so
     * "completed" can never unlock an unpaid enrollment.
     *
     * Course completion stays fulfillment-domain state on the enrollment
     * (LessonController reads it for access), while the canonical Order
     * lifecycle is advanced to "completed" in the same transaction so the two
     * never disagree.
     */
    public function complete(Enrollment $enrollment): bool
    {
        return DB::transaction(function () use ($enrollment): bool {
            $changed = DB::table('enrollments')
                ->where('id', $enrollment->id)
                ->where('payment_status', Enrollment::PAYMENT_STATUS_PAID)
                ->where('enrollment_status', 'in_progress')
                ->update(['enrollment_status' => 'completed']) === 1;

            if ($changed) {
                Order::where('legacy_source', Order::SOURCE_ENROLLMENT)
                    ->where('legacy_id', $enrollment->getKey())
                    ->update(['order_status' => Order::STATUS_COMPLETED]);
            }

            return $changed;
        });
    }

    /**
     * Queue the "order received — payment pending" email right after the
     * public enrollment is committed. Best-effort and marker-guarded.
     */
    public function notifyOrderReceived(Enrollment $enrollment): bool
    {
        $sent = $this->sendMarked(
            $enrollment->id,
            'order_received_email_sent_at',
            self::TEMPLATE_ORDER_RECEIVED,
            $this->recipientEmail($enrollment),
            $this->recipientName($enrollment),
            [
                'student_name' => $this->recipientName($enrollment) ?? 'there',
                'order_number' => (string) $enrollment->id,
                'product_title' => $enrollment->course?->title ?? 'your course',
                'amount' => number_format((float) $enrollment->amount, 2),
                'order_url' => route('dashboard.index'),
            ],
        );

        $this->mirrorOrderReceivedMarker($enrollment->id);

        return $sent;
    }

    /**
     * Queue the single course-enrollment confirmation email for a FREE course.
     *
     * A free enrollment is active immediately, so no "payment pending" email,
     * reminder or verification flow applies. The confirmation uses the same
     * template as a paid approval but is guarded by the canonical Order's
     * outcome marker (mirrored to the legacy confirmation marker on success),
     * so a free student can never receive it twice.
     */
    public function notifyFreeEnrollmentConfirmation(Enrollment $enrollment): bool
    {
        $order = Order::where('legacy_source', Order::SOURCE_ENROLLMENT)
            ->where('legacy_id', $enrollment->getKey())
            ->first();

        if ($order === null) {
            return false;
        }

        $to = $this->recipientEmail($enrollment);
        $name = $this->recipientName($enrollment);

        if (! filled($to)) {
            Log::warning('Free enrollment confirmation skipped: no recipient.', [
                'enrollment_id' => $enrollment->id,
            ]);

            return false;
        }

        $enrollment->loadMissing('course');

        $claimed = DB::table('orders')
            ->where('id', $order->id)
            ->whereNull('outcome_email_sent_at')
            ->update(['outcome_email_sent_at' => now()]) === 1;

        if (! $claimed) {
            return true; // Already confirmed — never send twice.
        }

        try {
            $result = app(EmailService::class)->sendTemplate(
                'course_enrollment_confirmation',
                $to,
                [
                    'student_name' => $name ?? 'there',
                    'course_title' => $enrollment->course?->title ?? 'your course',
                ],
                $name,
            );

            if ($result['success'] ?? false) {
                DB::table('enrollments')
                    ->where('id', $enrollment->id)
                    ->update(['confirmation_email_sent_at' => now()]);

                return true;
            }

            Log::warning('Free enrollment confirmation not queued.', [
                'enrollment_id' => $enrollment->id,
                'template_key' => 'course_enrollment_confirmation',
                'recipient_email' => $to,
                'error' => $result['error'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Free enrollment confirmation failed — marker released for retry.', [
                'enrollment_id' => $enrollment->id,
                'recipient_email' => $to,
                'error' => $exception->getMessage(),
            ]);
        }

        DB::table('orders')
            ->where('id', $order->id)
            ->update(['outcome_email_sent_at' => null]);

        return false;
    }

    /**
     * The canonical Order marker mirrors the legacy marker after a successful
     * (or previously completed) send, so parity never sees a legacy-only
     * order_received marker on records created from this point forward.
     */
    protected function mirrorOrderReceivedMarker(int $enrollmentId): void
    {
        $marker = DB::table('enrollments')
            ->where('id', $enrollmentId)
            ->value('order_received_email_sent_at');

        if ($marker === null) {
            return;
        }

        Order::where('legacy_source', Order::SOURCE_ENROLLMENT)
            ->where('legacy_id', $enrollmentId)
            ->update(['order_received_email_sent_at' => $marker]);
    }

    /**
     * Claim the marker, attempt the queued send, and release the marker again
     * if the queue attempt fails — a marker is only a durable "email done"
     * once EmailService has accepted the job.
     *
     * @param  array<string, string>  $variables
     */
    protected function sendMarked(int $enrollmentId, string $marker, string $templateKey, ?string $to, ?string $name, array $variables): bool
    {
        if (! filled($to)) {
            Log::warning('Enrollment email skipped: no recipient.', [
                'enrollment_id' => $enrollmentId,
                'template_key' => $templateKey,
            ]);

            return false;
        }

        $claimed = DB::table('enrollments')
            ->where('id', $enrollmentId)
            ->whereNull($marker)
            ->update([$marker => now()]) === 1;

        if (! $claimed) {
            return true; // Another request is already sending this email.
        }

        try {
            $result = app(EmailService::class)->sendTemplate($templateKey, $to, $variables, $name);

            if ($result['success'] ?? false) {
                return true;
            }

            Log::warning('Enrollment email not queued.', [
                'enrollment_id' => $enrollmentId,
                'template_key' => $templateKey,
                'recipient_email' => $to,
                'error' => $result['error'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Enrollment email failed — marker released for retry.', [
                'enrollment_id' => $enrollmentId,
                'template_key' => $templateKey,
                'recipient_email' => $to,
                'error' => $exception->getMessage(),
            ]);
        }

        DB::table('enrollments')->where('id', $enrollmentId)->update([$marker => null]);

        return false;
    }

    protected function recipientEmail(Enrollment $enrollment): ?string
    {
        $enrollment->loadMissing('user');

        return filled($enrollment->student_email)
            ? $enrollment->student_email
            : ($enrollment->user?->email ?? null);
    }

    protected function recipientName(Enrollment $enrollment): ?string
    {
        $enrollment->loadMissing('user');

        return filled($enrollment->student_name)
            ? $enrollment->student_name
            : ($enrollment->user?->name ?? null);
    }
}
