<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentManagementController extends Controller
{
    /**
     * List all payment enrollments with filters, search and status tabs.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search'));

        $validStatuses = ['pending', 'active', 'cancelled'];

        $payments = Enrollment::query()
            ->with(['user', 'course'])
            ->when(in_array($status, $validStatuses, true), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                        ->orWhere('transaction_id', 'like', "%{$search}%")
                        ->orWhere('sender_number', 'like', "%{$search}%");
                });
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Enrollment::count(),
            'pending' => Enrollment::where('status', 'pending')->count(),
            'active' => Enrollment::where('status', 'active')->count(),
            'cancelled' => Enrollment::where('status', 'cancelled')->count(),
        ];

        return view('admin.payments.index', [
            'payments' => $payments,
            'currentStatus' => $status,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    /**
     * Show full payment details.
     */
    public function show(Enrollment $enrollment): View
    {
        $enrollment->load(['user', 'course']);

        return view('admin.payments.show', [
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Approve a pending payment — unlocks the course instantly.
     */
    public function approve(Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->status === 'pending', 422, 'Only pending payments can be approved.');

        $enrollment->update([
            'status' => 'active',
            'paid_at' => now(),
        ]);

        return back()->with('success', "Payment from {$enrollment->user?->name} approved. Course access has been unlocked.");
    }

    /**
     * Reject/cancel a payment.
     */
    public function reject(Enrollment $enrollment): RedirectResponse
    {
        abort_unless(in_array($enrollment->status, ['pending', 'active'], true), 422, 'This payment cannot be cancelled.');

        $enrollment->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', "Payment from {$enrollment->user?->name} has been rejected/cancelled.");
    }
}
