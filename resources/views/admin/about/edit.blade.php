@extends('layouts.admin')

@section('title', 'Edit About Page')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-primary-800">📄 Edit About Page</h1>
        <p class="mt-1 text-sm text-primary-600">Manage all content sections for the About Us page.</p>
    </div>

    @if(session('success'))
    <div class="mb-6 rounded-lg bg-primary-50 border border-primary-200 px-4 py-3 text-primary-700 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('admin.about.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- HERO SECTION --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>👤</span> Hero Section
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($sections['hero'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- STORY SECTION --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>📖</span> My Story
            </h2>
            <div class="space-y-4">
                @foreach($sections['story'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- TIMELINE SECTION --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>📅</span> Timeline
            </h2>
            <div class="space-y-4">
                @foreach($sections['timeline'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- EXPERIENCE SECTION --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>🏆</span> Experience & Recognition
            </h2>
            <div class="space-y-4">
                @foreach($sections['experience'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- WHY SECTION --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>💡</span> Why BanglayChinese?
            </h2>
            <div class="space-y-4">
                @foreach($sections['why'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- MISSION SECTION --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>🎯</span> Mission
            </h2>
            <div class="space-y-4">
                @foreach($sections['mission'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- COMMITMENT SECTION --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>🤝</span> Our Commitment
            </h2>
            <div class="space-y-4">
                @foreach($sections['commitment'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- VISION SECTION --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>🔭</span> Vision
            </h2>
            <div class="space-y-4">
                @foreach($sections['vision'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- WHY CHOOSE US --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>⭐</span> Why Choose Us
            </h2>
            <div class="space-y-4">
                @foreach($sections['choose_us'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- FAQ --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>❓</span> FAQ
            </h2>
            <div class="space-y-4">
                @foreach($sections['faq'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        {{-- CTA --}}
        <div class="mb-8 rounded-xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="mb-4 text-lg font-semibold text-primary-800 flex items-center gap-2">
                <span>🚀</span> Final CTA
            </h2>
            <div class="space-y-4">
                @foreach($sections['cta'] ?? [] as $section)
                    @include('admin.about._field', ['section' => $section])
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-primary-600 px-6 py-3 text-sm font-semibold text-white hover:bg-primary-700 transition shadow-md">
                💾 Save All Changes
            </button>
        </div>
    </form>
</div>
@endsection
