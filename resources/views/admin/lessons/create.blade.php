@extends('layouts.admin')

@section('page-title', 'Add Lesson - ' . $module->title)

@section('content')
    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.courses.index') }}" class="hover:text-emerald-700">Courses</a>
        <span>/</span>
        <a href="{{ route('admin.courses.modules.index', $course) }}" class="hover:text-emerald-700">{{ $course->title }}</a>
        <span>/</span>
        <a href="{{ route('admin.courses.modules.lessons.index', [$course, $module]) }}" class="hover:text-emerald-700">{{ $module->title }}</a>
        <span>/</span>
        <span class="text-slate-800">Add Lesson</span>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-800">Add Lesson to "{{ $module->title }}"</h2>
            <p class="text-xs text-slate-500 mt-0.5">Course: {{ $course->title }}</p>
        </div>

        <div class="px-6 py-6">
            <form method="POST" action="{{ route('admin.courses.modules.lessons.store', [$course, $module]) }}">
                @csrf

                {{-- Title --}}
                <div class="mb-4">
                    <label for="title" class="block text-sm font-semibold text-slate-700">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                           placeholder="e.g., Introduction to Tones">
                    @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Slug --}}
                <div class="mb-4">
                    <label for="slug" class="block text-sm font-semibold text-slate-700">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" maxlength="255"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                           placeholder="Auto-generated from title if empty">
                    <p class="mt-1 text-xs text-slate-400">Leave empty to auto-generate from the title.</p>
                    @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Video URL --}}
                <div class="mb-4">
                    <label for="video_url" class="block text-sm font-semibold text-slate-700">Video URL</label>
                    <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}" maxlength="255"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                           placeholder="https://www.youtube.com/watch?v=...">
                    @error('video_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Content --}}
                <div class="mb-4">
                    <label for="content" class="block text-sm font-semibold text-slate-700">Content</label>
                    <textarea name="content" id="content" rows="8"
                              class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                              placeholder="Write lesson content here...">{{ old('content') }}</textarea>
                    @error('content') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Free Preview --}}
                <div class="mb-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_free_preview" value="1" @checked(old('is_free_preview'))
                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700">Free Preview</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400 ml-6">Allow unenrolled users to preview this lesson.</p>
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="rounded-lg bg-[#0F5132] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0d452c]">
                        Create Lesson
                    </button>
                    <a href="{{ route('admin.courses.modules.lessons.index', [$course, $module]) }}"
                       class="rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
