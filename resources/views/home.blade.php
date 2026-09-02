<x-app-layout
    :metaTitle="$metaTitle"
    :metaDescription="$metaDescription"
    :courseJsonLd="$courseJsonLd"
    :faqJsonLd="$faqJsonLd"
>
    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0F5132] via-[#0D5A38] to-[#052e16] text-white">
        {{-- Decorative circles --}}
        <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute top-40 -left-32 h-96 w-96 rounded-full bg-accent-500/10"></div>
        <div class="pointer-events-none absolute bottom-0 right-1/3 h-64 w-64 rounded-full bg-white/5"></div>

        <div class="relative mx-auto flex max-w-7xl flex-col items-center px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8">
            <span class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-sm font-medium backdrop-blur">
                <span class="h-2 w-2 animate-pulse rounded-full bg-accent-400"></span>
                নতুন ব্যাচ শুরু — লাইভ ক্লাসে জায়গা সীমিত
            </span>

            <h1 class="max-w-4xl text-3xl font-extrabold leading-tight tracking-tight font-display sm:text-5xl lg:text-[3.4rem]">
                {!! nl2br(e(\App\Services\SettingsService::get('hero_title', 'সরাসরি চীন থেকে এক্সক্লুসিভ মেন্টরশিপে স্কলারশিপ ও চাইনীজ ভাষা শিখুন'))) !!}
            </h1>

            <p class="mt-6 max-w-2xl text-base leading-relaxed text-emerald-100 sm:text-lg">
                {{ \App\Services\SettingsService::get('hero_subtitle', 'লাইভ ব্যাচ, HSK ১–৪ প্রস্তুতি, স্পিকিং মাস্টারি এবং CSC স্কলারশিপ সাপোর্ট — সব এক জায়গায়। চায়নার বিশ্ববিদ্যালয়ে ভর্তির স্বপ্ন পূরণ করুন বাংলায় শেখা চাইনিজে।') }}
            </p>

            <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row">
                <a href="{{ route('study-in-china') }}" class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent-600 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-black/30 transition hover:bg-accent-500 sm:w-auto">
                    🎓 Study in China
                    <svg class="h-5 w-5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="#courses" class="inline-flex w-full items-center justify-center gap-2 rounded-full border-2 border-emerald-400/60 bg-white/10 px-8 py-4 text-lg font-semibold text-white backdrop-blur transition hover:border-emerald-300 hover:bg-white/20 sm:w-auto">
                    কোর্সসমূহ দেখুন
                </a>
            </div>

            {{-- Social Proof Banner --}}
            <div class="mt-12 flex flex-col items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-6 py-4 backdrop-blur sm:flex-row sm:gap-6">
                <div class="flex -space-x-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-300 text-xs font-bold text-emerald-950 ring-2 ring-white/30">S</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-300 text-xs font-bold text-amber-950 ring-2 ring-white/30">R</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-300 text-xs font-bold text-sky-950 ring-2 ring-white/30">T</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-300 text-xs font-bold text-rose-950 ring-2 ring-white/30">+</span>
                </div>
                <p class="text-sm font-semibold text-emerald-50 sm:text-base">
                    <span class="font-extrabold text-accent-400">৫০০+ শিক্ষার্থী</span> চায়নায় স্কলারশিপ পেয়েছেন আমাদের গাইডেন্সে
                </p>
            </div>

            {{-- Trust Stats --}}
            <div class="mt-12 grid w-full max-w-3xl grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-2xl font-extrabold text-accent-400 sm:text-3xl">HSK ১–৪</p>
                    <p class="mt-1 text-xs text-emerald-100 sm:text-sm">কমপ্লিট ট্র্যাক</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-2xl font-extrabold text-accent-400 sm:text-3xl">৯৫%+</p>
                    <p class="mt-1 text-xs text-emerald-100 sm:text-sm">স্কলারশিপ সাকসেস</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-2xl font-extrabold text-accent-400 sm:text-3xl">১০০%</p>
                    <p class="mt-1 text-xs text-emerald-100 sm:text-sm">বাংলা সাপোর্ট</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-2xl font-extrabold text-accent-400 sm:text-3xl">লাইভ</p>
                    <p class="mt-1 text-xs text-emerald-100 sm:text-sm">স্মল-গ্রুপ ক্লাস</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== INTERACTIVE GOAL FILTER ===== --}}
    <section id="courses" class="scroll-mt-24 bg-[#F0FDF4] py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-800">লার্নিং প্রোগ্রাম</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900 font-display sm:text-4xl">আপনার গোল অনুযায়ী কোর্স বেছে নিন</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base text-slate-500 sm:text-lg">
                    স্কলারশিপ, ক্যারিয়ার, কিংবা বাচ্চাদের ফাউন্ডেশন — যেটাই হোক, সঠিক প্রোগ্রাম আছে আপনার জন্য।
                </p>
            </div>

            {{-- Filter Buttons --}}
            <div class="mb-10 flex flex-wrap items-center justify-center gap-3" id="course-filters">
                <button type="button" data-filter="all" class="filter-btn rounded-full bg-primary-800 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-primary-800/20 transition hover:bg-primary-900">
                    সব প্রোগ্রাম
                </button>
                <button type="button" data-filter="hsk" class="filter-btn rounded-full bg-white px-6 py-2.5 text-sm font-bold text-slate-600 ring-1 ring-slate-200 transition hover:ring-primary-400">
                    HSK প্রস্তুতি
                </button>
                <button type="button" data-filter="speaking" class="filter-btn rounded-full bg-white px-6 py-2.5 text-sm font-bold text-slate-600 ring-1 ring-slate-200 transition hover:ring-primary-400">
                    স্পিকিং মাস্টারি
                </button>
                <button type="button" data-filter="kids" class="filter-btn rounded-full bg-white px-6 py-2.5 text-sm font-bold text-slate-600 ring-1 ring-slate-200 transition hover:ring-primary-400">
                    কিডস প্রোগ্রাম
                </button>
                <button type="button" data-filter="scholarship" class="filter-btn rounded-full bg-white px-6 py-2.5 text-sm font-bold text-slate-600 ring-1 ring-slate-200 transition hover:ring-primary-400">
                    স্কলারশিপ
                </button>
            </div>

            {{-- Course Grid --}}
            @if($courses->isEmpty())
                <p class="py-16 text-center text-slate-400">কোনো কোর্স পাওয়া যায়নি।</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($courses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ===== CHINA SCHOLARSHIP ROADMAP ===== --}}
    <section id="scholarship-roadmap" class="scroll-mt-24 bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <span class="rounded-full bg-red-50 px-4 py-1.5 text-sm font-bold text-accent-600">🎓 চায়না স্কলারশিপ রোডম্যাপ</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900 font-display sm:text-4xl">৪ ধাপে চীনের বিশ্ববিদ্যালয়ে ভর্তি</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base text-slate-500 sm:text-lg">
                    Banglay Chinese-এর মেন্টরশিপে সম্পূর্ণ গাইডেন্স — শুরু থেকে ফ্লাইট পর্যন্ত।
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                @php
                    $steps = [
                        [
                            'num' => '১',
                            'icon' => '📚',
                            'title' => 'HSK প্রস্তুতি',
                            'desc' => 'HSK 1–4 প্রমাণিত রোডম্যাপে ভাষা দক্ষতা অর্জন করুন মক টেস্ট ও লাইভ ক্লাসে।',
                            'color' => 'bg-emerald-50 text-emerald-800',
                        ],
                        [
                            'num' => '২',
                            'icon' => '📝',
                            'title' => 'ডকুমেন্ট ও SOP',
                            'desc' => 'SOP, ট্রান্সক্রিপ্ট, রেকমেন্ডেশন — সব ডকুমেন্ট এক্সপার্ট রিভিউসহ প্রস্তুত করুন।',
                            'color' => 'bg-sky-50 text-sky-800',
                        ],
                        [
                            'num' => '৩',
                            'icon' => '🏫',
                            'title' => 'ইউনিভার্সিটি অ্যাপ্লিকেশন',
                            'desc' => 'আপনার প্রোফাইল অনুযায়ী সেরা ইউনিভার্সিটি নির্বাচন ও আবেদন টাইমলাইন।',
                            'color' => 'bg-amber-50 text-amber-800',
                        ],
                        [
                            'num' => '৪',
                            'icon' => '✈️',
                            'title' => 'ভিসা ও ফ্লাইট',
                            'desc' => 'ভিসা প্রসেসিং, প্রি-ডিপার্চার ব্রিফিং এবং চীনে পৌঁছানোর পর সাপোর্ট।',
                            'color' => 'bg-red-50 text-red-700',
                        ],
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

            <div class="mt-12 text-center">
                <a href="{{ route('study-in-china') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-accent-600 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-accent-600/30 transition hover:bg-accent-700">
                    স্কলারশিপ গাইডেন্স শুরু করুন
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ===== MEET YOUR MENTOR ===== --}}
    <section class="bg-gradient-to-br from-slate-50 to-emerald-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-emerald-100 bg-white shadow-xl shadow-emerald-900/5">
                <div class="flex flex-col items-center gap-8 p-8 sm:p-12 lg:flex-row lg:gap-12">
                    {{-- Portrait --}}
                    <div class="shrink-0">
                        <div class="h-28 w-28 overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 shadow-lg ring-4 ring-emerald-100 sm:h-36 sm:w-36">
                            <div class="flex h-full w-full items-center justify-center text-5xl font-extrabold text-white/90">ন</div>
                        </div>
                    </div>
                    {{-- Content --}}
                    <div class="flex-1 text-center lg:text-left">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">👋 আপনার মেন্টর</span>
                        <h3 class="mt-3 text-2xl font-extrabold text-slate-900 sm:text-3xl">Md. Naymur Rahman</h3>
                        <p class="mt-1 text-base font-semibold text-emerald-700">Founder & Lead Instructor</p>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
                            🇨🇳 ২০১৭ সাল থেকে চীনে পড়াশোনা করছি। স্ক্র্যাচ থেকে ফ্লুয়েন্ট — আমি নিজে এই জার্নি করেছি। এখন বাংলাদেশি শিক্ষার্থীদের সহজভাবে চাইনিজ শেখাচ্ছি।
                        </p>
                        <a href="{{ route('about') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                            সম্পূর্ণ গল্প পড়ুন
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FAQ ACCORDION ===== --}}
    <section id="faq" class="scroll-mt-24 bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-800">সাধারণ প্রশ্ন</span>
                <h2 class="mt-3 text-3xl font-extrabold text-slate-900 font-display sm:text-4xl">আপনার প্রশ্নের উত্তর</h2>
            </div>

            <div class="space-y-4" id="faq-accordion">
                @php
                    $faqs = [
                        [
                            'q' => 'বাংলাদেশি শিক্ষার্থীরা কীভাবে চায়না স্কলারশিপ পেতে পারে?',
                            'a' => 'চায়না স্কলারশিপ (CSC) পেতে HSK 3–4 লেভেলের সার্টিফিকেট, একাডেমিক ট্রান্সক্রিপ্ট, SOP এবং ইউনিভার্সিটি অ্যাপ্লিকেশন প্রয়োজন। আমাদের স্কলারশিপ মেন্টরশিপে ডকুমেন্ট প্রস্তুতি থেকে ইউনিভার্সিটি সিলেকশন পর্যন্ত সম্পূর্ণ গাইডেন্স দেওয়া হয়।',
                        ],
                        [
                            'q' => 'HSK কী এবং কত লেভেল পর্যন্ত শেখানো হয়?',
                            'a' => 'HSK হলো চীনা ভাষার আন্তর্জাতিক দক্ষতা পরীক্ষা। Banglay Chinese-এ HSK 1 থেকে HSK 4 পর্যন্ত সম্পূর্ণ প্রস্তুতি করা হয় — লাইভ ক্লাস, মক টেস্ট ও পার্সোনাল ফিডব্যাক সহ।',
                        ],
                        [
                            'q' => 'কোর্সের ফি কত এবং কী কী সুবিধা আছে?',
                            'a' => 'কোর্স অনুযায়ী ফি আলাদা — Fun Chinese for Kids ১২,০০০ টাকা, HSK Standard Track ১২,০০০ টাকা, Chinese Speaking Mastery ১৬,০০০ টাকা এবং HSK Intensive Program ২০,০০০ টাকা। Study in China সার্ভিস ২৫,০০০ টাকা থেকে শুরু। সব কোর্সে লাইভ স্মল-গ্রুপ ক্লাস, রেকর্ডিং, AI ওয়ার্ড ম্যাপ ও সার্টিফিকেট অন্তর্ভুক্ত।',
                        ],
                        [
                            'q' => 'কোর্স করতে কি আগে থেকে চাইনিজ জানতে হবে?',
                            'a' => 'না, কোনো পূর্ব অভিজ্ঞতা লাগবে না। HSK 1 থেকে শুরু হয় আমাদের সিলেবাস — সম্পূর্ণ বাংলায় ব্যাখ্যাসহ, তাই একদম শূন্য থেকেও শুরু করতে পারবেন।',
                        ],
                        [
                            'q' => 'লাইভ ক্লাসের সময়সূচী কেমন?',
                            'a' => 'লাইভ ক্লাস বাংলাদেশ সময় সন্ধ্যায় সপ্তাহে ৩ দিন হয়। প্রতিটি ক্লাসের রেকর্ডিং থাকে, তাই মিস করলেও পরে দেখে নিতে পারবেন।',
                        ],
                    ];
                @endphp

                @foreach($faqs as $index => $faq)
                    <div class="faq-item rounded-2xl border border-slate-200 bg-white transition hover:border-primary-300" x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" :aria-expanded="open.toString()">
                            <span class="text-base font-bold text-slate-900 sm:text-lg">{{ $faq['q'] }}</span>
                            <svg class="h-5 w-5 shrink-0 text-primary-800 transition-transform duration-300" :class="open ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-5 pb-5">
                            <p class="text-sm leading-relaxed text-slate-500 sm:text-base">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @php $waNumber = \App\Services\SettingsService::get('whatsapp_number', '8618223249514'); @endphp
            <p class="mt-8 text-center text-sm text-slate-500">
                আরও প্রশ্ন আছে? <a href="{{ route('contact') }}" class="font-bold text-primary-800 hover:underline">কন্টাক্ট করুন</a> অথবা
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="font-bold text-[#25D366] hover:underline">WhatsApp</a>-এ মেসেজ দিন।
            </p>
        </div>
    </section>

    {{-- ===== FINAL CTA ===== --}}
    <section class="bg-gradient-to-br from-primary-900 to-primary-950 px-4 py-16 text-white sm:px-6">
        <div class="mx-auto flex max-w-4xl flex-col items-center text-center">
            <span class="mb-4 inline-flex items-center gap-2 rounded-full border border-accent-500/40 bg-accent-500/10 px-4 py-1.5 text-sm font-semibold text-accent-400">
                🔥 নতুন ব্যাচে ভর্তি চলছে
            </span>
            <h2 class="text-3xl font-extrabold font-display sm:text-4xl">আপনার চায়না ড্রিম শুরু হোক আজই</h2>
            <p class="mt-4 max-w-2xl text-lg text-emerald-200">
                জায়গা সীমিত — আজই রেজিস্ট্রেশন করুন এবং প্রথম লাইভ ক্লাসে ফ্রি অংশ নিন।
            </p>
            <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-accent-600 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-black/30 transition hover:bg-accent-500">
                    ফ্রি রেজিস্ট্রেশন করুন
                </a>
                <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('আমি কোর্স সম্পর্কে জানতে চাই') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-emerald-400/60 bg-white/10 px-8 py-4 text-lg font-semibold text-white backdrop-blur transition hover:border-emerald-300 hover:bg-white/20">
                    💬 WhatsApp-এ কথা বলুন
                </a>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // ---- Interactive Course Filter ----
                const filterButtons = document.querySelectorAll('#course-filters .filter-btn');
                const courseCards = document.querySelectorAll('.course-card');

                function setActiveFilter(activeBtn) {
                    filterButtons.forEach(btn => {
                        const isActive = btn === activeBtn;
                        btn.classList.toggle('bg-primary-800', isActive);
                        btn.classList.toggle('text-white', isActive);
                        btn.classList.toggle('shadow-md', isActive);
                        btn.classList.toggle('shadow-primary-800/20', isActive);
                        btn.classList.toggle('bg-white', !isActive);
                        btn.classList.toggle('text-slate-600', !isActive);
                        btn.classList.toggle('ring-1', !isActive);
                        btn.classList.toggle('ring-slate-200', !isActive);
                    });
                }

                filterButtons.forEach(btn => {
                    btn.addEventListener('click', function () {
                        const filter = this.dataset.filter;
                        setActiveFilter(this);

                        courseCards.forEach(card => {
                            if (filter === 'all' || card.dataset.filter === filter) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    });
                });

            });
        </script>
    @endpush
</x-app-layout>
