<x-app-layout
    :metaTitle="$metaTitle"
    :metaDescription="$metaDescription"
>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-14 text-white sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-4 text-sm text-emerald-200">
                <a href="{{ route('home') }}" class="transition hover:text-white">হোম</a>
                <span class="mx-2">/</span>
                <a href="{{ route('courses.index') }}" class="transition hover:text-white">কোর্সসমূহ</a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ $course->title }}</span>
            </nav>
            <div class="flex flex-wrap items-start gap-6">
                @if($course->thumbnail)
                    <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->title }}" class="h-44 w-full rounded-2xl object-cover shadow-lg sm:w-72">
                @endif
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-emerald-200">
                        @if($course->category)
                            <span class="rounded-full bg-white/10 px-3 py-1">{{ $course->category->name }}</span>
                        @endif
                        @if($course->hsk_level)
                            <span class="rounded-full bg-white/10 px-3 py-1">HSK {{ $course->hsk_level }}</span>
                        @endif
                        @if($course->duration_weeks)
                            <span class="rounded-full bg-white/10 px-3 py-1">{{ $course->duration_weeks }} সপ্তাহ</span>
                        @endif
                        @if($course->is_featured)
                            <span class="rounded-full bg-amber-400 px-3 py-1 font-bold text-amber-950">★ Featured</span>
                        @endif
                    </div>
                    <h1 class="mt-4 text-3xl font-extrabold leading-tight sm:text-4xl">{{ $course->title }}</h1>
                    <div class="mt-5 flex items-center gap-6">
                        <span class="text-3xl font-extrabold text-amber-300">
                            @if($course->price > 0)
                                ৳{{ number_format($course->price) }}
                            @else
                                <span class="rounded-full bg-emerald-300 px-4 py-1 text-lg font-bold text-emerald-950">ফ্রি</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(session('status'))
        <div class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Details + Enrollment -->
    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-2">
                <div class="prose prose-emerald max-w-none prose-headings:font-extrabold prose-headings:text-slate-900">
                    {!! $course->description !!}
                </div>

                @if($course->modules->isNotEmpty())
                    <div class="mt-12">
                        <h2 class="text-2xl font-extrabold text-slate-900">কোর্স মডিউলসমূহ</h2>
                        <div class="mt-6 space-y-4">
                            @foreach($course->modules as $module)
                                <div class="flex items-start gap-4 rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                                    <span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-[#0F5132] text-sm font-extrabold text-white">{{ $loop->iteration }}</span>
                                    <div>
                                        <h3 class="font-bold text-slate-900">{{ $module->title }}</h3>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Enrollment sidebar -->
            <div>
                <div class="sticky top-20 rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200">
                    <h2 class="text-lg font-extrabold text-slate-900">এনরোল করুন</h2>
                    <p class="mt-2 text-sm text-slate-500">আপনার পছন্দের পেমেন্ট পদ্ধতিতে পেমেন্ট করে কোর্সটি শুরু করুন।</p>

                    <div class="mt-6 space-y-3">
                        @auth
                            @php
                                $isEnrolled = auth()->user()->enrollments()->where('course_id', $course->id)->exists();
                            @endphp
                            @if($isEnrolled)
                                <a href="{{ route('dashboard.index') }}" class="block w-full rounded-full bg-[#0F5132] px-6 py-3 text-center text-sm font-bold text-white transition hover:bg-[#0d452c]">
                                    Go to Course
                                </a>
                                <p class="text-center text-xs text-slate-400">You are already enrolled in this course.</p>
                            @elseif($course->price > 0)
                                <a href="{{ route('checkout.show', $course->slug) }}" class="block w-full rounded-full bg-[#0F5132] px-6 py-3 text-center text-sm font-bold text-white transition hover:bg-[#0d452c]">
                                    Enroll Now – ৳{{ number_format($course->price) }}
                                </a>
                            @else
                                <form method="POST" action="{{ route('courses.enroll', $course->slug) }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="free">
                                    <input type="hidden" name="transaction_id" value="free-{{ time() }}">
                                    <input type="hidden" name="sender_number" value="01{{ rand(30000000, 99999999) }}">
                                    <button type="submit" class="block w-full rounded-full bg-[#0F5132] px-6 py-3 text-center text-sm font-bold text-white transition hover:bg-[#0d452c]">
                                        Enroll for Free
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login', ['redirect' => route('checkout.show', $course->slug)]) }}" class="block w-full rounded-full bg-[#0F5132] px-6 py-3 text-center text-sm font-bold text-white transition hover:bg-[#0d452c]">Login to Enroll</a>
                            <a href="{{ route('register') }}" class="block w-full rounded-full border-2 border-[#0F5132] px-6 py-3 text-center text-sm font-bold text-[#0F5132] transition hover:bg-emerald-50">Create Account</a>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-4 text-xs leading-relaxed text-slate-400">
                        এনরোল করার পর আমাদের টিম ২৪ ঘণ্টার মধ্যে আপনার সাথে যোগাযোগ করবে।
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($related->isNotEmpty())
        <section class="bg-[#F0FDF4] py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-extrabold text-slate-900">আরও কোর্স</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach($related as $item)
                        <a href="{{ route('courses.show', $item->slug) }}" class="group rounded-2xl bg-white p-6 shadow-sm ring-1 ring-emerald-100 transition hover:-translate-y-1 hover:shadow-lg">
                            @if($item->thumbnail)
                                <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->title }}" class="mb-4 h-32 w-full rounded-xl object-cover">
                            @endif
                            <h3 class="font-bold text-slate-900 transition group-hover:text-primary-800">{{ $item->title }}</h3>
                            <p class="mt-2 text-sm font-extrabold text-primary-800">
                                @if($item->price > 0)
                                    ৳{{ number_format($item->price) }}
                                @else
                                    <span class="text-sm font-bold text-emerald-600">ফ্রি</span>
                                @endif
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-app-layout>
