<x-app-layout :metaTitle="'Payment Submitted | ' . config('app.name', 'Banglay Chinese')">
    <section class="bg-white py-16 sm:py-24">
        <div class="mx-auto max-w-lg px-4 text-center sm:px-6 lg:px-8">
            {{-- Success Icon --}}
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-10 w-10 text-[#0F5132]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="mt-6 text-2xl font-extrabold text-slate-900 sm:text-3xl">
                Payment Submitted Successfully
            </h1>

            <p class="mt-3 text-slate-600">
                Your payment is under review. You will get access within 24 hours.
            </p>

            {{-- Transaction ID Card --}}
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Transaction ID</p>
                <p class="mt-1 text-lg font-extrabold text-[#0F5132]">{{ $transactionId }}</p>
            </div>

            <p class="mt-4 text-sm text-slate-500">
                We will notify you once your payment is verified.
            </p>

            <p class="mt-1 text-sm text-slate-500">
                Save your Transaction ID for future reference.
            </p>

            {{-- Action Buttons --}}
            <div class="mt-8 space-y-3">
                <a href="{{ route('dashboard.index') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-full bg-[#0F5132] px-6 py-4 text-base font-bold text-white transition hover:bg-[#0d452c] active:scale-[0.98]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Go to Dashboard
                </a>
                <a href="{{ route('courses.index') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-full border-2 border-[#0F5132] px-6 py-4 text-base font-bold text-[#0F5132] transition hover:bg-emerald-50 active:scale-[0.98]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Browse More Courses
                </a>
            </div>

            {{-- Social Proof & Cross-sell --}}
            <div class="mt-8 rounded-2xl border border-emerald-200 bg-white p-6 text-left shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-200 text-sm font-bold text-emerald-800 ring-2 ring-white">👨‍🎓</div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-200 text-sm font-bold text-amber-800 ring-2 ring-white">👩‍🎓</div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-200 text-sm font-bold text-blue-800 ring-2 ring-white">🧑‍🎓</div>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">৫০০+ শিক্ষার্থী সফলভাবে কোর্স সম্পন্ন করেছে</p>
                        <p class="text-xs text-slate-500 mt-0.5">আমাদের পরবর্তী ব্যাচে আপনার জায়গা নিশ্চিত করুন</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
