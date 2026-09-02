@extends('layouts.admin')

@section('page-title', 'Services')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
        {{-- Header with search, filter and actions --}}
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex gap-2">
                    <a href="{{ route('admin.services.index', ['status' => 'all'] + request()->only('search')) }}"
                       class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $status === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        All
                    </a>
                    <a href="{{ route('admin.services.index', ['status' => 'active'] + request()->only('search')) }}"
                       class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $status === 'active' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Active
                    </a>
                    <a href="{{ route('admin.services.index', ['status' => 'inactive'] + request()->only('search')) }}"
                       class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $status === 'inactive' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Inactive
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('admin.services.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search services..."
                               class="w-48 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        <button type="submit"
                                class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200">
                            Search
                        </button>
                    </form>
                    <a href="{{ route('admin.services.create') }}"
                       class="rounded-lg bg-[#0F5132] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0d452c]">
                        + Add New Service
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Order</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Features</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($services as $service)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <form method="POST" action="{{ route('admin.services.move-up', $service) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="Move Up" class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-emerald-600">↑</button>
                                    </form>
                                    <span class="text-slate-500">{{ $service->sort_order }}</span>
                                    <form method="POST" action="{{ route('admin.services.move-down', $service) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="Move Down" class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-emerald-600">↓</button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-semibold text-slate-800">{{ $service->name }}</div>
                                <div class="text-xs text-slate-400">/{{ $service->slug }}</div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">৳{{ number_format($service->price) }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-slate-600">{{ $service->duration ?: '—' }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ is_array($service->features) ? count($service->features) : 0 }} items</td>

                            <td class="px-6 py-3 whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.services.toggle-status', $service) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-full px-3 py-1 text-xs font-bold {{ $service->status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $service->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.services.edit', $service) }}" title="Edit"
                                       class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-indigo-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}"
                                          onsubmit="return confirm('Delete this service package?')"
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
                                <p class="text-lg font-semibold">No services found</p>
                                <p class="mt-1 text-sm">
                                    @if($search)
                                        No services match your search. <a href="{{ route('admin.services.index') }}" class="text-emerald-600 underline">Clear search</a>
                                    @else
                                        Get started by creating your first service package.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($services->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $services->links() }}
            </div>
        @endif
    </div>
@endsection
