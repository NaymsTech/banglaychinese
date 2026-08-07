<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ScholarshipApplication;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function index()
    {
        $services = Course::where('is_published', true)
            ->where('type', 'service')
            ->orderBy('price')
            ->get();

        return view('study-in-china.index', compact('services'));
    }

    public function services()
    {
        $services = Course::where('is_published', true)
            ->where('type', 'service')
            ->orderBy('price')
            ->get();

        return view('study-in-china.services', compact('services'));
    }

    public function consultation()
    {
        return view('study-in-china.consultation');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'email'                  => ['required', 'email', 'max:255'],
            'phone'                  => ['required', 'string', 'max:30'],
            'highest_qualification'  => ['nullable', 'string', 'max:255'],
            'gpa_cgpa'               => ['nullable', 'string', 'max:50'],
            'gpa'                    => ['nullable', 'string', 'max:50'],
            'desired_program'        => ['nullable', 'string', 'max:255'],
            'target_intake'          => ['nullable', 'string', 'max:255'],
            'preferred_intake'       => ['nullable', 'string', 'max:255'],
            'hsk_english_level'      => ['nullable', 'string', 'max:255'],
            'target_course'          => ['nullable', 'string', 'max:255'],
            'educational_background' => ['nullable', 'string', 'max:255'],
            'statement_of_purpose'   => ['nullable', 'string', 'max:5000'],
            'message'                => ['nullable', 'string', 'max:5000'],
        ]);

        // Normalize fields: map alternate keys to their canonical column names
        $data = $validated;

        // Map the admission form multi-step fields
        if (empty($data['highest_qualification']) && !empty($data['educational_background'])) {
            $data['highest_qualification'] = $data['educational_background'];
        }
        if (empty($data['gpa_cgpa']) && !empty($data['gpa'])) {
            $data['gpa_cgpa'] = $data['gpa'];
        }
        if (empty($data['desired_program']) && !empty($data['target_course'])) {
            $data['desired_program'] = $data['target_course'];
        }
        if (empty($data['target_intake']) && !empty($data['preferred_intake'])) {
            $data['target_intake'] = $data['preferred_intake'];
        }

        ScholarshipApplication::create($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'আপনার আবেদন সফলভাবে গৃহীত হয়েছে!'], 201);
        }

        return redirect()->route('study-in-china.consultation')->with('success', 'আপনার আবেদনটি সফলভাবে গৃহীত হয়েছে। আমাদের টিম শীঘ্রই আপনার সাথে যোগাযোগ করবে।');
    }
}
