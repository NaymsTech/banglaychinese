<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\LeadCaptureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request, LeadCaptureService $leads): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $leads->capture([
            'email' => $validated['email'],
            'source' => 'newsletter',
            'interest' => Lead::INTEREST_GENERAL,
        ]);

        return back()->with('newsletter', "You're on the list! We'll email you when new intakes open.");
    }
}
