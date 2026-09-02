<?php

namespace App\Http\Controllers;

use App\Models\ScholarshipApplication;
use App\Models\StudyInChinaSection;
use Illuminate\Http\Request;

class StudyInChinaController extends Controller
{
    /**
     * Display the Study in China landing page.
     */
    public function index()
    {
        $sections = StudyInChinaSection::orderBy('sort_order')->get()
            ->groupBy('group')
            ->map(function ($group) {
                $data = [];
                foreach ($group as $section) {
                    $key = str_replace($section->group . '_', '', $section->key);
                    $data[$key] = $section->parsed_value;
                }
                return $data;
            });

        return view('study-in-china.index', compact('sections'));
    }

    /**
     * Handle consultation booking submission.
     */
    public function submitConsultation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'highest_qualification' => 'required|string|max:255',
            'desired_program' => 'required|string|max:255',
            'target_intake' => 'required|string|max:255',
            'interested_service_id' => 'nullable|integer|exists:services,id',
            'budget' => 'nullable|string|max:255',
            'preferred_consultation_time' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ], [
            'name.required' => 'Please enter your full name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Please enter your phone number.',
            'highest_qualification.required' => 'Please enter your current qualification.',
            'desired_program.required' => 'Please enter your desired degree.',
            'target_intake.required' => 'Please select your preferred intake.',
        ]);

        $application = ScholarshipApplication::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'highest_qualification' => $validated['highest_qualification'],
            // These legacy columns are NOT NULL in the schema; populate them from
            // the consultation form fields so the lead is stored correctly.
            'educational_background' => $validated['highest_qualification'],
            'desired_program' => $validated['desired_program'],
            'target_course' => $validated['desired_program'],
            'statement_of_purpose' => $validated['message'] ?? '',
            'target_intake' => $validated['target_intake'],
            'interested_service_id' => $validated['interested_service_id'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'preferred_consultation_time' => $validated['preferred_consultation_time'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'new',
        ]);

        return redirect()->to(route('study-in-china') . '#booking')
            ->with('success', 'আপনার তথ্য আমরা পেয়েছি। আমাদের টিম আপনার প্রোফাইল review করে পরবর্তী ধাপ সম্পর্কে যোগাযোগ করবে।');
    }
}
