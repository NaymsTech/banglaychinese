<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status', 'all');

        $courses = Course::query()
            ->withCount('enrollments')
            ->withCount('lessons')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->when($status === 'published', function ($q) {
                $q->where('is_published', true);
            })
            ->when($status === 'draft', function ($q) {
                $q->where('is_published', false);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.courses.index', [
            'courses' => $courses,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.courses.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $data = $request->validatedData();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully.');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', [
            'course' => $course,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validatedData();

        // Handle thumbnail replacement
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        // Handle remove thumbnail checkbox
        if ($request->boolean('remove_thumbnail') && $course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
            $data['thumbnail'] = null;
        }

        $course->update($data);

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        // Delete thumbnail file
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        // Cascade delete: lessons progress -> lessons -> modules -> enrollments
        $moduleIds = $course->modules()->pluck('id');
        if ($moduleIds->isNotEmpty()) {
            \App\Models\LessonProgress::whereIn('lesson_id', function ($q) use ($moduleIds) {
                $q->select('id')->from('lessons')->whereIn('module_id', $moduleIds);
            })->delete();

            \App\Models\Lesson::whereIn('module_id', $moduleIds)->delete();
            $course->modules()->delete();
        }

        $course->enrollments()->delete();
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }

    public function togglePublished(Course $course): RedirectResponse
    {
        $course->update(['is_published' => ! $course->is_published]);

        $status = $course->is_published ? 'published' : 'unpublished';

        return redirect()->route('admin.courses.index')->with('success', "Course {$status} successfully.");
    }

    public function toggleFeatured(Course $course): RedirectResponse
    {
        $course->update(['is_featured' => ! $course->is_featured]);

        $status = $course->is_featured ? 'featured' : 'unfeatured';

        return redirect()->route('admin.courses.index')->with('success', "Course {$status} successfully.");
    }
}
