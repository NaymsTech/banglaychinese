<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Rules\BangladeshiPhone;
use App\Services\EmailService;
use App\Services\LeadCaptureService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function send(StoreContactMessageRequest $request, LeadCaptureService $leads)
    {
        $validated = $request->validated();

        ContactMessage::create($validated);

        $interest = match ($validated['topic']) {
            'Chinese Language Course' => Lead::INTEREST_COURSES,
            'Study in China Consultancy' => Lead::INTEREST_STUDY_IN_CHINA,
            'Digital Products' => Lead::INTEREST_DIGITAL_PRODUCTS,
            default => Lead::INTEREST_GENERAL,
        };

        $leads->capture([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'whatsapp_number' => $validated['phone'],
            'source' => Lead::SOURCE_CONTACT_FORM,
            'interest' => $interest,
        ]);

        // Acknowledge the enquiry once the message and lead are safely stored.
        // A missing template must never break the submission itself.
        try {
            app(EmailService::class)->sendTemplate(
                'contact_inquiry_received',
                $validated['email'],
                ['student_name' => $validated['name']],
                $validated['name'],
            );
        } catch (ModelNotFoundException) {
            Log::warning('Contact acknowledgement not queued: contact_inquiry_received template is missing or inactive.', [
                'email' => $validated['email'],
            ]);
        }

        return back()->with('success', 'Thank you! Your message has been sent. We will contact you shortly.');
    }

    /**
     * Handle the homepage "Book Free Consultation" lead form.
     * Stored as a ContactMessage row with the chosen service in `topic`.
     */
    public function submitLead(Request $request, LeadCaptureService $leads)
    {
        $services = [
            'Study in China',
            'Courses',
            'Digital Products',
            'Mentorship',
            'General Inquiry',
        ];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', new BangladeshiPhone],
            'email' => ['nullable', 'email', 'max:255'],
            'service' => ['required', 'string', Rule::in($services)],
        ]);

        ContactMessage::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'topic' => 'Homepage lead: '.$validated['service'],
            'message' => '—',
        ]);

        $interest = match ($validated['service']) {
            'Study in China' => Lead::INTEREST_STUDY_IN_CHINA,
            'Courses' => Lead::INTEREST_COURSES,
            'Digital Products' => Lead::INTEREST_DIGITAL_PRODUCTS,
            default => Lead::INTEREST_GENERAL,
        };

        $leads->capture([
            'email' => $validated['email'] ?? null,
            'name' => $validated['name'],
            'whatsapp_number' => $validated['phone'],
            'source' => Lead::SOURCE_CONTACT_FORM,
            'interest' => $interest,
            'notes' => 'Interested in: '.$validated['service'],
        ]);

        // Return to the consultation form itself so the visitor sees the
        // success banner instead of being dumped back at the top of the page.
        return redirect()
            ->route('home')
            ->with('success', 'Message sent successfully! We will contact you within 24 hours.')
            ->withFragment('contact');
    }
}
