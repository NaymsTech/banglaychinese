@extends('layouts.admin')

@section('page-title', 'Edit Service')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-800">Edit: {{ $service->name }}</h2>
        </div>

        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="px-6 py-6">
            @csrf
            @method('PUT')
            @include('admin.services._form', ['service' => $service])

            <div class="mt-8 flex items-center gap-4">
                <button type="submit"
                        class="rounded-lg bg-[#0F5132] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0d452c]">
                    Update Service
                </button>
                <a href="{{ route('admin.services.index') }}"
                   class="rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
