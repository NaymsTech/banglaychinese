<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\PaymentManagementController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ScholarshipManagementController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudyInChinaPageController;
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
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('scholarships')->name('scholarships.')->group(function () {
        Route::get('/', [ScholarshipManagementController::class, 'index'])->name('index');
        Route::get('/{application}', [ScholarshipManagementController::class, 'show'])->name('show');
        Route::patch('/{application}/status', [ScholarshipManagementController::class, 'updateStatus'])
            ->where('application', '[0-9]+')
            ->name('status');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentManagementController::class, 'index'])->name('index');
        Route::get('/{enrollment}', [PaymentManagementController::class, 'show'])
            ->where('enrollment', '[0-9]+')
            ->name('show');
        Route::post('/{enrollment}/approve', [PaymentManagementController::class, 'approve'])
            ->where('enrollment', '[0-9]+')
            ->name('approve');
        Route::post('/{enrollment}/reject', [PaymentManagementController::class, 'reject'])
            ->where('enrollment', '[0-9]+')
            ->name('reject');
    });

    // Course CRUD
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [AdminCourseController::class, 'index'])->name('index');
        Route::get('/create', [AdminCourseController::class, 'create'])->name('create');
        Route::post('/', [AdminCourseController::class, 'store'])->name('store');
        Route::get('/{course}/edit', [AdminCourseController::class, 'edit'])->name('edit');
        Route::put('/{course}', [AdminCourseController::class, 'update'])->name('update');
        Route::delete('/{course}', [AdminCourseController::class, 'destroy'])->name('destroy');
        Route::patch('/{course}/toggle-published', [AdminCourseController::class, 'togglePublished'])->name('toggle-published');
        Route::patch('/{course}/toggle-featured', [AdminCourseController::class, 'toggleFeatured'])->name('toggle-featured');

        // Module Management (nested under courses)
        Route::prefix('/{course}/modules')->name('modules.')->group(function () {
            Route::get('/', [ModuleController::class, 'index'])->name('index');
            Route::post('/', [ModuleController::class, 'store'])->name('store');
            Route::put('/{module}', [ModuleController::class, 'update'])->name('update');
            Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('destroy');
            Route::patch('/{module}/up', [ModuleController::class, 'moveUp'])->name('up');
            Route::patch('/{module}/down', [ModuleController::class, 'moveDown'])->name('down');

            // Lesson Management (nested under modules)
            Route::prefix('/{module}/lessons')->name('lessons.')->group(function () {
                Route::get('/', [AdminLessonController::class, 'index'])->name('index');
                Route::get('/create', [AdminLessonController::class, 'create'])->name('create');
                Route::post('/', [AdminLessonController::class, 'store'])->name('store');
                Route::get('/{lesson}/edit', [AdminLessonController::class, 'edit'])->name('edit');
                Route::put('/{lesson}', [AdminLessonController::class, 'update'])->name('update');
                Route::delete('/{lesson}', [AdminLessonController::class, 'destroy'])->name('destroy');
                Route::patch('/{lesson}/up', [AdminLessonController::class, 'moveUp'])->name('up');
                Route::patch('/{lesson}/down', [AdminLessonController::class, 'moveDown'])->name('down');
            });
        });
    });

    // Service CRUD (Study in China consulting packages)
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [AdminServiceController::class, 'index'])->name('index');
        Route::get('/create', [AdminServiceController::class, 'create'])->name('create');
        Route::post('/', [AdminServiceController::class, 'store'])->name('store');
        Route::get('/{service}/edit', [AdminServiceController::class, 'edit'])->name('edit');
        Route::put('/{service}', [AdminServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [AdminServiceController::class, 'destroy'])->name('destroy');
        Route::patch('/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{service}/move-up', [AdminServiceController::class, 'moveUp'])->name('move-up');
        Route::patch('/{service}/move-down', [AdminServiceController::class, 'moveDown'])->name('move-down');
    });

    Route::resource('posts', AdminPostController::class)->except(['show']);

    Route::post('/categories', function (Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        $category = Category::create(['name' => $request->name, 'slug' => Str::slug($request->name)]);

        return response()->json(['id' => $category->id, 'name' => $category->name]);
    })->name('categories.store');

    Route::prefix('contact-messages')->name('contact-messages.')->group(function () {
        Route::get('/', [ContactMessageController::class, 'index'])->name('index');
        Route::get('/{message}', [ContactMessageController::class, 'show'])
            ->where('message', '[0-9]+')
            ->name('show');
        Route::delete('/{message}', [ContactMessageController::class, 'destroy'])
            ->where('message', '[0-9]+')
            ->name('destroy');
    });

    // Study in China CMS (section editor; see admin/study-in-china/edit.blade.php)
    Route::prefix('study-in-china')->name('study-in-china.')->group(function () {
        Route::get('/', [StudyInChinaPageController::class, 'edit'])->name('index');
        Route::post('/update-key', [StudyInChinaPageController::class, 'updateByKey'])->name('update-by-key');
        Route::post('/update-json', [StudyInChinaPageController::class, 'updateJson'])->name('update-json');
    });

    // CRM - Lead Management
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/', [CrmController::class, 'index'])->name('index');
        Route::get('/{lead}', [CrmController::class, 'show'])
            ->where('lead', '[0-9]+')
            ->name('show');
        Route::put('/{lead}', [CrmController::class, 'update'])
            ->where('lead', '[0-9]+')
            ->name('update');
    });

    // About Page CMS (simplified - single form)
    Route::prefix('about-page')->name('about.')->group(function () {
        Route::get('/', [AboutPageController::class, 'index'])->name('index');
        Route::put('/', [AboutPageController::class, 'update'])->name('update');
    });

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
