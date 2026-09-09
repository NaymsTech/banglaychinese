<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DigitalCheckoutController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FreeResourceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\UnifiedCheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('courses.show');

// Legacy free-enrollment endpoint; the unified checkout handles paid courses.
Route::post('/courses/{course:slug}/enroll', [CourseController::class, 'enroll'])
    ->middleware(['auth', 'throttle:5,60'])
    ->name('courses.enroll');

// Old paid-course checkout page now points to the unified checkout.
Route::get('/checkout/{course:slug}', [CheckoutController::class, 'show'])->name('checkout.show');

// Public, signed, display-only confirmation for a just-completed paid course
// purchase (guests and logged-in buyers). A guest must be able to see the
// outcome without logging in; the signature keeps it unguessable.
Route::get('/checkout/confirmation/{enrollment}', [UnifiedCheckoutController::class, 'courseConfirmation'])
    ->middleware('signed')
    ->name('checkout.course.confirmation');

Route::get('/checkout/confirmation', [CheckoutController::class, 'confirmation'])
    ->middleware('auth')
    ->name('checkout.confirmation');

// Unified checkout for courses and digital products.
Route::get('/checkout/{type}/{slug}', [UnifiedCheckoutController::class, 'show'])
    ->whereIn('type', ['course', 'product'])
    ->name('checkout.unified');
Route::post('/checkout/{type}/{slug}', [UnifiedCheckoutController::class, 'store'])
    ->middleware('throttle:5,60')
    ->whereIn('type', ['course', 'product'])
    ->name('checkout.unified.store');

Route::get('/free-resources', [FreeResourceController::class, 'index'])->name('free-resources.index');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

// Old shop checkout page now points to the unified checkout. The POST route
// stays for legacy compatibility until the unified store is proven.
Route::get('/shop/checkout/{product:slug}', [DigitalCheckoutController::class, 'checkout'])->name('shop.checkout');
Route::post('/shop/checkout/{product:slug}', [DigitalCheckoutController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('shop.checkout.store');
Route::get('/shop/order-confirmed', [DigitalCheckoutController::class, 'thankYou'])->name('shop.thank-you');

Route::get('/about', [AboutController::class, 'index'])->name('about');

// Helper/legal pages (Terms, Privacy, Refund, FAQ).
Route::get('/pages/{page}', [StaticPageController::class, 'show'])
    ->whereIn('page', ['terms-and-conditions', 'privacy-policy', 'refund-and-returns-policy', 'faq'])
    ->name('pages.show');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact/send', [ContactController::class, 'send'])
    ->middleware('throttle:3,1')
    ->name('contact.send');
Route::post('/contact/lead', [ContactController::class, 'submitLead'])
    ->middleware('throttle:3,60')
    ->name('contact.lead');
Route::post('/newsletter', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:3,1')
    ->name('newsletter.subscribe');
Route::get('/study-in-china', [ScholarshipController::class, 'index'])->name('study-in-china');
Route::get('/study-in-china/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/study-in-china/consultation', [ScholarshipController::class, 'consultation'])
    ->name('study-in-china.consultation');
Route::post('/study-in-china/apply', [ScholarshipController::class, 'store'])
    ->middleware('throttle:3,60')
    ->name('study-in-china.apply');

Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [StudentDashboardController::class, 'index'])->name('index');
    Route::get('/courses/{course:slug}/lessons/{lesson:slug}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'toggleComplete'])->name('lessons.complete');

    // Payment information correction / resubmission.
    Route::get('/payments/enrollment/{enrollment}/edit', [StudentDashboardController::class, 'editEnrollmentPayment'])->name('payments.enrollment.edit');
    Route::post('/payments/enrollment/{enrollment}/resubmit', [StudentDashboardController::class, 'resubmitEnrollment'])->name('payments.enrollment.resubmit');
    Route::get('/payments/order/{order}/edit', [StudentDashboardController::class, 'editOrderPayment'])->name('payments.order.edit');
    Route::post('/payments/order/{order}/resubmit', [StudentDashboardController::class, 'resubmitOrder'])->name('payments.order.resubmit');
});

Route::get('/my-downloads/{order}/download', [DownloadController::class, 'download'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.downloads.download');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
