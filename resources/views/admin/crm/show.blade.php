@extends('layouts.admin')

@section('page-title', 'Lead #' . $lead->id . ' - ' . $lead->name)

@section('content')
<div class="space-y-6">
    {{-- Back + Actions Header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.crm.index', request()->only('search', 'status', 'sort', 'direction')) }}" class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Leads
        </a>
        <span class="text-sm text-slate-400">Created {{ $lead->created_at->format('M d, Y \a\t g:i A') }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left: Lead Info --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Personal Details --}}
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-800">Lead Information</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Full Name</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $lead->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Email</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $lead->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Phone</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $lead->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Current Qualification</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $lead->highest_qualification ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Desired Degree</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $lead->desired_program ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Preferred Intake</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $lead->target_intake ?? '—' }}</dd>
                        </div>
                        @if ($lead->budget)
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Budget</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $lead->budget }}</dd>
                        </div>
                        @endif
                        @if ($lead->preferred_consultation_time)
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Preferred Consultation Time</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $lead->preferred_consultation_time }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if ($lead->message)
                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <dt class="text-xs font-semibold uppercase text-slate-400">Message</dt>
                        <dd class="mt-2 text-sm leading-relaxed text-slate-600 whitespace-pre-wrap">{{ $lead->message }}</dd>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Admin Notes --}}
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-800">Admin Notes</h3>
                </div>
                <div class="px-6 py-4">
                    @if ($lead->admin_notes)
                        <p class="text-sm leading-relaxed text-slate-600 whitespace-pre-wrap">{{ $lead->admin_notes }}</p>
                    @else
                        <p class="text-sm italic text-slate-400">No notes yet.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Status & Update Sidebar --}}
        <div class="space-y-6">
            {{-- Current Status --}}
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-800">Status</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    @php
                        $statusColors = [
                            'new' => 'bg-blue-100 text-blue-700',
                            'contacted' => 'bg-yellow-100 text-yellow-700',
                            'consultation_scheduled' => 'bg-purple-100 text-purple-700',
                            'application_started' => 'bg-orange-100 text-orange-700',
                            'converted' => 'bg-green-100 text-green-700',
                            'closed' => 'bg-slate-100 text-slate-500',
                        ];
                    @endphp
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $statusColors[$lead->status] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ $statusLabels[$lead->status] ?? ucfirst($lead->status) }}
                    </span>

                    @if ($lead->follow_up_date)
                    <div>
                        <dt class="text-xs font-semibold uppercase text-slate-400">Follow-up Date</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ \Carbon\Carbon::parse($lead->follow_up_date)->format('F d, Y') }}</dd>
                    </div>
                    @endif

                    <div>
                        <dt class="text-xs font-semibold uppercase text-slate-400">Last Updated</dt>
                        <dd class="mt-1 text-sm text-slate-600">{{ $lead->updated_at->format('M d, Y g:i A') }}</dd>
                    </div>
                </div>
            </div>

            {{-- Update Form --}}
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-800">Update Lead</h3>
                </div>
                <form method="POST" action="{{ route('admin.crm.update', $lead) }}" class="px-6 py-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" id="status"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach ($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ $lead->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="follow_up_date" class="block text-sm font-semibold text-slate-700">Follow-up Date</label>
                        <input type="date" name="follow_up_date" id="follow_up_date"
                            value="{{ $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('Y-m-d') : '' }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="admin_notes" class="block text-sm font-semibold text-slate-700">Notes</label>
                        <textarea name="admin_notes" id="admin_notes" rows="5"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Add internal notes about this lead...">{{ old('admin_notes', $lead->admin_notes) }}</textarea>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 transition">
                        Update Lead
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
