@extends('layouts.admin')

@section('page-title', 'Modules: ' . $course->title)

@section('content')
    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.courses.index') }}" class="hover:text-emerald-700">Courses</a>
        <span>/</span>
        <span class="font-semibold text-slate-700">{{ $course->title }}</span>
        <span>/</span>
        <span class="text-slate-800">Modules</span>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-800">Modules for "{{ $course->title }}"</h2>
                <a href="{{ route('admin.courses.index') }}"
                   class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    ← Back to Courses
                </a>
            </div>
        </div>

        {{-- Inline Add Module Form --}}
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <form method="POST" action="{{ route('admin.courses.modules.store', $course) }}" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label for="title" class="block text-xs font-semibold text-slate-600">New Module Title</label>
                    <input type="text" name="title" id="title" required maxlength="255"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                           placeholder="e.g., Introduction to Hanyu Pinyin">
                </div>
                <button type="submit"
                        class="shrink-0 rounded-lg bg-[#0F5132] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0d452c]">
                    + Add Module
                </button>
            </form>
            @error('title') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Module List --}}
        <div class="divide-y divide-slate-100">
            @forelse($modules as $module)
                <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/50">
                    {{-- Order --}}
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">
                        {{ $module->order }}
                    </span>

                    {{-- Title & Lessons count --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $module->title }}</p>
                        <p class="text-xs text-slate-500">{{ $module->lessons_count }} lesson(s)</p>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex items-center gap-1">
                        {{-- Manage Lessons --}}
                        <a href="{{ route('admin.courses.modules.lessons.index', [$course, $module]) }}"
                           title="Manage Lessons"
                           class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-blue-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </a>

                        {{-- Edit (inline form toggle via modal or simple inline) --}}
                        <button type="button" onclick="toggleEdit({{ $module->id }})"
                           title="Edit"
                           class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-indigo-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>

                        {{-- Move Up --}}
                        @if(!$loop->first)
                            <form method="POST" action="{{ route('admin.courses.modules.up', [$course, $module]) }}" class="inline">
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
                            <form method="POST" action="{{ route('admin.courses.modules.down', [$course, $module]) }}" class="inline">
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
                        <form method="POST" action="{{ route('admin.courses.modules.destroy', [$course, $module]) }}"
                              onsubmit="return confirm('Delete this module? All lessons and progress within it will be permanently removed.')"
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

                {{-- Inline Edit Form (hidden by default) --}}
                <div id="edit-form-{{ $module->id }}" class="hidden border-t border-dashed border-slate-200 bg-amber-50/50 px-6 py-4">
                    <form method="POST" action="{{ route('admin.courses.modules.update', [$course, $module]) }}" class="flex items-end gap-3">
                        @csrf
                        @method('PUT')
                        <div class="flex-1">
                            <label for="edit-title-{{ $module->id }}" class="block text-xs font-semibold text-slate-600">Edit Module Title</label>
                            <input type="text" name="title" id="edit-title-{{ $module->id }}" value="{{ $module->title }}" required maxlength="255"
                                   class="mt-1 block w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <button type="submit"
                                class="shrink-0 rounded-lg bg-[#0F5132] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0d452c]">
                            Save
                        </button>
                        <button type="button" onclick="toggleEdit({{ $module->id }})"
                                class="shrink-0 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </button>
                    </form>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-slate-500">
                    <p class="text-lg font-semibold">No modules yet</p>
                    <p class="mt-1 text-sm">Add your first module using the form above.</p>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleEdit(id) {
            const form = document.getElementById('edit-form-' + id);
            if (form) {
                form.classList.toggle('hidden');
            }
        }
    </script>
    @endpush
@endsection
