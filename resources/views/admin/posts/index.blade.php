@extends('layouts.admin')

@section('page-title', 'Blog')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
        {{-- Header with search and actions --}}
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.posts.index', ['status' => 'all'] + request()->only('search')) }}"
                           class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ ($currentStatus ?? 'all') === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            All
                        </a>
                        <a href="{{ route('admin.posts.index', ['status' => 'published'] + request()->only('search')) }}"
                           class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ ($currentStatus ?? 'all') === 'published' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Published
                        </a>
                        <a href="{{ route('admin.posts.index', ['status' => 'draft'] + request()->only('search')) }}"
                           class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ ($currentStatus ?? 'all') === 'draft' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Draft
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('admin.posts.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="status" value="{{ $currentStatus ?? 'all' }}">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search posts..."
                               class="w-48 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        <button type="submit"
                                class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200">
                            Search
                        </button>
                    </form>
                    <a href="{{ route('admin.posts.create') }}"
                       class="rounded-lg bg-[#0F5132] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0d452c]">
                        + Add New Post
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
                        <th class="px-6 py-3">Author</th>
                        <th class="px-6 py-3">Published Date</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($posts as $post)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $post->title }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $post->category->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $post->author->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $post->published_at ? $post->published_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $post->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $post->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    {{-- View on site --}}
                                    <a href="{{ route('posts.show', $post->slug) }}" target="_blank" title="View on site"
                                       class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-blue-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.posts.edit', $post) }}" title="Edit"
                                       class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-indigo-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}"
                                          onsubmit="return confirm('Delete this post? This action cannot be undone.')"
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
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <p class="text-lg font-semibold">No posts found</p>
                                <p class="mt-1 text-sm">
                                    @if($search)
                                        No posts match your search. <a href="{{ route('admin.posts.index') }}" class="text-emerald-600 underline">Clear search</a>
                                    @else
                                        Get started by creating your first blog post.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($posts->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
