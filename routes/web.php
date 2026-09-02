<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudyInChinaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('courses.show');
Route::post('/courses/{course:slug}/enroll', [CourseController::class, 'enroll'])
    ->middleware(['auth', 'throttle:5,60'])
    ->name('courses.enroll');
Route::get('/checkout/{course:slug}', [CheckoutController::class, 'show'])
    ->middleware('auth')
    ->name('checkout.show');
Route::get('/checkout/confirmation', [CheckoutController::class, 'confirmation'])
    ->middleware('auth')
    ->name('checkout.confirmation');

Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/about/founder', fn () => redirect()->route('about'))->name('about.founder');
Route::get('/founder', fn () => redirect()->route('about'))->name('founder');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact/send', [ContactController::class, 'send'])
    ->middleware('throttle:3,1')
    ->name('contact.send');
Route::get('/study-in-china', [ScholarshipController::class, 'index'])->name('study-in-china');
Route::get('/study-in-china/services', [ServiceController::class, 'index'])->name('services.index');
// Legacy service URLs → permanent redirect to new canonical slugs.
// Old Laravel app slugs (services table before the canonical rename):
Route::redirect('/study-in-china/services/study-in-china-application-guide', '/study-in-china/services/guided-application', 301);
Route::redirect('/study-in-china/services/study-in-china-complete-support', '/study-in-china/services/full-application-service', 301);
Route::redirect('/study-in-china/services/complete-china-success', '/study-in-china/services/elite-success-program', 301);
// Old WordPress /product/ URLs (previous site) → new canonical service URLs:
Route::redirect('/product/study-in-china-application-guide', '/study-in-china/services/guided-application', 301);
Route::redirect('/product/study-in-china-complete-application', '/study-in-china/services/full-application-service', 301);
Route::redirect('/product/complete-china-success-1-year-pathway', '/study-in-china/services/elite-success-program', 301);
Route::get('/study-in-china/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/study-in-china/consultation', [ScholarshipController::class, 'consultation'])
    ->name('study-in-china.consultation');
Route::post('/study-in-china/consultation', [StudyInChinaController::class, 'submitConsultation'])
    ->middleware('throttle:3,60')
    ->name('study-in-china.consultation.store');
Route::post('/study-in-china/apply', [ScholarshipController::class, 'store'])
    ->middleware('throttle:3,60')
    ->name('study-in-china.apply');
Route::redirect('/scholarship', '/study-in-china', 301);
Route::redirect('/scholarship/apply', '/study-in-china/consultation', 301);

Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [StudentDashboardController::class, 'index'])->name('index');
    Route::get('/courses/{course:slug}/lessons/{lesson:slug}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'toggleComplete'])->name('lessons.complete');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
