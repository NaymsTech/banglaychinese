@extends('layouts.admin')

@section('page-title', 'CRM - Lead Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Lead Management</h2>
            <p class="mt-1 text-sm text-slate-500">Manage consultation requests and track lead status.</p>
        </div>
    </div>

    {{-- Status Stats --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach (['new' => 'New', 'contacted' => 'Contacted', 'consultation_scheduled' => 'Scheduled', 'application_started' => 'In Progress', 'converted' => 'Converted', 'closed' => 'Closed'] as $key => $label)
        <a href="{{ route('admin.crm.index', ['status' => request('status') === $key ? null : $key]) }}"
            class="rounded-xl border p-4 text-center transition hover:shadow-md {{ request('status') == $key ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-200 bg-white' }}">
            <p class="text-2xl font-bold {{ request('status') == $key ? 'text-primary-600' : 'text-slate-700' }}">
                {{ $statusCounts[$key] ?? 0 }}
            </p>
            <p class="mt-1 text-xs font-medium {{ request('status') == $key ? 'text-primary-600' : 'text-slate-500' }}">{{ $label }}</p>
        </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.crm.index') }}" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone..."
            class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
        <button type="submit" class="rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 transition">
            Search
        </button>
        @if (request('search'))
        <a href="{{ route('admin.crm.index', request()->only('status', 'sort', 'direction')) }}" class="flex items-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50">
            Clear
        </a>
        @endif
    </form>

    {{-- Leads Table --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            <a href="{{ route('admin.crm.index', ['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}" class="hover:text-slate-700">
                                Name
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Program</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Follow-up</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            <a href="{{ route('admin.crm.index', ['sort' => 'created_at', 'direction' => request('sort') === 'created_at' && request('direction') === 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}" class="hover:text-slate-700">
                                Date
                            </a>
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($leads as $lead)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">#{{ $lead->id }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <p class="text-sm font-semibold text-slate-800">{{ $lead->name }}</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <p class="text-sm text-slate-600">{{ $lead->email }}</p>
                            <p class="text-xs text-slate-400">{{ $lead->phone }}</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                            {{ $lead->desired_program ?? 'N/A' }}<br>
                            <span class="text-xs text-slate-400">{{ $lead->target_intake ?? '' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
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
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$lead->status] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $statusLabels[$lead->status] ?? ucfirst($lead->status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            @if ($lead->follow_up_date)
                                <span class="text-slate-600">{{ \Carbon\Carbon::parse($lead->follow_up_date)->format('M d, Y') }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">
                            {{ $lead->created_at->format('M d, Y') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.crm.show', $lead) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-800 transition">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">
                            No leads found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leads->hasPages())
        <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
            {{ $leads->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
