@extends('layouts.admin')

@section('page-title', 'Edit Course')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-800">Edit: {{ $course->title }}</h2>
        </div>

        <form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data" class="px-6 py-6">
            @csrf
            @method('PUT')
            @include('admin.courses._form', ['course' => $course])

            <div class="mt-8 flex items-center gap-4">
                <button type="submit"
                        class="rounded-lg bg-[#0F5132] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0d452c]">
                    Update Course
                </button>
                <a href="{{ route('admin.courses.index') }}"
                   class="rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
