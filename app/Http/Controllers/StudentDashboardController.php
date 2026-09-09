<?php

namespace App\Http\Controllers;

use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Rules\BangladeshiPhone;
use App\Services\PaymentReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    /**
     * Show the student dashboard with their enrolled courses and progress.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'enrollments' => fn ($q) => $q->whereIn('enrollment_status', ['in_progress', 'completed', 'pending'])
                ->with(['course', 'unifiedOrder.payments']),
            'lessonProgress',
        ]);

        $completedLessonIds = $user->lessonProgress->pluck('lesson_id');

        $enrolledCourses = $user->enrollments->map(function ($enrollment) use ($completedLessonIds) {
            $course = $enrollment->course;

            if (! $course) {
                return null;
            }

            $totalLessons = $course->lessons()->count();
            $completedLessons = $course->lessons()
                ->whereIn('id', $completedLessonIds)
                ->count();

            $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

            return (object) [
                'course' => $course,
                'enrollment' => $enrollment,
                'enrollment_status' => $enrollment->enrollment_status,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'progress' => $progress,
                // Canonical money totals for customer visibility.
                'payment_paid' => (float) ($enrollment->unifiedOrder?->paidTotal() ?? 0),
                'payment_due' => (float) ($enrollment->unifiedOrder?->dueTotal() ?? 0),
                'payment_state' => $enrollment->unifiedOrder?->reviewStatus() ?? 'pending',
            ];
        })->filter()->values();

        // Split courses into active (unlocked) and pending (payment under review)
        $activeCourses = $enrolledCourses->whereIn('enrollment_status', ['in_progress', 'completed'])->values();
        $pendingCourses = $enrolledCourses
            ->where('enrollment_status', 'pending')
            ->values();

        $metaTitle = 'Student Dashboard | Banglay Chinese';
        $metaDescription = 'Your enrolled courses and learning progress on Banglay Chinese.';

        // Approved digital product purchases — matched by the linked account or the checkout email.
        $approvedDownloads = DigitalOrder::query()
            ->with(['product', 'unifiedOrder'])
            ->where('status', DigitalOrder::STATUS_APPROVED)
            ->where(function ($query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhere('student_email', $user->email);
            })
            ->latest()
            ->get()
            ->filter(fn (DigitalOrder $order): bool => $order->product !== null)
            ->values();

        return view('dashboard', compact(
            'user',
            'enrolledCourses',
            'activeCourses',
            'pendingCourses',
            'approvedDownloads',
            'metaTitle',
            'metaDescription'
        ));
    }

    /**
     * Show the course payment correction form for a needs-attention enrollment.
     */
    public function editEnrollmentPayment(Enrollment $enrollment): View|RedirectResponse
    {
        abort_unless($this->ownsEnrollment($enrollment), 403);

        $enrollment->loadMissing(['course', 'unifiedOrder.payments']);
        $payment = $enrollment->unifiedOrder?->payments->first();

        // Review state comes exclusively from the canonical ledger. A record
        // without canonical data fails closed: the correction form is not
        // offered and nothing is materialized during this read.
        $awaitingCorrection = $enrollment->unifiedOrder?->reviewStatus() === Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION;

        if (! $awaitingCorrection) {
            return redirect()->route('dashboard.index');
        }

        return view('dashboard.payment.enrollment', [
            'enrollment' => $enrollment,
            'metaTitle' => 'Update Payment Info | Banglay Chinese',
            // Submitted payment information for the review form, taken only
            // from the canonical Payment row. A null value simply renders an
            // empty field — legacy columns are never used as a fallback.
            'reviewMethod' => $payment?->method,
            'reviewTransactionId' => $payment?->trx_reference,
            'reviewSenderNumber' => $payment?->sender_number,
        ]);
    }

    /**
     * Store the corrected course payment information and return the enrollment
     * to the ordinary pending state for a fresh admin review.
     */
    public function resubmitEnrollment(Request $request, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($this->ownsEnrollment($enrollment), 403);

        $validated = $request->validate([
            'payment_method' => ['required', 'in:bkash,nagad,bank'],
            'transaction_id' => ['required', 'string', 'min:8', 'max:64'],
            'sender_number' => ['required', new BangladeshiPhone],
        ]);

        $resubmitted = app(PaymentReviewService::class)->resubmit($enrollment, $validated);

        if (! $resubmitted) {
            return back()->with('error', 'This enrollment is not awaiting corrected payment information.');
        }

        return redirect()
            ->route('dashboard.index')
            ->with('status', 'আপনার পেমেন্টের তথ্য আপডেট করা হয়েছে — আবার যাচাই করা হবে।');
    }

    /**
     * Show the digital order payment correction form for a needs-attention order.
     */
    public function editOrderPayment(DigitalOrder $order): View|RedirectResponse
    {
        abort_unless($this->ownsOrder($order), 403);

        $order->loadMissing(['product', 'unifiedOrder.payments']);
        $payment = $order->unifiedOrder?->payments->first();

        // Review state comes exclusively from the canonical ledger. A record
        // without canonical data fails closed: the correction form is not
        // offered and nothing is materialized during this read.
        $awaitingCorrection = $order->unifiedOrder?->payments->contains(
            fn (Payment $paymentRow): bool => $paymentRow->status === Payment::STATUS_NEEDS_ATTENTION
        );

        if (! $awaitingCorrection) {
            return redirect()->route('dashboard.index');
        }

        return view('dashboard.payment.order', [
            'order' => $order,
            'metaTitle' => 'Update Payment Info | Banglay Chinese',
            // Canonical Payment reference is the only source for the form
            // prefill; a null value renders an empty field — the legacy trx_id
            // is never used as a fallback.
            'reviewTrxId' => $payment?->trx_reference,
        ]);
    }

    /**
     * Store the corrected transaction reference and return the order to pending.
     */
    public function resubmitOrder(Request $request, DigitalOrder $order): RedirectResponse
    {
        abort_unless($this->ownsOrder($order), 403);

        $validated = $request->validate([
            'trx_id' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $resubmitted = app(PaymentReviewService::class)->resubmit($order, $validated);

        if (! $resubmitted) {
            return back()->with('error', 'This order is not awaiting corrected payment information.');
        }

        return redirect()
            ->route('dashboard.index')
            ->with('status', 'আপনার পেমেন্টের তথ্য আপডেট করা হয়েছে — আবার যাচাই করা হবে।');
    }

    protected function ownsEnrollment(Enrollment $enrollment): bool
    {
        $user = auth()->user();

        return $enrollment->user_id === $user->id
            || strcasecmp((string) $enrollment->student_email, (string) $user->email) === 0;
    }

    protected function ownsOrder(DigitalOrder $order): bool
    {
        $user = auth()->user();

        return $order->user_id === $user->id
            || strcasecmp((string) $order->student_email, (string) $user->email) === 0;
    }
}
