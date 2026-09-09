<x-app-layout :metaTitle="$metaTitle" :robots="'noindex, nofollow'">
    <section class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-10 text-white sm:py-14">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-4 text-sm text-emerald-200">
                <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('courses.index') }}" class="transition hover:text-white">Courses</a>
                <span class="mx-2">/</span>
                <span class="text-white">Order Submitted</span>
            </nav>
            <h1 class="text-2xl font-extrabold sm:text-3xl">আপনার অর্ডারটি আমরা পেয়েছি</h1>
            <p class="mt-2 text-sm text-emerald-200">Thank you — your course enrollment request has been received.</p>
        </div>
    </section>

    <section class="bg-white py-12 sm:py-20">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-8 text-center sm:p-12">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#0F5132]">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>

                <h2 class="mt-6 text-xl font-extrabold text-slate-900">{{ $courseTitle }}</h2>

                <dl class="mx-auto mt-6 max-w-sm space-y-3 rounded-xl bg-white p-5 text-left text-sm ring-1 ring-emerald-100">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">অর্ডার নম্বর</dt>
                        <dd class="font-bold text-slate-900">#{{ $orderNumber }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">মোট</dt>
                        <dd class="font-bold text-slate-900">{{ $displayAmount }}</dd>
                    </div>
                </dl>

                <div class="mt-6 rounded-xl bg-amber-50 px-5 py-4 text-left text-sm leading-relaxed text-amber-800 ring-1 ring-amber-200">
                    <p class="font-bold">পেমেন্ট যাচাই চলছে</p>
                    <p class="mt-1">
                        আপনার পেমেন্ট ম্যানুয়ালি যাচাই করা হবে। যাচাই সম্পন্ন হলে কোর্স অ্যাক্সেস সক্রিয় হয়ে যাবে এবং আপনি ইমেইলে জানতে পারবেন।
                        @if($reviewStatus === 'paid')
                            আপনার পেমেন্ট সম্পন্ন হয়েছে — কোর্সটি শুরুর জন্য ড্যাশবোর্ডে যান।
                        @endif
                    </p>
                </div>

                <div class="mt-6 rounded-xl bg-slate-50 px-5 py-4 text-left text-sm leading-relaxed text-slate-600 ring-1 ring-slate-200">
                    <p>
                        অর্ডার ও অ্যাকাউন্ট সংক্রান্ত যেকোনো তথ্য আপনার ইমেইলে পাঠানো হয়েছে — অনুগ্রহ করে ইনবক্স (এবং স্প্যাম ফোল্ডার) চেক করুন।
                    </p>
                </div>

                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('courses.index') }}" class="inline-flex items-center justify-center rounded-full bg-[#0F5132] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#0d452c]">
                        আরও কোর্স দেখুন
                    </a>
                    @auth
                        <a href="{{ route('dashboard.index') }}" class="inline-flex items-center justify-center rounded-full border-2 border-[#0F5132] px-6 py-3 text-sm font-bold text-[#0F5132] transition hover:bg-emerald-50">
                            ড্যাশবোর্ডে যান
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
