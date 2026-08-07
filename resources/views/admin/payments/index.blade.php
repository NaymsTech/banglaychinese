@extends('layouts.admin')

@section('page-title', 'Payment Management')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.payments.index') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $currentStatus === null ? 'bg-[#0F5132] text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-emerald-300' }}">All ({{ $counts['all'] }})</a>
        <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $currentStatus === 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-amber-300' }}">Pending ({{ $counts['pending'] }})</a>
        <a href="{{ route('admin.payments.index', ['status' => 'active']) }}" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $currentStatus === 'active' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-emerald-300' }}">Active ({{ $counts['active'] }})</a>
        <a href="{{ route('admin.payments.index', ['status' => 'cancelled']) }}" class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $currentStatus === 'cancelled' ? 'bg-red-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-red-300' }}">Cancelled ({{ $counts['cancelled'] }})</a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.payments.index') }}" class="mb-6">
        <input type="hidden" name="status" value="{{ $currentStatus }}">
        <div class="flex max-w-xl gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by student name, email, transaction ID, or sender number..."
                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 placeholder-slate-400 focus:border-[#0F5132] focus:ring-2 focus:ring-emerald-200 focus:outline-none">
            <button type="submit" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#0F5132] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#0d452c]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                Search
            </button>
            @if($search !== '' || $currentStatus !== null)
                <a href="{{ route('admin.payments.index') }}" class="inline-flex shrink-0 items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Clear</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Sender</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Transaction ID</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm">
                            <p class="font-semibold text-slate-800">{{ $payment->user?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $payment->user?->email ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ $payment->course?->title ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-800">৳{{ number_format($payment->price_paid, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide {{ $payment->payment_method === 'bkash' ? 'text-pink-600' : 'text-orange-600' }}">{{ $payment->payment_method }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $payment->sender_number ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm font-mono text-slate-600">{{ $payment->transaction_id }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $payment->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($payment->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($payment->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="inline-flex items-center rounded-lg bg-[#0F5132] px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-[#0d452c]">View</a>

                            @if($payment->status === 'pending')
                                <form method="POST" action="{{ route('admin.payments.approve', $payment) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-emerald-700" onclick="return confirm('Approve this payment and unlock course access?');">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-red-700" onclick="return confirm('Reject this payment?');">Reject</button>
                                </form>
                            @elseif($payment->status === 'active')
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 bg-white px-4 py-1.5 text-sm font-semibold text-red-600 transition hover:bg-red-50" onclick="return confirm('Cancel this active enrollment?');">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-sm text-slate-500">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $payments->links() }}
    </div>
@endsection
