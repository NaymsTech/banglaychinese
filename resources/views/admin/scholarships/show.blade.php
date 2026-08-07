@extends('layouts.admin')

@section('page-title', 'Application Detail')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.scholarships.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-[#0F5132]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Applications
        </a>
    </div>

    <div class="mb-6 flex items-center justify-between rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <div>
            <h2 class="text-xl font-bold text-slate-800">{{ $application->name }}</h2>
            <p class="mt-1 text-sm text-slate-500">Submitted {{ $application->created_at->format('M d, Y g:i A') }}</p>
        </div>
        <span class="inline-flex rounded-full px-4 py-1.5 text-sm font-bold {{ $application->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($application->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($application->status) }}</span>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Applicant Contact</h3>
                <div class="mt-4 space-y-3 text-sm">
                    <p class="font-semibold text-slate-800">{{ $application->name }}</p>
                    <p class="text-slate-600">{{ $application->email }}</p>
                    <p class="text-slate-600">{{ $application->phone }}</p>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Desired Program</h3>
                <p class="mt-3 text-sm font-semibold text-slate-800">{{ $application->desired_program ?? $application->target_course }}</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Academic Details</h3>
                <div class="mt-3 space-y-2 text-sm text-slate-600">
                    <p><span class="font-medium text-slate-500">Qualification:</span> {{ $application->qualification ?? '—' }}</p>
                    @if($application->gpa)
                        <p><span class="font-medium text-slate-500">GPA / CGPA:</span> {{ $application->gpa }}</p>
                    @endif
                    @if($application->target_intake)
                        <p><span class="font-medium text-slate-500">Target Intake:</span> {{ $application->target_intake }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200 lg:col-span-2">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Statement of Purpose</h3>
            <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $application->statement_of_purpose }}</p>

            <div class="mt-8 border-t border-slate-200 pt-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Update Status</h3>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('admin.scholarships.status', $application) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button type="submit"
                            @if($application->status === 'approved')
                                disabled
                                class="rounded-lg border-2 border-emerald-600 bg-white px-5 py-2 text-sm font-semibold text-emerald-600 cursor-not-allowed"
                            @else
                                class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            @endif
                        >
                            @if($application->status === 'approved')
                                ✓ Approved (Current)
                            @else
                                Approve
                            @endif
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.scholarships.status', $application) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="pending">
                        <button type="submit"
                            @if($application->status === 'pending')
                                disabled
                                class="rounded-lg border-2 border-amber-500 bg-white px-5 py-2 text-sm font-semibold text-amber-500 cursor-not-allowed"
                            @else
                                class="rounded-lg bg-amber-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-amber-600"
                            @endif
                        >
                            @if($application->status === 'pending')
                                ● Pending (Current)
                            @else
                                Mark Pending
                            @endif
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.scholarships.status', $application) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit"
                            @if($application->status === 'rejected')
                                disabled
                                class="rounded-lg border-2 border-red-600 bg-white px-5 py-2 text-sm font-semibold text-red-600 cursor-not-allowed"
                            @else
                                class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                            @endif
                        >
                            @if($application->status === 'rejected')
                                ✗ Rejected (Current)
                            @else
                                Reject
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
