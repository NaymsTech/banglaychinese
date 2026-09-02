@extends('layouts.admin')

@section('page-title', 'Scholarship Applications')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.scholarships.index') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $currentStatus === null ? 'bg-[#0F5132] text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-emerald-300' }}">All</a>
        <a href="{{ route('admin.scholarships.index', ['status' => 'pending']) }}" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $currentStatus === 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-amber-300' }}">Pending ({{ $counts['pending'] }})</a>
        <a href="{{ route('admin.scholarships.index', ['status' => 'approved']) }}" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $currentStatus === 'approved' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-emerald-300' }}">Approved ({{ $counts['approved'] }})</a>
        <a href="{{ route('admin.scholarships.index', ['status' => 'rejected']) }}" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $currentStatus === 'rejected' ? 'bg-red-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-red-300' }}">Rejected ({{ $counts['rejected'] }})</a>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Applicant</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Qualification</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Desired Program</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Intake</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Submitted</th>
                    <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse ($applications as $application)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm font-semibold text-slate-800">{{ $application->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <p>{{ $application->email }}</p>
                            <p class="text-xs text-slate-400">{{ $application->phone }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <span class="font-medium">{{ $application->qualification ?? '—' }}</span>
                            @if($application->gpa)
                                <span class="ml-1 text-xs text-slate-400">({{ $application->gpa }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $application->desired_program ?? $application->target_course }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if($application->target_intake)
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $application->target_intake }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php $displayStatus = $application->application_status ?? 'pending'; @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $displayStatus === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($displayStatus === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($displayStatus) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $application->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.scholarships.show', $application) }}" class="inline-flex items-center rounded-lg bg-[#0F5132] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#0d452c]">View</a>
                                @if (($application->application_status ?? 'pending') !== 'approved')
                                    <form method="POST" action="{{ route('admin.scholarships.status', $application) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">Approve</button>
                                    </form>
                                @endif
                                @if (($application->application_status ?? 'pending') !== 'rejected')
                                    <form method="POST" action="{{ route('admin.scholarships.status', $application) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700">Reject</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">No scholarship applications found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $applications->links() }}
    </div>
@endsection
