@extends('layouts.admin')

@section('page-title', 'Courses')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
        {{-- Header with search and actions --}}
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.courses.index', ['status' => 'all'] + request()->only('search')) }}"
                           class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $status === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            All
                        </a>
                        <a href="{{ route('admin.courses.index', ['status' => 'published'] + request()->only('search')) }}"
                           class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $status === 'published' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Published
                        </a>
                        <a href="{{ route('admin.courses.index', ['status' => 'draft'] + request()->only('search')) }}"
                           class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $status === 'draft' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Draft
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('admin.courses.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search courses..."
                               class="w-48 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        <button type="submit"
                                class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200">
                            Search
                        </button>
                    </form>
                    <a href="{{ route('admin.courses.create') }}"
                       class="rounded-lg bg-[#0F5132] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0d452c]">
                        + Add New Course
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Featured</th>
                        <th class="px-6 py-3">Students</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($courses as $course)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                <a href="{{ route('admin.courses.modules.index', $course) }}" class="hover:text-emerald-700">
                                    {{ $course->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $course->category->name ?? '—' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">৳{{ number_format($course->price, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $course->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $course->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($course->is_featured)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                        ⭐ Featured
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $course->enrollments_count }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    {{-- Quick actions --}}
                                    <form method="POST" action="{{ route('admin.courses.toggle-published', $course) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="{{ $course->is_published ? 'Unpublish' : 'Publish' }}"
                                                class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                            @if($course->is_published)
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.courses.toggle-featured', $course) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="{{ $course->is_featured ? 'Unfeature' : 'Feature' }}"
                                                class="rounded p-1 text-slate-400 hover:bg-slate-100 {{ $course->is_featured ? 'text-amber-500 hover:text-amber-600' : 'hover:text-slate-600' }}">
                                            <svg class="h-4 w-4" fill="{{ $course->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        </button>
                                    </form>

                                    {{-- Modules --}}
                                    <a href="{{ route('admin.courses.modules.index', $course) }}" title="Manage Modules"
                                       class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-blue-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.courses.edit', $course) }}" title="Edit"
                                       class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-indigo-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                                          onsubmit="return confirm('Delete this course? All modules, lessons, enrollments, and progress will be permanently removed.')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"
                                                class="rounded p-1 text-slate-400 hover:bg-red-50 hover:text-red-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <p class="text-lg font-semibold">No courses found</p>
                                <p class="mt-1 text-sm">
                                    @if($search)
                                        No courses match your search. <a href="{{ route('admin.courses.index') }}" class="text-emerald-600 underline">Clear search</a>
                                    @else
                                        Get started by creating your first course.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($courses->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
@endsection
