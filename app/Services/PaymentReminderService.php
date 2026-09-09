<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sends course payment reminders through the queued EmailService.
 *
 * Eligibility is canonical: only course Orders (legacy_source =
 * 'enrollment') whose canonical review state is still "pending" or
 * "partially_paid" with a positive canonical due amount are reminded.
 * Legacy payment columns are never used to decide eligibility, and a missing
 * canonical Order is never materialized or repaired during reminder reads —
 * it simply is not eligible.
 *
 * The Enrollment stays the reminder anchor (recipient, name, course, marker)
 * and the legacy `enrollments.payment_reminder_sent_at` column remains the
 * single reminder marker: claimed atomically before dispatch, kept on
 * successful EmailService acceptance, and released on failure so the
 * 7-day cooldown is never consumed by a failed attempt.
 */
class PaymentReminderService
{
    public const TEMPLATE_KEY = 'payment_reminder';

    /**
     * Minimum days between two reminders for the same enrollment.
     */
    public const INTERVAL_DAYS = 7;

    /**
     * Remind every course order that is currently owed money and has not
     * been reminded within the cooldown interval.
     *
     * @return int number of reminders queued
     */
    public function sendDueReminders(): int
    {
        $queued = 0;

        Order::query()
            ->with('payments')
            ->where('legacy_source', Order::SOURCE_ENROLLMENT)
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$queued): void {
                $enrollments = Enrollment::whereIn('id', $orders->pluck('legacy_id'))
                    ->get()
                    ->keyBy('id');

                foreach ($orders as $order) {
                    if (! $this->isOrderEligible($order)) {
                        continue;
                    }

                    $enrollment = $enrollments->get((int) $order->legacy_id);

                    if ($enrollment === null) {
                        // Orphan canonical order (parity error): fail closed.
                        continue;
                    }

                    if ($this->claimAndSend($enrollment, $order)['queued']) {
                        $queued++;
                    }
                }
            });

        return $queued;
    }

    /**
     * Attempt to remind a single enrollment.
     *
     * @return array{claimed: bool, queued: bool, log_id?: int, error?: bool}
     */
    public function remind(Enrollment $enrollment): array
    {
        $order = $enrollment->unifiedOrder()->with('payments')->first();

        if ($order === null || ! $this->isOrderEligible($order)) {
            return ['claimed' => false, 'queued' => false];
        }

        return $this->claimAndSend($enrollment, $order);
    }

    protected function isOrderEligible(Order $order): bool
    {
        if ($order->order_status === Order::STATUS_COMPLETED || $order->order_status === Order::STATUS_CANCELLED) {
            return false;
        }

        if (! in_array($order->reviewStatus(), ['pending', 'partially_paid'], true)) {
            return false;
        }

        return $order->dueTotal() > 0;
    }

    /**
     * @return array{claimed: bool, queued: bool, log_id?: int, error?: bool}
     */
    protected function claimAndSend(Enrollment $enrollment, Order $order): array
    {
        // Atomic claim on the enrollment reminder marker: only rows that are
        // still past the cooldown can win, so racing submissions cannot both
        // send. Canonical eligibility was already verified above.
        $claimed = DB::table('enrollments')
            ->where('id', $enrollment->id)
            ->where(function ($query): void {
                $query->whereNull('payment_reminder_sent_at')
                    ->orWhere('payment_reminder_sent_at', '<=', now()->subDays(self::INTERVAL_DAYS));
            })
            ->update(['payment_reminder_sent_at' => now()]) === 1;

        if (! $claimed) {
            return ['claimed' => false, 'queued' => false];
        }

        $enrollment->refresh()->loadMissing(['course', 'user']);

        $email = filled($enrollment->student_email)
            ? $enrollment->student_email
            : ($enrollment->user?->email ?? null);

        if (! filled($email)) {
            Log::warning('Payment reminder skipped: enrollment has no recipient email.', [
                'enrollment_id' => $enrollment->id,
            ]);

            return ['claimed' => true, 'queued' => false];
        }

        $name = filled($enrollment->student_name)
            ? $enrollment->student_name
            : ($enrollment->user?->name ?? null);

        try {
            $result = app(EmailService::class)->sendTemplate(
                self::TEMPLATE_KEY,
                $email,
                [
                    'student_name' => $name ?? 'there',
                    'course_title' => $enrollment->course?->title ?? 'your course',
                    'amount_due' => (string) number_format($order->dueTotal(), 2),
                    'due_date' => $this->dueDate($order),
                ],
                $name,
            );

            if ($result['success'] ?? false) {
                return ['claimed' => true, 'queued' => true, 'log_id' => $result['log_id'] ?? null];
            }

            Log::warning('Payment reminder not queued.', [
                'enrollment_id' => $enrollment->id,
                'template_key' => self::TEMPLATE_KEY,
                'recipient_email' => $email,
                'error' => $result['error'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Payment reminder failed — cooldown released for retry.', [
                'enrollment_id' => $enrollment->id,
                'template_key' => self::TEMPLATE_KEY,
                'recipient_email' => $email,
                'error' => $exception->getMessage(),
            ]);
        }

        // A failed queue attempt must never consume the 7-day cooldown: the
        // claim is released so the next run can try again after the problem
        // (e.g. a missing template) is fixed.
        DB::table('enrollments')
            ->where('id', $enrollment->id)
            ->update(['payment_reminder_sent_at' => null]);

        return ['claimed' => false, 'queued' => false, 'error' => true];
    }

    /**
     * The payment was expected seven days after the sale; overdue sales are
     * reminded with that same original due date, computed from the canonical
     * Order timestamp.
     */
    protected function dueDate(Order $order): string
    {
        return $order->created_at
            ->addDays(self::INTERVAL_DAYS)
            ->format('j M Y');
    }
}
