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
            ->when(in_array($status, ['pending', 'approved', 'rejected']), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        return view('admin.scholarships.index', [
            'applications' => $applications,
            'currentStatus' => $status,
            'counts' => [
                'pending' => ScholarshipApplication::where('status', 'pending')->count(),
                'approved' => ScholarshipApplication::where('status', 'approved')->count(),
                'rejected' => ScholarshipApplication::where('status', 'rejected')->count(),
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

        $application->update($validated);

        return back()->with('success', "Application status updated to '{$application->status}'.");
    }
}
