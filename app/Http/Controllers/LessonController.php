<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * Show the lesson player with video, content, and progress tracking.
     */
    public function show(Request $request, Course $course, Lesson $lesson): View|RedirectResponse
    {
        $user = $request->user();

        // Ensure the lesson belongs to this course (prevents cross-course lesson access)
        abort_unless($course->lessons()->whereKey($lesson->getKey())->exists(), 404);

        // Free preview lessons are accessible to all authenticated users regardless of payment status
        if ($lesson->is_free_preview) {
            return $this->renderLesson($request, $course, $lesson);
        }

        // Non-free lessons require an active enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->latest()
            ->first();

        if (! $enrollment) {
            abort(403, 'You must have an active enrollment to access this lesson.');
        }

        if ($enrollment->enrollment_status === 'pending') {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'আপনার পেমেন্ট বর্তমানে যাচাইকরণের অধীনে রয়েছে।');
        }

        if (! in_array($enrollment->enrollment_status, ['in_progress', 'completed'], true)) {
            abort(403, 'Your enrollment is not active. Please contact support.');
        }

        return $this->renderLesson($request, $course, $lesson);
    }

    /**
     * Toggle lesson completion progress for the current user.
     *
     * Ensures the authenticated user has an active enrollment in the course
     * that owns the lesson before allowing any progress mutation (IDOR-safe).
     */
    public function toggleComplete(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();

        $course = $lesson->module?->course;

        abort_unless($course instanceof Course, 404);

        $hasActiveEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('enrollment_status', ['in_progress', 'completed'])
            ->exists();

        abort_unless($hasActiveEnrollment, 403, 'You must have an active enrollment to update lesson progress.');

        $existing = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', 'Lesson marked as incomplete.');
        }

        LessonProgress::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        return back()->with('status', '🎉 Lesson completed!');
    }

    /**
     * Render the lesson player view with shared data.
     */
    protected function renderLesson(Request $request, Course $course, Lesson $lesson): View
    {
        $user = $request->user();

        $completed = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        $completedLessonIds = $user->lessonProgress()->pluck('lesson_id');

        // Current enrollment status for the course (used for pending lock indicators)
        $enrollmentStatus = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->latest()
            ->value('enrollment_status');

        $course = $course->load(['modules.lessons']);

        // Build the ordered lesson list across all modules
        $allLessons = $course->modules->flatMap(fn ($module) => $module->lessons);

        $currentIndex = $allLessons->search(fn ($item) => $item->getKey() === $lesson->getKey());
        $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        $metaTitle = $lesson->title.' | '.$course->title.' | Banglay Chinese';
        $metaDescription = 'Lesson: '.$lesson->title.' from '.$course->title.'.';

        return view('dashboard.lessons.show', compact(
            'course',
            'lesson',
            'completed',
            'completedLessonIds',
            'enrollmentStatus',
            'allLessons',
            'prevLesson',
            'nextLesson',
            'metaTitle',
            'metaDescription'
        ));
    }
}
