<x-app-layout
    :metaTitle="'China Scholarship Mentorship | Banglay Chinese'"
    :metaDescription="'Get step-by-step China scholarship (CSC) mentorship in Bengali — HSK prep, SOP guidance, university application and visa support for Bangladeshi students.'"
>
    <section class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-16 text-white sm:py-24">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-accent-600 px-4 py-1.5 text-sm font-bold text-white shadow-lg">
                🎓 চায়না স্কলারশিপ মেন্টরশিপ
            </span>
            <h1 class="mt-5 text-3xl font-extrabold font-display sm:text-5xl">চীনের বিশ্ববিদ্যালয়ে ভর্তির নিশ্চিত গাইডেন্স</h1>
            <p class="mt-5 max-w-2xl mx-auto text-lg leading-relaxed text-emerald-100">
                HSK প্রস্তুতি থেকে ভিসা পর্যন্ত — সম্পূর্ণ প্রক্রিয়ায় সাথে আছেন চীনে থাকা আমাদের মেন্টররা।
                যারা নিজেরা এই পথে সফল, তারাই আপনার গাইড।
            </p>
            <div class="mt-8 flex flex-col justify-center gap-4 sm:flex-row">
                <a href="https://wa.me/8618223249514?text={{ urlencode('আমি চায়না স্কলারশিপ নিয়ে গাইডেন্স চাই') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-accent-600 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-black/30 transition hover:bg-accent-500">
                    💬 ফ্রি কনসালটেশন নিন
                </a>
                <a href="#roadmap" class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-emerald-400/60 bg-white/10 px-8 py-4 text-lg font-semibold text-white backdrop-blur transition hover:border-emerald-300 hover:bg-white/20">
                    রোডম্যাপ দেখুন
                </a>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="border-b border-emerald-100 bg-[#F0FDF4]">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 py-10 text-center sm:px-6 md:grid-cols-4 lg:px-8">
            <div>
                <p class="text-3xl font-extrabold text-primary-800">৫০০+</p>
                <p class="mt-1 text-sm font-semibold text-slate-500">স্কলারশিপ সাকসেস</p>
            </div>
            <div>
                <p class="text-3xl font-extrabold text-primary-800">৯৫%+</p>
                <p class="mt-1 text-sm font-semibold text-slate-500">সাকসেস রেট</p>
            </div>
            <div>
                <p class="text-3xl font-extrabold text-primary-800">HSK ১–৪</p>
                <p class="mt-1 text-sm font-semibold text-slate-500">ফুল প্রিপারেশন</p>
            </div>
            <div>
                <p class="text-3xl font-extrabold text-primary-800">১০০%</p>
                <p class="mt-1 text-sm font-semibold text-slate-500">বাংলা সাপোর্ট</p>
            </div>
        </div>
    </section>

    {{-- 4-Step Roadmap --}}
    <section id="roadmap" class="scroll-mt-24 bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-800">কীভাবে কাজ করে</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900 font-display sm:text-4xl">৪ ধাপে চায়না স্কলারশিপ</h2>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                @php
                    $steps = [
                        ['num' => '১', 'icon' => '📚', 'title' => 'HSK প্রস্তুতি', 'desc' => 'HSK 1–4 প্রমাণিত রোডম্যাপে ভাষা দক্ষতা অর্জন — লাইভ ক্লাস, মক টেস্ট ও পার্সোনাল ফিডব্যাক।', 'color' => 'bg-emerald-100 text-emerald-800'],
                        ['num' => '২', 'icon' => '📝', 'title' => 'ডকুমেন্ট ও SOP', 'desc' => 'SOP, ট্রান্সক্রিপ্ট, রেকমেন্ডেশন লেটার — এক্সপার্ট রিভিউসহ সব ডকুমেন্ট প্রস্তুত।', 'color' => 'bg-sky-100 text-sky-800'],
                        ['num' => '৩', 'icon' => '🏫', 'title' => 'ইউনিভার্সিটি অ্যাপ্লিকেশন', 'desc' => 'প্রোফাইল অনুযায়ী সেরা ইউনিভার্সিটি সিলেকশন ও আবেদন টাইমলাইন ম্যানেজমেন্ট।', 'color' => 'bg-amber-100 text-amber-800'],
                        ['num' => '৪', 'icon' => '✈️', 'title' => 'ভিসা ও ফ্লাইট', 'desc' => 'ভিসা প্রসেসিং, প্রি-ডিপার্চার ব্রিফিং এবং চীনে পৌঁছানোর পরও সাপোর্ট।', 'color' => 'bg-red-100 text-red-700'],
                    ];
                @endphp

                @foreach($steps as $step)
                    <div class="relative rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="absolute -top-3 left-6 flex h-8 w-8 items-center justify-center rounded-full {{ $step['color'] }} text-sm font-extrabold ring-4 ring-white">
                            {{ $step['num'] }}
                        </div>
                        <div class="mt-4 text-4xl">{{ $step['icon'] }}</div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Scholarship Requirements --}}
    <section class="bg-[#F0FDF4] py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div>
                    <span class="text-sm font-bold uppercase tracking-widest text-primary-800">প্রয়োজনীয় যোগ্যতা</span>
                    <h2 class="mt-3 text-3xl font-extrabold text-slate-900 font-display sm:text-4xl">স্কলারশিপ পেতে যা যা লাগবে</h2>
                    <p class="mt-5 leading-relaxed text-slate-500">
                        CSC ও ইউনিভার্সিটি স্কলারশিপের জন্য সাধারণত HSK 3–4 সার্টিফিকেট, ভালো একাডেমিক রেজাল্ট
                        এবং শক্তিশালী আবেদন ডকুমেন্ট প্রয়োজন। আমাদের মেন্টররা স্টেপ বাই স্টেপ সব প্রস্তুতিতে গাইড করেন।
                    </p>
                    <a href="https://wa.me/8618223249514?text={{ urlencode('আমার প্রোফাইল অনুযায়ী স্কলারশিপ সম্ভাবনা জানতে চাই') }}" target="_blank" rel="noopener"
                       class="mt-8 inline-flex items-center gap-2 rounded-full bg-accent-600 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-accent-600/30 transition hover:bg-accent-700">
                        ফ্রি প্রোফাইল রিভিউ নিন
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
                <div class="space-y-4">
                    @php
                        $requirements = [
                            ['icon' => '🏆', 'title' => 'একাডেমিক ট্রান্সক্রিপ্ট', 'desc' => 'SSC/HSC বা ডিগ্রির ভালো রেজাল্ট — স্কলারশিপ আবেদনের মূল ভিত্তি।'],
                            ['icon' => '🇨🇳', 'title' => 'HSK 3–4 সার্টিফিকেট', 'desc' => 'চীনা ভাষার দক্ষতার প্রমাণ — আমাদের কোর্সে সম্পূর্ণ প্রস্তুতি।'],
                            ['icon' => '📄', 'title' => 'SOP ও রেকমেন্ডেশন', 'desc' => 'মানসম্মত স্টেটমেন্ট অব পারপাস ও রেকমেন্ডেশন লেটার।'],
                            ['icon' => '🛂', 'title' => 'পাসপোর্ট ও ভিসা ডকুমেন্ট', 'desc' => 'সঠিক সময়ে সব ডকুমেন্ট প্রস্তুত — ভিসা গাইডেন্সসহ।'],
                        ];
                    @endphp
                    @foreach($requirements as $req)
                        <div class="flex items-start gap-4 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-emerald-100">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-100 text-2xl">{{ $req['icon'] }}</div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $req['title'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $req['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Related Scholarship Courses --}}
    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-800">scলারশিপ প্রোগ্রাম</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900 font-display sm:text-4xl">স্কলারশিপের জন্য প্রস্তুত হওয়ার কোর্স</h2>
            </div>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse($courses->where('is_published', true)->take(3) as $course)
                    <div class="flex flex-col rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-primary-800 px-3 py-1 text-xs font-bold uppercase text-white">HSK {{ $course->hsk_level }}</span>
                            <span class="rounded-lg bg-accent-600 px-3 py-1 text-sm font-extrabold text-white">৳{{ number_format($course->price) }}</span>
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $course->title }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-500">{{ \Illuminate\Support\Str::limit($course->description, 100) }}</p>
                        @if($course->duration_weeks)
                            <p class="mt-3 text-sm font-semibold text-slate-400">⏱ {{ $course->duration_weeks }} সপ্তাহ</p>
                        @endif
                        <a href="https://wa.me/8618223249514?text={{ urlencode('আমি ' . $course->title . ' কোর্সে ভর্তি হতে চাই') }}"
                           target="_blank" rel="noopener"
                           class="mt-5 inline-flex items-center justify-center gap-2 rounded-full bg-primary-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-900">
                            এখনই ভর্তি হন
                        </a>
                    </div>
                @empty
                    <p class="col-span-full py-12 text-center text-slate-400">শীঘ্রই স্কলারশিপ প্রোগ্রাম কোর্স আসছে।</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-gradient-to-br from-primary-900 to-primary-950 px-4 py-16 text-center text-white sm:px-6">
        <div class="mx-auto max-w-3xl">
            <h2 class="text-3xl font-extrabold font-display sm:text-4xl">চায়না যাওয়ার স্বপ্ন আজই শুরু করুন</h2>
            <p class="mt-4 text-lg text-emerald-200">প্রতি সেশনের জায়গা সীমিত — ফ্রি কনসালটেশনের জন্য এখনই মেসেজ দিন।</p>
            <div class="mt-8 flex flex-col justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-accent-600 px-8 py-4 text-lg font-bold shadow-xl shadow-black/30 transition hover:bg-accent-500">
                    ফ্রি রেজিস্ট্রেশন
                </a>
                <a href="https://wa.me/8618223249514?text={{ urlencode('আমি স্কলারশিপ কনসালটেশন চাই') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-emerald-400/60 bg-white/10 px-8 py-4 text-lg font-semibold backdrop-blur transition hover:border-emerald-300 hover:bg-white/20">
                    💬 WhatsApp
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
