<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnrollmentRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lead;
use App\Models\Service;
use App\Services\CheckoutOrderWriter;
use App\Services\EnrollmentApprovalService;
use App\Services\LeadCaptureService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

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

        $metaTitle = filled($course->meta_title)
            ? $course->meta_title
            : $course->title.' | Banglay Chinese';
        $metaDescription = filled($course->meta_description)
            ? $course->meta_description
            : str()->limit(strip_tags((string) $course->description), 160);
        $metaImage = filled($course->og_image)
            ? asset('storage/'.$course->og_image)
            : (filled($course->thumbnail) ? asset('storage/'.$course->thumbnail) : null);

        $courseSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $course->title,
            'description' => trim(strip_tags((string) $course->description)),
            'url' => route('courses.show', ['slug' => $course->slug]),
            'provider' => [
                '@type' => 'EducationalOrganization',
                'name' => SettingsService::get('site_name', 'Banglay Chinese'),
                'url' => url('/'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) round((float) $course->price, 2),
                'priceCurrency' => 'BDT',
            ],
        ];

        if (filled($course->thumbnail)) {
            $courseSchema['image'] = asset('storage/'.$course->thumbnail);
        }

        $courseJsonLd = json_encode($courseSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);

        return view('courses.show', compact('course', 'related', 'metaTitle', 'metaDescription', 'metaImage', 'courseJsonLd'));
    }

    public function enroll(StoreEnrollmentRequest $request, Course $course, LeadCaptureService $leads)
    {
        abort_unless($course->is_published, 404);

        $user = $request->user();

        // Free enrollment is a legitimate zero-price sale. It must never be
        // usable as a way to register a paid course without paying: the
        // authoritative course price is checked server-side here, so a
        // hand-crafted `payment_method=free` submission against a paid course
        // is rejected before any record is created.
        $paymentMethod = $request->validated('payment_method');
        $isFree = $paymentMethod === 'free';

        if ($isFree && (float) $course->price > 0) {
            return back()->withErrors([
                'payment_method' => 'এই কোর্সটি ফ্রি নয় — ফ্রি এনরোলমেন্ট শুধুমাত্র ফ্রি কোর্সের জন্য প্রযোজ্য।',
            ]);
        }

        $existing = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return back()->with('status', 'আপনি ইতিমধ্যে এই কোর্সে এনরোল করেছেন।');
        }

        $enrollment = null;

        try {
            DB::transaction(function () use ($request, $user, $course, $isFree, &$enrollment) {
                $snapshot = [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'student_name' => $user->name,
                    'student_email' => $user->email,
                    'student_phone' => $user->phone,
                ];

                if ($isFree) {
                    // Price is always pulled from the database — never trusted
                    // from client input. A free course is immediately active:
                    // zero price, nothing paid, nothing due, no payment review.
                    $enrollment = Enrollment::create($snapshot + [
                        'amount' => 0,
                        'amount_paid' => 0,
                        'amount_due' => 0,
                        'payment_status' => Enrollment::PAYMENT_STATUS_PAID,
                        'enrollment_status' => 'in_progress',
                        'status' => 'active',
                        'price_paid' => 0,
                        'payment_method' => 'free',
                        'transaction_id' => null,
                        'sender_number' => null,
                        'paid_at' => null,
                    ]);

                    app(CheckoutOrderWriter::class)->recordFreeEnrollmentSale($enrollment);

                    return;
                }

                $enrollment = Enrollment::create($snapshot + [
                    'amount' => $course->price,
                    'amount_paid' => 0,
                    'amount_due' => $course->price,
                    'payment_status' => 'pending',
                    'enrollment_status' => 'pending',
                    'status' => 'pending',
                    'price_paid' => $course->price,
                    'payment_method' => $request->validated('payment_method'),
                    'transaction_id' => $request->validated('transaction_id'),
                    'sender_number' => $request->validated('sender_number'),
                ]);

                // Shadow-write the unified sale in the same transaction: a
                // failure rolls the enrollment back with it.
                app(CheckoutOrderWriter::class)->recordEnrollmentSale($enrollment);
            });
        } catch (Throwable $exception) {
            Log::warning('Course enrollment failed.', [
                'course_id' => $course->getKey(),
                'user_id' => $user->getKey(),
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors(['enroll' => 'এনরোলমেন্ট সফল হয়নি। আবার চেষ্টা করুন।']);
        }

        $leads->capture([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'whatsapp_number' => $user->phone,
            'source' => Lead::SOURCE_CHECKOUT,
            'interest' => Lead::INTEREST_COURSES,
            'notes' => 'Course: '.$course->title,
        ]);

        if ($isFree) {
            // A free course is active immediately: no "payment pending" email,
            // no reminder, no verification. The single confirmation email is
            // marker-guarded so it can never be sent twice.
            app(EnrollmentApprovalService::class)->notifyFreeEnrollmentConfirmation($enrollment);

            return back()->with('status', 'আপনি সফলভাবে কোর্সে ভর্তি হয়েছেন — শুভকামনা!');
        }

        // Queued only after the enrollment transaction has committed; a failed
        // queue attempt is logged by the service and never breaks enrollment.
        app(EnrollmentApprovalService::class)->notifyOrderReceived($enrollment);

        return redirect()->route('checkout.confirmation')
            ->with('transaction_id', $request->validated('transaction_id'));
    }
}
