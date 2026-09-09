<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\ScholarshipApplication;
use App\Models\Service;
use App\Models\Setting;
use App\Rules\BangladeshiPhone;
use App\Services\EmailService;
use App\Services\LeadCaptureService;
use App\Support\StudyInChinaContent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScholarshipController extends Controller
{
    public function index()
    {
        // The `services` table is the single source of truth for the service cards.
        $services = Service::active()->ordered()->get();

        // Editable landing-page copy: saved `settings` rows on top of code
        // defaults. List keys are stored as JSON and decoded back here.
        $defaults = StudyInChinaContent::defaults();

        $stored = Setting::whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->toArray();

        $settings = $defaults;

        foreach ($stored as $key => $value) {
            if (blank($value)) {
                continue;
            }

            $settings[$key] = is_array($defaults[$key] ?? null)
                ? (json_decode((string) $value, true) ?? [])
                : $value;
        }

        return view('study-in-china.index', compact('services', 'settings'));
    }

    public function consultation()
    {
        $services = Service::active()->ordered()->get();

        return view('study-in-china.consultation', compact('services'));
    }

    public function store(Request $request, LeadCaptureService $leads)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', new BangladeshiPhone],
            'interested_service_id' => ['nullable', 'integer', 'exists:services,id'],
            'highest_qualification' => ['required', 'string', 'max:255'],
            'desired_program' => ['required', 'string', 'max:255'],
            'target_intake' => ['required', 'string', 'max:255'],
            'hsk_english_level' => ['nullable', 'string', 'max:255'],
            'gpa_cgpa' => ['nullable', 'string', 'max:255'],
            'statement_of_purpose' => ['required', 'string', 'min:50', 'max:5000'],
        ]);

        ScholarshipApplication::create([
            ...$validated,
            'status' => 'new',
            'application_status' => null,
        ]);

        $leads->capture([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'whatsapp_number' => $validated['phone'],
            'source' => Lead::SOURCE_APPLICATION,
            'interest' => Lead::INTEREST_STUDY_IN_CHINA,
            'notes' => 'Desired program: '.$validated['desired_program'],
        ]);

        // Acknowledge the applicant once the application and lead are safely
        // stored. A missing template must never break the submission itself.
        try {
            app(EmailService::class)->sendTemplate(
                'application_received',
                $validated['email'],
                [
                    'student_name' => $validated['name'],
                    'desired_program' => $validated['desired_program'],
                ],
                $validated['name'],
            );
        } catch (ModelNotFoundException) {
            Log::warning('Application acknowledgement not queued: application_received template is missing or inactive.', [
                'email' => $validated['email'],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'আপনার আবেদন সফলভাবে গৃহীত হয়েছে!'], 201);
        }

        return redirect()->route('study-in-china.consultation')->with('success', 'আপনার আবেদনটি সফলভাবে গৃহীত হয়েছে। আমাদের টিম শীঘ্রই আপনার সাথে যোগাযোগ করবে।');
    }
}
