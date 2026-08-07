<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Show the checkout page for a specific course.
     */
    public function show(Course $course)
    {
        if (! $course->is_published) {
            abort(404);
        }

        $user = auth()->user();

        $existing = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('status', 'You are already enrolled in this course.');
        }

        $bkashNumber = SettingsService::get('bkash_number');
        $nagadNumber = SettingsService::get('nagad_number');
        $whatsappNumber = SettingsService::get('whatsapp_number', '8618223249514');

        return view('checkout.show', compact(
            'course',
            'bkashNumber',
            'nagadNumber',
            'whatsappNumber'
        ));
    }

    /**
     * Show the confirmation page after successful enrollment.
     */
    public function confirmation(Request $request)
    {
        $transactionId = session('transaction_id');

        if (! $transactionId) {
            return redirect()->route('courses.index');
        }

        return view('checkout.confirmation', compact('transactionId'));
    }
}
