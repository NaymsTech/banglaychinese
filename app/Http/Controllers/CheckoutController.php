<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\UnifiedCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * The old course checkout URL now points to the unified checkout page.
     */
    public function show(Course $course): RedirectResponse
    {
        abort_unless($course->is_published, 404);

        return redirect()->route('checkout.unified', [
            'type' => UnifiedCheckoutService::TYPE_COURSE,
            'slug' => $course->slug,
        ]);
    }

    /**
     * Show the confirmation page after successful enrollment.
     */
    public function confirmation(Request $request): View|RedirectResponse
    {
        $transactionId = session('transaction_id');

        if (! $transactionId) {
            return redirect()->route('courses.index');
        }

        return view('checkout.confirmation', compact('transactionId'));
    }
}
