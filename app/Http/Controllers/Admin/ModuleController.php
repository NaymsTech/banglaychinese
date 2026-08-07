<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function index(Course $course): View
    {
        $modules = $course->modules()
            ->withCount('lessons')
            ->orderBy('order')
            ->get();

        return view('admin.modules.index', [
            'course' => $course,
            'modules' => $modules,
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $nextOrder = $course->modules()->max('order') + 1;

        $course->modules()->create([
            'title' => $validated['title'],
            'order' => $nextOrder,
        ]);

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module added successfully.');
    }

    public function update(Request $request, Course $course, Module $module): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $module->update($validated);

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module updated successfully.');
    }

    public function destroy(Course $course, Module $module): RedirectResponse
    {
        // Cascade delete lessons and their progress
        $lessonIds = $module->lessons()->pluck('id');
        if ($lessonIds->isNotEmpty()) {
            \App\Models\LessonProgress::whereIn('lesson_id', $lessonIds)->delete();
            $module->lessons()->delete();
        }

        $module->delete();

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module deleted successfully.');
    }

    public function moveUp(Course $course, Module $module): RedirectResponse
    {
        $prev = $course->modules()
            ->where('order', '<', $module->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($prev) {
            $currentOrder = $module->order;
            $module->update(['order' => $prev->order]);
            $prev->update(['order' => $currentOrder]);
        }

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module reordered.');
    }

    public function moveDown(Course $course, Module $module): RedirectResponse
    {
        $next = $course->modules()
            ->where('order', '>', $module->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($next) {
            $currentOrder = $module->order;
            $module->update(['order' => $next->order]);
            $next->update(['order' => $currentOrder]);
        }

        return redirect()
            ->route('admin.courses.modules.index', $course)
            ->with('success', 'Module reordered.');
    }
}
