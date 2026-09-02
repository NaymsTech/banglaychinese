<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnrollmentRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $courses = Course::with('category')
            ->where('is_published', true)
            ->orderByRaw('is_featured DESC')
            ->orderBy('title')
            ->paginate(12);

        $metaTitle = 'কোর্সসমূহ | Banglay Chinese';
        $metaDescription = 'HSK ১–৪ প্রস্তুতি, চাইনিজ স্পিকিং মাস্টারি ও ক্যারিয়ার কোর্স — বাংলায় শিখুন চীনা ভাষা। Banglay Chinese-এ আজই ভর্তি হোন।';

        return view('courses.index', compact('categories', 'courses', 'metaTitle', 'metaDescription'));
    }

    public function show(string $slug)
    {
        $course = Course::with(['category', 'modules'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (! $course) {
            // Legacy link: a Study in China package shares this slug → send them to its service page.
            if (Service::where('slug', $slug)->exists()) {
                return redirect()->route('services.show', $slug);
            }

            abort(404);
        }

        $related = Course::where('is_published', true)
            ->where('id', '!=', $course->id)
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->take(3)
            ->get();

        $metaTitle = $course->title.' | Banglay Chinese';
        $metaDescription = $course->description;

        return view('courses.show', compact('course', 'related', 'metaTitle', 'metaDescription'));
    }

    public function enroll(StoreEnrollmentRequest $request, Course $course)
    {
        if (! $course->is_published) {
            abort(404);
        }

        $user = $request->user();

        $existing = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return back()->with('status', 'আপনি ইতিমধ্যে এই কোর্সে এনরোল করেছেন।');
        }

        try {
            DB::transaction(function () use ($request, $user, $course) {
                // Price is always pulled from the database — never trusted from client input.
                Enrollment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'status' => 'pending',
                    'price_paid' => $course->price,
                    'payment_method' => $request->validated('payment_method'),
                    'transaction_id' => $request->validated('transaction_id'),
                    'sender_number' => $request->validated('sender_number'),
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['enroll' => 'এনরোলমেন্ট সফল হয়নি। আবার চেষ্টা করুন।']);
        }

        return redirect()->route('checkout.confirmation')
            ->with('transaction_id', $request->validated('transaction_id'));
    }
}
