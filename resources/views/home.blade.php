<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Banglay Chinese — বাংলা থেকে চীনা ভাষা শিখুন। HSK প্রস্তুতি, স্পিকিং কোর্স, কিডস প্রোগ্রাম ও স্কলারশিপ গাইডেন্স।">
    <title>Banglay Chinese | বাংলা থেকে চীনা ভাষা শিখুন</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    {{-- ===== NAVBAR ===== --}}
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md shadow-sm">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('assets/logo.png') }}" alt="Banglay Chinese Logo" class="h-10 w-auto">
                <span class="text-xl font-extrabold tracking-tight text-slate-900">
                    Banglay <span class="text-primary-600">Chinese</span>
                </span>
            </a>
            <div class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="#courses" class="transition hover:text-primary-600">কোর্সসমূহ</a>
                <a href="#why-us" class="transition hover:text-primary-600">কেন আমরা</a>
                <a href="#scholarship" class="transition hover:text-primary-600">Scholarship Mentorship</a>
            </div>
            <a href="#courses" class="rounded-full bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700">
                এখনই শুরু করুন
            </a>
        </nav>
    </header>

    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-700 via-primary-800 to-red-950 text-white">
        {{-- Decorative circles --}}
        <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute top-40 -left-32 h-96 w-96 rounded-full bg-gold-500/10"></div>
        <div class="pointer-events-none absolute bottom-0 right-1/3 h-64 w-64 rounded-full bg-white/5"></div>

        <div class="relative mx-auto flex max-w-7xl flex-col items-center px-4 py-24 text-center sm:px-6 sm:py-32 lg:px-8">
            <span class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-sm font-medium backdrop-blur">
                <span class="h-2 w-2 animate-pulse rounded-full bg-gold-400"></span>
                বাংলাদেশের জন্য অনলাইন চাইনিজ লার্নিং
            </span>
            <h1 class="max-w-4xl text-4xl font-extrabold leading-tight tracking-tight font-display sm:text-5xl lg:text-6xl">
                সহজ উপায়ে <span class="text-gold-400">বাংলা থেকে</span><br>
                চীনা ভাষা শিখুন
            </h1>
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-red-100 sm:text-xl">
                HSK ১ থেকে ৪ পর্যন্ত প্রমাণিত রোডম্যাপ, লাইভ স্মল-গ্রুপ ক্লাস, AI ওয়ার্ড ম্যাপ
                এবং চায়না স্কলারশিপ অ্যাডমিশন গাইডেন্স — সব এক জায়গায়।
            </p>
            <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row">
                <a href="#courses" class="group inline-flex items-center gap-2 rounded-full bg-gold-500 px-8 py-4 text-lg font-bold text-slate-900 shadow-xl shadow-black/20 transition hover:bg-gold-400">
                    কোার্সসমূহ দেখুন
                    <svg class="h-5 w-5 transition group-hover:translate-y-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                </a>
                <a href="#scholarship" class="inline-flex items-center gap-2 rounded-full border-2 border-white/30 bg-white/10 px-8 py-4 text-lg font-semibold text-white backdrop-blur transition hover:border-white/60 hover:bg-white/20">
                    🎓 Scholarship Mentorship
                </a>
            </div>
            <div class="mt-14 grid w-full max-w-3xl grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-3xl font-extrabold text-gold-400">{{ $courses->count() }}+</p>
                    <p class="mt-1 text-sm text-red-100">লাইভ প্রোগ্রাম</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-3xl font-extrabold text-gold-400">HSK ১–৪</p>
                    <p class="mt-1 text-sm text-red-100">কমপ্লিট ট্র্যাক</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-3xl font-extrabold text-gold-400">৮–১৩</p>
                    <p class="mt-1 text-sm text-red-100">কিডস প্রোগ্রাম</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-3xl font-extrabold text-gold-400">১০০%</p>
                    <p class="mt-1 text-sm text-red-100">বাংলা সাপোর্ট</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== COURSE GRID ===== --}}
    <section id="courses" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <span class="text-sm font-bold uppercase tracking-widest text-primary-600">লার্নিং প্রোগ্রাম</span>
            <h2 class="mt-3 text-3xl font-extrabold text-slate-900 font-display sm:text-4xl">আমাদের কোর্সসমূহ</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-500">
                আপনার গোল অনুযায়ী বেছে নিন — স্কলারশিপ, ক্যারিয়ার, কিংবা বাচ্চাদের ফাউন্ডেশন।
            </p>
        </div>

        @if($courses->isEmpty())
            <p class="py-16 text-center text-slate-400">কোনো কোর্স পাওয়া যায়নি।</p>
        @else
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($courses as $course)
                    @php
                        $badgeSlug = $course->slug;
                        $badgeLabel = match ($badgeSlug) {
                            'fun-chinese-for-kids' => 'Kids Program',
                            'chinese-speaking-mastery' => 'Speaking & Fluency',
                            'hsk-intensive-program' => 'Scholarship Guidance',
                            default => 'HSK Preparation',
                        };
                        $badgeColors = match ($badgeSlug) {
                            'fun-chinese-for-kids' => 'bg-emerald-100 text-emerald-700',
                            'chinese-speaking-mastery' => 'bg-sky-100 text-sky-700',
                            'hsk-intensive-program' => 'bg-gold-500 text-slate-900',
                            default => 'bg-primary-100 text-primary-700',
                        };
                    @endphp
                    <article class="group flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200 transition duration-300 hover:-translate-y-1.5 hover:shadow-xl">
                        <div class="relative h-36 bg-gradient-to-br from-primary-600 to-red-900 p-6">
                            <div class="pointer-events-none absolute -bottom-8 -right-8 h-28 w-28 rounded-full bg-white/10"></div>
                            <div class="flex items-start justify-between">
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white backdrop-blur">{{ $badgeLabel }}</span>
                            </div>
                            <div class="mt-8 flex items-center gap-2">
                                <span class="rounded-lg bg-white/20 px-2.5 py-1 text-xs font-bold text-white">HSK {{ $course->hsk_level }}</span>
                                <span class="rounded-lg bg-gold-500 px-2.5 py-1 text-xs font-extrabold text-slate-900">BDT {{ number_format($course->price) }}</span>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <a href="#courses" class="text-xl font-bold text-slate-900 transition group-hover:text-primary-600">
                                {{ $course->title }}
                            </a>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-500">
                                {{ \Illuminate\Support\Str::limit($course->description, 110) }}
                            </p>
                            <ul class="mt-4 space-y-2 text-sm text-slate-600">
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    লাইভ ছোট গ্রুপ ক্লাস
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    নিয়মিত মক টেস্ট ও ফিডব্যাক
                                </li>
                            </ul>
                            <a href="#scholarship" class="mt-6 inline-flex items-center justify-center gap-2 rounded-full bg-primary-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700">
                                Enroll Now
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ===== WHY US ===== --}}
    <section id="why-us" class="scroll-mt-24 bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-600">কেন Banglay Chinese</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900 font-display sm:text-4xl">বাংলা থেকে চীনা ভাষা শেখার সেরা প্ল্যাটফর্ম</h2>
            </div>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-3xl border border-slate-100 bg-slate-50 p-6 text-center transition hover:border-primary-200 hover:bg-white hover:shadow-md">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-2xl">🗣️</div>
                    <h3 class="text-lg font-bold text-slate-900">বাংলায় ব্যাখ্যা</h3>
                    <p class="mt-2 text-sm text-slate-500">জটিল ব্যাকরণ থেকে শুরু করে উচ্চারণ — সব কিছু মাতৃভাষায় সহজভাবে শেখানো হয়।</p>
                </div>
                <div class="rounded-3xl border border-slate-100 bg-slate-50 p-6 text-center transition hover:border-primary-200 hover:bg-white hover:shadow-md">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-2xl">🤖</div>
                    <h3 class="text-lg font-bold text-slate-900">AI ওয়ার্ড ম্যাপ</h3>
                    <p class="mt-2 text-sm text-slate-500">স্মার্ট ওয়ার্ড ম্যাপ দ্রুত ভোকাবুলারি মনে রাখতে এবং রিভিশন করতে সাহায্য করে।</p>
                </div>
                <div class="rounded-3xl border border-slate-100 bg-slate-50 p-6 text-center transition hover:border-primary-200 hover:bg-white hover:shadow-md">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-2xl">🎯</div>
                    <h3 class="text-lg font-bold text-slate-900">স্কলারশিপ গাইডেন্স</h3>
                    <p class="mt-2 text-sm text-slate-500">চায়না সরকারের স্কলারশিপ আবেদন, ডকুমেন্ট আর ডেডলাইন ম্যানেজমেন্টে হাতে-কলমে সাপোর্ট।</p>
                </div>
                <div class="rounded-3xl border border-slate-100 bg-slate-50 p-6 text-center transition hover:border-primary-200 hover:bg-white hover:shadow-md">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-2xl">⏱️</div>
                    <h3 class="text-lg font-bold text-slate-900">ফ্লেক্সিবল প্রোগ্রাম</h3>
                    <p class="mt-2 text-sm text-slate-500">কিডস, প্রফেশনাল আর স্কলারশিপ আপ্লিক্যান্ট — সবার জন্য আলাদা আলাদা রোডম্যাপ।</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SCHOLARSHIP CTA ===== --}}
    <section id="scholarship" class="scroll-mt-24 bg-gradient-to-br from-slate-900 to-slate-800 px-4 py-20 text-white sm:px-6">
        <div class="mx-auto flex max-w-4xl flex-col items-center text-center">
            <span class="mb-4 inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-4 py-1.5 text-sm font-semibold text-gold-400">
                🎓 চায়না স্কলারশিপ ২০২৬
            </span>
            <h2 class="text-3xl font-extrabold font-display sm:text-4xl">স্কলারশিপ ডেডলাইনের প্রস্তুতি নিন আজই</h2>
            <p class="mt-4 max-w-2xl text-lg text-slate-300">
                HSK ইনটেনসিভ প্রোগ্রামে ভর্তি হয়ে আসন্ন স্কলারশিপ ডেডলাইনের আগেই HSK ১–৪ সম্পন্ন করুন।
            </p>
            <a href="#courses" class="mt-8 inline-flex items-center gap-2 rounded-full bg-gold-500 px-8 py-4 text-lg font-bold text-slate-900 shadow-xl shadow-black/30 transition hover:bg-gold-400">
                HSK Intensive Program
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer class="bg-slate-950 text-slate-400">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div>
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img src="{{ asset('assets/logo.png') }}" alt="Banglay Chinese Logo" class="h-9 w-auto">
                    <span class="text-lg font-extrabold text-white">Banglay <span class="text-primary-500">Chinese</span></span>
                </a>
                <p class="mt-4 text-sm leading-relaxed">
                    বাংলা ভাষাভাষীদের জন্য চীনা ভাষা শিক্ষা, HSK প্রস্তুতি ও স্কলারশিপ গাইডেন্স — সম্পূর্ণ অনলাইনে।
                </p>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white">Quick Links</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#courses" class="transition hover:text-gold-400">কোর্সসমূহ</a></li>
                    <li><a href="#why-us" class="transition hover:text-gold-400">কেন আমরা</a></li>
                    <li><a href="#scholarship" class="transition hover:text-gold-400">Scholarship Mentorship</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white">HSK Preparation</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#courses" class="transition hover:text-gold-400">HSK Standard Track</a></li>
                    <li><a href="#courses" class="transition hover:text-gold-400">HSK Intensive Program</a></li>
                    <li><a href="#courses" class="transition hover:text-gold-400">Chinese Speaking Mastery</a></li>
                    <li><a href="#courses" class="transition hover:text-gold-400">Fun Chinese for Kids</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white">Contact</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        hello@banglaychinese.com
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        +880 1XXX-XXXXXX
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/></svg>
                        ঢাকা, বাংলাদেশ
                    </li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
            © {{ date('Y') }} Banglay Chinese. সর্বস্বত্ব সংরক্ষিত।
        </div>
    </footer>

</body>
</html>
