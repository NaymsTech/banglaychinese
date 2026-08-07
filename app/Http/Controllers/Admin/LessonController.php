<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Course $course, Module $module): View
    {
        $lessons = $module->lessons()
            ->orderBy('order')
            ->get();

        return view('admin.lessons.index', [
            'course' => $course,
            'module' => $module,
            'lessons' => $lessons,
        ]);
    }

    public function create(Course $course, Module $module): View
    {
        return view('admin.lessons.create', [
            'course' => $course,
            'module' => $module,
        ]);
    }

    public function store(Request $request, Course $course, Module $module): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_free_preview' => ['sometimes', 'boolean'],
        ]);

        $slug = $validated['slug'] ?: Str::slug($validated['title']);

        // Ensure slug uniqueness within the course (through module)
        $baseSlug = $slug;
        $counter = 1;
        while (Lesson::where('slug', $slug)
            ->whereHas('module', fn ($q) => $q->where('course_id', $course->id))
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $nextOrder = $module->lessons()->max('order') + 1;

        $module->lessons()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'video_url' => $validated['video_url'] ?? null,
            'content' => isset($validated['content']) ? strip_tags($validated['content'], '<p><a><strong><em><ul><ol><li><h2><h3><h4><img><br><table><thead><tbody><tr><td><th>') : null,
            'order' => $nextOrder,
            'is_free_preview' => $request->boolean('is_free_preview'),
        ]);

        return redirect()
            ->route('admin.courses.modules.lessons.index', [$course, $module])
            ->with('success', 'Lesson created successfully.');
    }

    public function edit(Course $course, Module $module, Lesson $lesson): View
    {
        return view('admin.lessons.edit', [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
        ]);
    }

    public function update(Request $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_free_preview' => ['sometimes', 'boolean'],
        ]);

        $slug = $validated['slug'] ?: Str::slug($validated['title']);

        // Ensure slug uniqueness within the course, excluding current lesson
        $baseSlug = $slug;
        $counter = 1;
        while (Lesson::where('slug', $slug)
            ->where('id', '!=', $lesson->id)
            ->whereHas('module', fn ($q) => $q->where('course_id', $course->id))
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $lesson->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'video_url' => $validated['video_url'] ?? null,
            'content' => isset($validated['content']) ? strip_tags($validated['content'], '<p><a><strong><em><ul><ol><li><h2><h3><h4><img><br><table><thead><tbody><tr><td><th>') : null,
            'is_free_preview' => $request->boolean('is_free_preview'),
        ]);

        return redirect()
            ->route('admin.courses.modules.lessons.index', [$course, $module])
            ->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        // Cascade delete lesson progress
        $lesson->progress()->delete();
        $lesson->delete();

        return redirect()
            ->route('admin.courses.modules.lessons.index', [$course, $module])
            ->with('success', 'Lesson deleted successfully.');
    }

    public function moveUp(Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        $prev = $module->lessons()
            ->where('order', '<', $lesson->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($prev) {
            $currentOrder = $lesson->order;
            $lesson->update(['order' => $prev->order]);
            $prev->update(['order' => $currentOrder]);
        }

        return redirect()
            ->route('admin.courses.modules.lessons.index', [$course, $module])
            ->with('success', 'Lesson reordered.');
    }

    public function moveDown(Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        $next = $module->lessons()
            ->where('order', '>', $lesson->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($next) {
            $currentOrder = $lesson->order;
            $lesson->update(['order' => $next->order]);
            $next->update(['order' => $currentOrder]);
        }

        return redirect()
            ->route('admin.courses.modules.lessons.index', [$course, $module])
            ->with('success', 'Lesson reordered.');
    }
}
