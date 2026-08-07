@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Registered Students</p>
                    <p class="mt-1 text-3xl font-bold text-slate-800">{{ $totalStudents }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-6.927 4 4 0 004 6.927zM16 13a4 4 0 10-4-6.927"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Active Courses</p>
                    <p class="mt-1 text-3xl font-bold text-slate-800">{{ $activeCourses }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Pending Scholarships</p>
                    <p class="mt-1 text-3xl font-bold text-amber-600">{{ $pendingScholarships }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Published Posts</p>
                    <p class="mt-1 text-3xl font-bold text-slate-800">{{ $publishedPosts }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-purple-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <a href="{{ route('admin.scholarships.index') }}" class="rounded-2xl bg-[#0F5132] p-6 text-white shadow-sm transition hover:bg-[#0d452c]">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Manage Scholarships</h3>
                    <p class="mt-1 text-sm text-emerald-200">Review and update scholarship application statuses.</p>
                </div>
                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
        <a href="{{ route('posts.index') }}" class="rounded-2xl bg-white p-6 text-slate-800 shadow-sm border border-slate-200 transition hover:border-emerald-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Manage Content</h3>
                    <p class="mt-1 text-sm text-slate-500">View published blog posts and courses.</p>
                </div>
                <svg class="h-8 w-8 text-[#0F5132]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
    </div>
@endsection
