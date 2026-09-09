<x-app-layout :robots="'noindex, nofollow'">
    <div class="min-h-screen bg-[#F0FDF4]">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm font-semibold text-slate-500">
                <a href="{{ route('dashboard.index') }}" class="transition hover:text-[#0F5132]">Dashboard</a>
                <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('courses.show', $course->slug) }}" class="transition hover:text-[#0F5132]">{{ $course->title }}</a>
                <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-[#0F5132]">{{ $lesson->title }}</span>
            </nav>

            @if (session('status'))
                <div class="mt-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-white p-4 shadow-sm">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#0F5132]/10 text-[#0F5132]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <p class="text-sm font-bold text-[#0F5132]">{{ session('status') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mt-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <p class="text-sm font-bold text-amber-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Pending Payment Banner (visible even on free previews) --}}
            @if(isset($enrollmentStatus) && $enrollmentStatus === 'pending')
                <div class="mt-6 flex items-center gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 shadow-sm">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-amber-800">পেমেন্ট পর্যালোচনায় আছে</p>
                        <p class="mt-0.5 text-xs font-semibold text-amber-700">আপনার পেমেন্ট বর্তমানে যাচাইকরণের অধীনে রয়েছে। শুধুমাত্র ফ্রি প্রিভিউ লেসনগুলো দেখা যাবে। অ্যাডমিন অনুমোদনের পর সব লেসন আনলক হবে।</p>
                    </div>
                </div>
            @endif

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- Main Player --}}
                <div class="lg:col-span-2">
                    {{-- Video / Hero --}}
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                        @if($lesson->video_url)
                            <div class="aspect-video w-full bg-black">
                                <video controls class="h-full w-full" poster="{{ $course->thumbnail ? asset('storage/'.$course->thumbnail) : '' }}">
                                    <source src="{{ $lesson->video_url }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @else
                            <div class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-[#0F5132] to-[#0d452c]">
                                <div class="text-center">
                                    <svg class="mx-auto h-16 w-16 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                                    <p class="mt-3 text-sm font-bold text-emerald-200">Video Coming Soon</p>
                                </div>
                            </div>
                        @endif

                        <div class="p-6 sm:p-8">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-[#0F5132]/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-[#0F5132]">Lesson {{ $lesson->order }}</span>
                                @if($lesson->is_free_preview)
                                    <span class="rounded-full bg-[#DC2626]/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-[#DC2626]">Free Preview</span>
                                @endif
                                @if($completed)
                                    <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-600">✓ Completed</span>
                                @endif
                            </div>

                            <h1 class="mt-4 text-2xl font-extrabold text-slate-800 sm:text-3xl">{{ $lesson->title }}</h1>
                            <p class="mt-2 text-sm font-semibold text-slate-500">{{ $course->title }}</p>

                            {{-- Complete / Incomplete Toggle --}}
                            <div class="mt-6">
                                <form method="POST" action="{{ route('dashboard.lessons.complete', $lesson) }}" class="inline-flex">
                                    @csrf
                                    @if($completed)
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-full border-2 border-[#DC2626] bg-white px-6 py-3 text-sm font-bold text-[#DC2626] transition hover:bg-[#DC2626] hover:text-white">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Mark as Incomplete
                                        </button>
                                    @else
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-[#0F5132] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-[#0d452c]">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Mark as Complete
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Lesson Content --}}
                    <div class="mt-8 rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm sm:p-8">
                        <h2 class="text-lg font-extrabold text-slate-800">Lesson Content</h2>
                        <div class="mt-4 prose prose-emerald max-w-none leading-relaxed text-slate-600">
                            {!! $lesson->content !!}
                        </div>
                    </div>

                    {{-- Prev / Next Navigation --}}
                    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @if($prevLesson)
                            <a href="{{ route('dashboard.lessons.show', ['course' => $course->slug, 'lesson' => $prevLesson->slug]) }}"
                               class="group rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm transition hover:border-[#0F5132] hover:shadow-md">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">← Previous Lesson</p>
                                <p class="mt-1 text-sm font-bold text-slate-700 group-hover:text-[#0F5132]">{{ $prevLesson->title }}</p>
                            </a>
                        @else
                            <div></div>
                        @endif

                        @if($nextLesson)
                            <a href="{{ route('dashboard.lessons.show', ['course' => $course->slug, 'lesson' => $nextLesson->slug]) }}"
                               class="group rounded-2xl border border-emerald-100 bg-white p-5 text-right shadow-sm transition hover:border-[#0F5132] hover:shadow-md">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Next Lesson →</p>
                                <p class="mt-1 text-sm font-bold text-slate-700 group-hover:text-[#0F5132]">{{ $nextLesson->title }}</p>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Sidebar: Course Outline --}}
                <aside class="lg:col-span-1">
                    <div class="rounded-2xl border border-emerald-100 bg-white shadow-sm">
                        <div class="border-b border-emerald-100 p-5">
                            <h2 class="text-base font-extrabold text-slate-800">Course Content</h2>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                {{ $allLessons->count() }} lessons • {{ $completedLessonIds->filter(fn ($id) => $allLessons->contains('id', $id))->count() }} completed
                            </p>
                        </div>

                        <div class="max-h-[600px] overflow-y-auto p-3">
                            @forelse($course->modules as $module)
                                <div class="mb-2">
                                    <p class="px-3 pb-1 pt-3 text-xs font-extrabold uppercase tracking-wide text-[#0F5132]">{{ $module->title }}</p>
                                    <ul class="space-y-1">
                                        @foreach($module->lessons as $lessonItem)
                                            @php
                                                $isActive = $lessonItem->id === $lesson->id;
                                                $isDone = $completedLessonIds->contains($lessonItem->id);
                                            @endphp
                                            @php
                                                $isLocked = ! $lessonItem->is_free_preview && isset($enrollmentStatus) && $enrollmentStatus === 'pending';
                                            @endphp
                                            <li>
                                                @if($isLocked)
                                                    <div class="flex items-start gap-3 rounded-lg bg-slate-50 px-3 py-2.5 opacity-80" title="লেসনটি লকড — পেমেন্ট অনুমোদনের পর খুলবে">
                                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-slate-300 text-slate-400">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                        </span>
                                                        <span class="flex-1 text-sm font-semibold leading-snug text-slate-400">{{ $lessonItem->title }}</span>
                                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    </div>
                                                @else
                                                    <a href="{{ route('dashboard.lessons.show', ['course' => $course->slug, 'lesson' => $lessonItem->slug]) }}"
                                                       class="flex items-start gap-3 rounded-lg px-3 py-2.5 transition {{ $isActive ? 'bg-[#0F5132] text-white' : 'text-slate-600 hover:bg-[#F0FDF4] hover:text-[#0F5132]' }}">
                                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 text-[10px] font-bold {{ $isDone ? 'border-[#0F5132] bg-[#0F5132] text-white' : ($isActive ? 'border-white text-white' : 'border-slate-300 text-transparent') }}">
                                                            @if($isDone)
                                                                ✓
                                                            @else
                                                                {{ $loop->iteration }}
                                                            @endif
                                                        </span>
                                                        <span class="text-sm font-semibold leading-snug">{{ $lessonItem->title }}</span>
                                                    </a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @empty
                                <p class="p-4 text-sm text-slate-500">No lessons available yet.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Back to Dashboard --}}
                    <a href="{{ route('dashboard.index') }}" class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-[#0F5132]/30 bg-white px-4 py-3 text-sm font-bold text-[#0F5132] transition hover:bg-[#0F5132] hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Back to Dashboard
                    </a>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
