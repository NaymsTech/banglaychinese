@extends('layouts.admin')

@section('page-title', 'Lessons: ' . $module->title)

@section('content')
    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.courses.index') }}" class="hover:text-emerald-700">Courses</a>
        <span>/</span>
        <a href="{{ route('admin.courses.modules.index', $course) }}" class="hover:text-emerald-700">{{ $course->title }}</a>
        <span>/</span>
        <span class="font-semibold text-slate-700">{{ $module->title }}</span>
        <span>/</span>
        <span class="text-slate-800">Lessons</span>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Lessons in "{{ $module->title }}"</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Course: {{ $course->title }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.courses.modules.index', $course) }}"
                       class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        ← Back to Modules
                    </a>
                    <a href="{{ route('admin.courses.modules.lessons.create', [$course, $module]) }}"
                       class="rounded-lg bg-[#0F5132] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0d452c]">
                        + Add Lesson
                    </a>
                </div>
            </div>
        </div>

        {{-- Lesson List --}}
        <div class="divide-y divide-slate-100">
            @forelse($lessons as $lesson)
                <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/50">
                    {{-- Order --}}
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                        {{ $lesson->order }}
                    </span>

                    {{-- Title, video, badge --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-800 truncate">{{ $lesson->title }}</p>
                            @if($lesson->is_free_preview)
                                <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">FREE</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 truncate">
                            @if($lesson->video_url)
                                {{ \Illuminate\Support\Str::limit($lesson->video_url, 60) }}
                            @else
                                No video URL
                            @endif
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1">
                        {{-- Edit --}}
                        <a href="{{ route('admin.courses.modules.lessons.edit', [$course, $module, $lesson]) }}"
                           title="Edit"
                           class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-indigo-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>

                        {{-- Move Up --}}
                        @if(!$loop->first)
                            <form method="POST" action="{{ route('admin.courses.modules.lessons.up', [$course, $module, $lesson]) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="Move Up"
                                        class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                </button>
                            </form>
                        @else
                            <span class="p-1.5 text-slate-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                            </span>
                        @endif

                        {{-- Move Down --}}
                        @if(!$loop->last)
                            <form method="POST" action="{{ route('admin.courses.modules.lessons.down', [$course, $module, $lesson]) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="Move Down"
                                        class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </form>
                        @else
                            <span class="p-1.5 text-slate-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        @endif

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.courses.modules.lessons.destroy', [$course, $module, $lesson]) }}"
                              onsubmit="return confirm('Delete this lesson? Student progress for this lesson will also be removed.')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Delete"
                                    class="rounded p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-slate-500">
                    <p class="text-lg font-semibold">No lessons yet</p>
                    <p class="mt-1 text-sm">Add your first lesson to this module.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
