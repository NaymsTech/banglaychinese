<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScholarshipApplication;
use Illuminate\Http\Request;

class ScholarshipManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $applications = ScholarshipApplication::query()
            ->when($status === 'pending', function ($q) {
                $q->whereNull('application_status');
            })
            ->when($status === 'approved', function ($q) {
                $q->where('application_status', 'approved');
            })
            ->when($status === 'rejected', function ($q) {
                $q->where('application_status', 'rejected');
            })
            ->latest()
            ->paginate(15);

        return view('admin.scholarships.index', [
            'applications' => $applications,
            'currentStatus' => $status,
            'counts' => [
                'pending' => ScholarshipApplication::whereNull('application_status')->count(),
                'approved' => ScholarshipApplication::where('application_status', 'approved')->count(),
                'rejected' => ScholarshipApplication::where('application_status', 'rejected')->count(),
            ],
        ]);
    }

    public function show(ScholarshipApplication $application)
    {
        return view('admin.scholarships.show', [
            'application' => $application,
        ]);
    }

    public function updateStatus(Request $request, ScholarshipApplication $application)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        // "pending" clears the review outcome (no decision recorded yet).
        $application->update([
            'application_status' => $validated['status'] === 'pending' ? null : $validated['status'],
        ]);

        return back()->with('success', "Application status updated to '".ucfirst($application->application_status ?? 'pending')."'.");
    }
}
