<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    /**
     * Show the student dashboard with their enrolled courses and progress.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'enrollments' => fn ($q) => $q->whereIn('status', ['active', 'pending', 'paid'])->with('course'),
            'lessonProgress',
        ]);

        $completedLessonIds = $user->lessonProgress->pluck('lesson_id');

        $enrolledCourses = $user->enrollments->map(function ($enrollment) use ($completedLessonIds) {
            $course = $enrollment->course;

            if (! $course) {
                return null;
            }

            $totalLessons = $course->lessons()->count();
            $completedLessons = $course->lessons()
                ->whereIn('id', $completedLessonIds)
                ->count();

            $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

            return (object) [
                'course' => $course,
                'enrollment' => $enrollment,
                'enrollment_status' => $enrollment->status,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'progress' => $progress,
            ];
        })->filter()->values();

        // Split courses into active (unlocked) and pending (payment under review)
        $activeCourses = $enrolledCourses->where('enrollment_status', 'active')->values();
        $pendingCourses = $enrolledCourses
            ->whereIn('enrollment_status', ['pending', 'paid'])
            ->values();

        $metaTitle = 'Student Dashboard | Banglay Chinese';
        $metaDescription = 'Your enrolled courses and learning progress on Banglay Chinese.';

        return view('dashboard', compact(
            'user',
            'enrolledCourses',
            'activeCourses',
            'pendingCourses',
            'metaTitle',
            'metaDescription'
        ));
    }
}
