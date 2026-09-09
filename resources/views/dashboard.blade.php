<x-app-layout>
    <div class="min-h-screen bg-[#F0FDF4]">
        {{-- Dashboard Sidebar --}}
        <div class="flex">
            {{-- Sidebar --}}
            <aside class="hidden w-72 shrink-0 flex-col border-r border-emerald-100 bg-white lg:flex">
                <div class="flex flex-col items-center gap-3 border-b border-emerald-100 px-6 py-8">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#0F5132] text-2xl font-bold text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-bold text-slate-800">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 px-4 py-6">
                    <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 rounded-lg bg-[#0F5132]/10 px-4 py-3 text-sm font-bold text-[#0F5132]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Overview
                    </a>
                    <a href="{{ route('courses.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-[#F0FDF4] hover:text-[#0F5132]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Browse Courses
                    </a>
                    <a href="{{ route('dashboard.index') }}#downloads" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-[#F0FDF4] hover:text-[#0F5132]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        My Downloads
                    </a>
                    <a href="{{ route('study-in-china') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-[#F0FDF4] hover:text-[#0F5132]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-6.16-3.42M19 11v4"/></svg>
                        Scholarship
                    </a>
                    <a href="{{ route('contact') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-[#F0FDF4] hover:text-[#0F5132]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Support
                    </a>
                </nav>

                {{-- LOGOUT — EXCLUSIVELY HERE (per navigation rules) --}}
                <div class="border-t border-emerald-100 px-4 py-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#DC2626] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#b91c1c]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 px-4 py-8 sm:px-6 lg:px-10">
                {{-- Mobile Logout (Dashboard page only — allowed) --}}
                <div class="mb-6 flex items-center justify-between lg:hidden">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-[#DC2626] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#b91c1c]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </form>
                </div>

                {{-- Welcome Banner --}}
                <div class="overflow-hidden rounded-2xl bg-[#0F5132] shadow-lg shadow-emerald-900/20">
                    <div class="px-6 py-10 sm:px-10">
                        <p class="text-sm font-semibold text-emerald-300">Welcome back 👋</p>
                        <h1 class="mt-2 text-3xl font-extrabold text-white sm:text-4xl">{{ $user->name }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-emerald-100">
                            আপনার শেখা চালিয়ে যান! এখান থেকে আপনার কোর্সসমূহ দেখুন, লেসন খেলুন এবং অগ্রগতি ট্র্যাক করুন।
                        </p>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Enrolled Courses</p>
                        <p class="mt-2 text-3xl font-extrabold text-[#0F5132]">{{ $enrolledCourses->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Lessons</p>
                        <p class="mt-2 text-3xl font-extrabold text-[#0F5132]">{{ $enrolledCourses->sum('total_lessons') }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Completed Lessons</p>
                        <p class="mt-2 text-3xl font-extrabold text-[#0F5132]">{{ $enrolledCourses->sum('completed_lessons') }}</p>
                    </div>
                </div>

                {{-- My Courses: Active --}}
                <h2 class="mt-12 text-xl font-extrabold text-slate-800">My Courses</h2>
                <p class="mt-1 text-sm text-slate-500">সক্রিয় কোর্সসমূহ — যেগুলোতে আপনি এখনই শেখা শুরু করতে পারবেন।</p>

                @forelse($activeCourses as $item)
                    <div class="mt-6 rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm sm:p-8">
                        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-bold text-slate-800">{{ $item->course->title }}</h3>
                                    @if($item->course->is_featured)
                                        <span class="rounded-full bg-[#DC2626]/10 px-3 py-1 text-xs font-bold text-[#DC2626]">FEATURED</span>
                                    @endif
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">ACTIVE</span>
                                </div>
                                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                                    {{ Str::limit($item->course->description ?? 'Start this course to begin learning Chinese step by step.', 180) }}
                                </p>
                                <div class="mt-4 flex items-center gap-4 text-xs font-semibold text-slate-500">
                                    <span>📚 {{ $item->total_lessons }} lessons</span>
                                    @if($item->course->duration_months)
                                        <span>⏱️ {{ $item->course->duration_months }} {{ $item->course->duration_months == 1 ? 'month' : 'months' }}</span>
                                    @endif
                                    @if($item->course->category)
                                        <span class="rounded-full bg-[#F0FDF4] px-3 py-1 text-xs font-bold text-[#0F5132]">{{ $item->course->category->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="w-full sm:w-56 shrink-0">
                                <div class="flex items-center justify-between text-sm font-bold">
                                    <span class="text-slate-600">Progress</span>
                                    <span class="text-[#0F5132]">{{ $item->progress }}%</span>
                                </div>
                                <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-emerald-100">
                                    <div class="h-full rounded-full bg-[#0F5132] transition-all" style="width: {{ $item->progress }}%"></div>
                                </div>
                                @php $firstLesson = $item->course->lessons()->orderBy('order')->first(); @endphp
                                @if($firstLesson)
                                    <a href="{{ route('dashboard.lessons.show', ['course' => $item->course->slug, 'lesson' => $firstLesson->slug]) }}"
                                       class="mt-4 block w-full rounded-lg bg-[#0F5132] px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-[#0d452c]">
                                        Continue Learning
                                    </a>
                                @else
                                    <a href="{{ route('courses.show', $item->course->slug) }}"
                                       class="mt-4 block w-full rounded-lg bg-[#0F5132] px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-[#0d452c]">
                                        View Course
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="mt-6 rounded-2xl border border-dashed border-emerald-200 bg-white p-10 text-center">
                        <svg class="mx-auto h-12 w-12 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <h3 class="mt-4 text-lg font-bold text-slate-800">No active courses yet</h3>
                        <p class="mt-2 text-sm text-slate-500">আপনার প্রথম চীনা ভাষার কোর্সে ভর্তি হোন এবং আজই শেখা শুরু করুন!</p>
                        <a href="{{ route('courses.index') }}" class="mt-6 inline-flex items-center rounded-full bg-[#0F5132] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#0d452c]">
                            Browse Courses →
                        </a>
                    </div>
                @endforelse

                {{-- My Courses: Pending --}}
                @if($pendingCourses->isNotEmpty())
                    <h2 class="mt-12 text-xl font-extrabold text-slate-800">Pending Payments</h2>
                    <p class="mt-1 text-sm text-slate-500">এই কোর্সগুলোর পেমেন্ট যাচাইকরণের অধীনে রয়েছে।</p>

                    @foreach($pendingCourses as $item)
                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm sm:p-8">
                            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-lg font-bold text-slate-800">{{ $item->course->title }}</h3>
                                        {{-- Warning badge: Payment under review --}}
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            পেমেন্ট পর্যালোচনায় আছে
                                        </span>
                                    </div>
                                    <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                                        {{ Str::limit($item->course->description ?? 'Start this course to begin learning Chinese step by step.', 180) }}
                                    </p>
                                    <div class="mt-4 flex items-center gap-4 text-xs font-semibold text-slate-500">
                                        <span>📚 {{ $item->total_lessons }} lessons</span>
                                        @if($item->course->price)
                                            <span>💰 ৳{{ number_format($item->course->price) }}</span>
                                        @endif
                                    </div>
                                    @if($item->payment_paid > 0 || $item->payment_due > 0)
                                        <div class="mt-3 inline-flex flex-wrap items-center gap-2 rounded-xl bg-white/80 px-3 py-2 text-xs font-bold">
                                            @if($item->payment_paid > 0)
                                                <span class="text-emerald-700">Paid: ৳{{ number_format($item->payment_paid, 2) }}</span>
                                            @endif
                                            @if($item->payment_due > 0)
                                                <span class="text-amber-700">Due: ৳{{ number_format($item->payment_due, 2) }}</span>
                                            @else
                                                <span class="text-emerald-700">Due: ৳0.00</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="w-full sm:w-56 shrink-0">
                                    <div class="rounded-lg border border-dashed border-amber-300 bg-white/70 p-4 text-center">
                                        <svg class="mx-auto h-6 w-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <p class="mt-2 text-xs font-bold text-amber-700">লেসনে প্রবেশ নিষ্ক্রিয়</p>
                                        <p class="mt-1 text-[11px] leading-relaxed text-amber-600">
                                            পেমেন্ট অ্যাডমিন কর্তৃক অনুমোদনের পর লেসনগুলো আনলক হবে।
                                        </p>
                                    </div>
                                    <a href="{{ route('courses.show', $item->course->slug) }}"
                                       class="mt-4 block w-full rounded-lg bg-amber-100 px-4 py-2.5 text-center text-sm font-bold text-amber-700 transition hover:bg-amber-200">
                                        View Course
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- My Downloads --}}
                <h2 id="downloads" class="mt-12 scroll-mt-24 text-xl font-extrabold text-slate-800">My Downloads</h2>
                <p class="mt-1 text-sm text-slate-500">আপনার অনুমোদিত ডিজিটাল প্রোডাক্ট — ডাউনলোড করুন যেকোনো সময়।</p>

                @forelse($approvedDownloads as $download)
                    <div class="mt-6 rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm sm:p-8">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                @if($download->product->cover_image)
                                    <img src="{{ asset('storage/'.$download->product->cover_image) }}" alt="{{ $download->product->title }}" class="h-16 w-16 flex-none rounded-xl object-cover">
                                @else
                                    <div class="flex h-16 w-16 flex-none items-center justify-center rounded-xl bg-emerald-50 text-2xl">📘</div>
                                @endif
                                <div>
                                    <h3 class="font-bold text-slate-800">{{ $download->product->title }}</h3>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-slate-500">
                                        @if($download->product->category)
                                            <span class="rounded-full bg-[#F0FDF4] px-2.5 py-0.5 font-bold text-[#0F5132]">{{ $download->product->category }}</span>
                                        @endif
                                        <span>Purchased: {{ $download->created_at->format('d M Y') }}</span>
                                        @if($download->unifiedOrder)
                                            <span>৳{{ number_format((float) $download->unifiedOrder->total_amount, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('dashboard.downloads.download', $download) }}"
                               class="inline-flex flex-none items-center justify-center gap-2 rounded-full bg-[#0F5132] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#0d452c]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download PDF
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="mt-6 rounded-2xl border border-dashed border-emerald-200 bg-white p-10 text-center">
                        <svg class="mx-auto h-12 w-12 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <h3 class="mt-4 text-lg font-bold text-slate-800">No downloads yet</h3>
                        <p class="mt-2 text-sm text-slate-500">আপনার কেনা ডিজিটাল প্রোডাক্ট অনুমোদিত হলে এখানে দেখা যাবে।</p>
                        <a href="{{ route('shop.index') }}" class="mt-6 inline-flex items-center rounded-full bg-[#0F5132] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#0d452c]">
                            Browse the Shop →
                        </a>
                    </div>
                @endforelse
            </main>
        </div>
    </div>
</x-app-layout>
