<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScholarshipApplication;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    /**
     * Display the CRM lead list.
     */
    public function index(Request $request)
    {
        $query = ScholarshipApplication::with('interestedService');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $leads = $query->paginate(20)->withQueryString();

        $statusCounts = ScholarshipApplication::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusLabels = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'consultation_scheduled' => 'Consultation Scheduled',
            'application_started' => 'Application Started',
            'converted' => 'Converted',
            'closed' => 'Closed',
        ];

        return view('admin.crm.index', compact(
            'leads',
            'statusCounts',
            'statusLabels'
        ));
    }

    /**
     * Show lead details.
     */
    public function show(ScholarshipApplication $lead)
    {
        $lead->load('interestedService');

        $services = \App\Models\Service::active()->ordered()->get();

        $statusLabels = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'consultation_scheduled' => 'Consultation Scheduled',
            'application_started' => 'Application Started',
            'converted' => 'Converted',
            'closed' => 'Closed',
        ];

        return view('admin.crm.show', compact('lead', 'statusLabels', 'services'));
    }

    /**
     * Update lead status and notes.
     */
    public function update(Request $request, ScholarshipApplication $lead)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:new,contacted,consultation_scheduled,application_started,converted,closed',
            'admin_notes' => 'nullable|string|max:5000',
            'follow_up_date' => 'nullable|date',
            'interested_service_id' => 'nullable|integer|exists:services,id',
        ]);

        $lead->update($validated);

        return redirect()->route('admin.crm.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }
}
