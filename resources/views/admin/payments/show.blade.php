@extends('layouts.admin')

@section('page-title', 'Payment Detail')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.payments.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-[#0F5132]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Payments
        </a>
    </div>

    <div class="mb-6 flex items-center justify-between rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Payment #{{ $enrollment->id }}</h2>
            <p class="mt-1 text-sm text-slate-500">Submitted {{ $enrollment->created_at->format('M d, Y g:i A') }}</p>
        </div>
        <span class="inline-flex rounded-full px-4 py-1.5 text-sm font-bold {{ $enrollment->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($enrollment->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($enrollment->status) }}</span>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Student</h3>
                <div class="mt-4 space-y-2 text-sm">
                    <p class="font-semibold text-slate-800">{{ $enrollment->user?->name ?? '—' }}</p>
                    <p class="text-slate-600">{{ $enrollment->user?->email ?? '—' }}</p>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Course</h3>
                <p class="mt-3 text-sm font-semibold text-slate-800">{{ $enrollment->course?->title ?? '—' }}</p>
                @if($enrollment->course?->price)
                    <p class="mt-1 text-xs font-bold text-slate-500">৳{{ number_format($enrollment->course->price, 2) }}</p>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Timeline</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-slate-400">Submitted At</dt>
                        <dd class="mt-0.5 font-semibold text-slate-700">{{ $enrollment->created_at->format('M d, Y g:i A') }}</dd>
                    </div>
                    @if($enrollment->paid_at)
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Paid / Approved At</dt>
                            <dd class="mt-0.5 font-semibold text-emerald-700">{{ $enrollment->paid_at->format('M d, Y g:i A') }}</dd>
                        </div>
                    @endif
                    @if($enrollment->updated_at && $enrollment->status === 'cancelled')
                        <div>
                            <dt class="text-xs font-semibold uppercase text-slate-400">Cancelled At</dt>
                            <dd class="mt-0.5 font-semibold text-red-700">{{ $enrollment->updated_at->format('M d, Y g:i A') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200 lg:col-span-2">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Payment Details</h3>
            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Amount Paid</dt>
                    <dd class="mt-1 text-2xl font-extrabold text-slate-800">৳{{ number_format($enrollment->price_paid, 2) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Payment Method</dt>
                    <dd class="mt-1 inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-bold uppercase tracking-wide {{ $enrollment->payment_method === 'bkash' ? 'text-pink-600' : 'text-orange-600' }}">{{ $enrollment->payment_method }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Sender Number</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-700">{{ $enrollment->sender_number ?? '—' }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Transaction ID</dt>
                    <dd class="mt-1 font-mono text-sm font-bold text-slate-700">{{ $enrollment->transaction_id }}</dd>
                </div>
            </dl>

            <div class="mt-8 border-t border-slate-200 pt-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Actions</h3>
                <div class="mt-4 flex flex-wrap gap-3">
                    @if($enrollment->status === 'pending')
                        <form method="POST" action="{{ route('admin.payments.approve', $enrollment) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700" onclick="return confirm('Approve this payment and unlock course access?');">Approve & Unlock</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.reject', $enrollment) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-700" onclick="return confirm('Reject this payment?');">Reject / Cancel</button>
                        </form>
                    @elseif($enrollment->status === 'active')
                        <form method="POST" action="{{ route('admin.payments.reject', $enrollment) }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-red-200 bg-white px-5 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50" onclick="return confirm('Cancel this active enrollment?');">Cancel Enrollment</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
