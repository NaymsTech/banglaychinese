@php
    use App\Services\SettingsService;
    $waNumber = SettingsService::get('whatsapp_number', '8618223249514');
    $waLink = 'https://wa.me/' . $waNumber;

    // Presentation metadata (badges + Bangla positioning) keyed by canonical slug.
    // The actual service name / description / features / price / duration / CTA
    // come exclusively from the `services` table (Service model).
    $serviceMeta = [
        'guided-application' => [
            'badges'  => ['Self-Managed'],
            'tagline' => 'নিজের application নিজে করতে চান, শুধু expert direction ও strategy support দরকার।',
        ],
        'full-application-service' => [
            'badges'  => ['Complete Support'],
            'tagline' => 'Application থেকে pre-departure পর্যন্ত পুরো process-এ আমাদের team-এর hands-on support।',
        ],
        'elite-success-program' => [
            'badges'  => ['1-Year Mentorship', 'Most Comprehensive'],
            'tagline' => 'Admission + Chinese Language + Academic & Career Guidance — একসাথে 1-Year structured mentorship।',
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="metaTitle">Study in China from Bangladesh | Scholarship & Application Support | Banglay Chinese</x-slot>
    <x-slot name="metaDescription">চীনে পড়াশোনা — University selection, scholarship guidance, application support, Chinese language training এবং 1-Year Mentorship। বাংলাদেশি শিক্ষার্থীদের জন্য Banglay Chinese-এর সাথে China journey শুরু করুন।</x-slot>
    <x-slot name="canonicalUrl">{{ route('study-in-china') }}</x-slot>

    @push('meta')
        <meta property="og:title" content="Study in China from Bangladesh | Scholarship & Application Support | Banglay Chinese">
        <meta property="og:description" content="University selection, scholarship guidance, application support, Chinese language training এবং 1-Year Mentorship — বাংলাদেশি শিক্ষার্থীদের জন্য।">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="Banglay Chinese">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Study in China from Bangladesh | Banglay Chinese">
        <meta name="twitter:description" content="University selection, scholarship guidance, application support এবং 1-Year Mentorship।">
        <script type="application/ld+json">{"@@context":"https://schema.org","@@type":"Service","name":"Study in China Consultation Services","provider":{"@@type":"Organization","name":"Banglay Chinese","url":"{{ url('/') }}"},"description":"University selection, scholarship guidance, application support এবং long-term mentorship — বাংলাদেশি শিক্ষার্থীদের জন্য China study guidance।","serviceType":"Education Consulting","areaServed":"Bangladesh"}</script>
    @endpush

    <div class="bg-surface font-sans text-text">
        {{-- ========================================== SECTION 1: HERO ========================================== --}}
        <section class="relative overflow-hidden bg-white">
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-primary-50 blur-3xl"></div>
                <div class="absolute top-1/2 -left-32 w-80 h-80 rounded-full bg-accent-50 blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-16 lg:pt-20 lg:pb-24">
                <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    <div class="lg:col-span-7">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-50 text-primary-700 border border-primary-100 text-sm font-semibold">🇨🇳 Study in China Guidance for Bangladeshi Students</span>

                        <h1 class="mt-6 font-bangla text-4xl sm:text-5xl lg:text-[3.4rem] font-bold leading-tight text-slate-900">চীনে পড়াশোনার স্বপ্নকে একটি পরিষ্কার পরিকল্পনায় পরিণত করুন</h1>

                        <p class="mt-5 text-lg sm:text-xl text-slate-600 leading-relaxed max-w-2xl font-bangla">University selection, scholarship guidance, application support, Chinese language training এবং long-term mentorship — Banglay Chinese-এর সাথে আপনার China journey শুরু করুন।</p>

                        <p class="mt-5 inline-flex flex-wrap items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-alt border border-border text-sm font-semibold text-slate-700"><span class="text-accent-600">◆</span> Admission Support + Chinese Language + 1-Year Mentorship</p>

                        <div class="mt-8 flex flex-col sm:flex-row gap-4">
                            <a href="#consultation" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary-600 text-white font-bold rounded-xl shadow-sm hover:bg-primary-700 transition-colors text-lg">Get Eligibility Review
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></a>
                            <a href="#services" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-slate-200 text-slate-700 font-semibold rounded-xl hover:border-primary-300 hover:text-primary-700 transition-colors text-lg">Explore Study in China</a>
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        <div class="relative bg-gradient-to-br from-primary-50 to-surface border border-primary-100 rounded-2xl p-8 shadow-sm">
                            <span class="inline-block px-3 py-1 rounded-full bg-white text-primary-700 text-sm font-bold shadow-sm">আপনার সাথে যা থাকছে</span>
                            <ul class="mt-6 space-y-4">
                                @foreach([
                                    ['title' => 'University & Program Guidance', 'sub' => 'সঠিক university ও program বেছে নেওয়ার সাহায্য'],
                                    ['title' => 'Scholarship Support', 'sub' => 'Eligibility বুঝে scholarship-এর দিকনির্দেশনা'],
                                    ['title' => 'Application Support', 'sub' => 'সম্পূর্ণ process-এ human-led guidance'],
                                    ['title' => 'Chinese Language Training', 'sub' => 'বাংলায় Chinese শেখার সুযোগ'],
                                    ['title' => '1-Year Mentorship', 'sub' => 'দীর্ঘমেয়াদি structured support'],
                                ] as $item)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white text-sm">✓</span>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $item['title'] }}</p>
                                            <p class="text-sm text-slate-500">{{ $item['sub'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 2: TRUST / VALUE STRIP ========================================== --}}
        <section class="bg-white border-t border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach([
                        ['title' => 'University Guidance', 'icon' => '🎓', 'desc' => 'নিজের profile অনুযায়ী university ও program বেছে নেওয়ার দিকনির্দেশনা'],
                        ['title' => 'Scholarship Support', 'icon' => '🏅', 'desc' => 'Eligibility ও types বুঝে scholarship-এর সম্ভাব্য দিক'],
                        ['title' => 'Application Assistance', 'icon' => '📝', 'desc' => 'Documents ও application process-এ human-led help'],
                        ['title' => '1-Year Mentorship', 'icon' => '🗓️', 'desc' => 'Elite students-দের জন্য দীর্ঘমেয়াদি structured support'],
                    ] as $value)
                        <div class="rounded-xl border border-border bg-surface-alt p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-white border border-border text-2xl">{{ $value['icon'] }}</span>
                                <h3 class="font-display font-bold text-slate-900">{{ $value['title'] }}</h3>
                            </div>
                            <p class="mt-3 text-sm text-slate-600 leading-relaxed font-bangla">{{ $value['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 3: WHY STUDY IN CHINA ========================================== --}}
        <section class="py-16 lg:py-20 bg-surface-alt border-y border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Why China?</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">কেন বাংলাদেশি শিক্ষার্থীরা China বেছে নেয়?</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">China পড়াশোনার জন্য একটি জনপ্রিয় গন্তব্য। তবে প্রতিটি student-এর জন্য সব option উপযুক্ত নয় — যা আপনার জন্য সঠিক, তা বুঝতে প্রোফাইলভিত্তিক review প্রয়োজন।</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach([
                        ['icon' => '📚', 'title' => 'বিভিন্ন Program', 'desc' => 'Bachelor’s, Master’s, PhD, Chinese Language — অসংখ্য বিষয়ে পড়ার সুযোগ।'],
                        ['icon' => '🏅', 'title' => 'Scholarship সুযোগ', 'desc' => 'University, government ও provincial level-এ নানা scholarship-এর সম্ভাবনা।'],
                        ['icon' => '🌏', 'title' => 'আন্তর্জাতিক পরিবেশ', 'desc' => 'বিশ্বের বিভিন্ন দেশের শিক্ষার্থীদের সাথে পড়াশোনার সুযোগ।'],
                        ['icon' => '🔬', 'title' => 'প্রযুক্তি ও গবেষণা', 'desc' => 'প্রযুক্তি ও গবেষণা-কেন্দ্রিক program-এ উন্নত সুযোগ।'],
                        ['icon' => '🗣️', 'title' => 'Chinese Language', 'desc' => 'China-তে পড়তে গেলে ভাষা শেখার বাস্তব পরিবেশ পাওয়া যায়।'],
                        ['icon' => '🏫', 'title' => 'নানা University Option', 'desc' => 'শহর, program ও budget অনুযায়ী অনেক university বেছে নেওয়ার সুযোগ।'],
                    ] as $why)
                        <div class="rounded-xl border border-border bg-white p-6">
                            <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary-50 text-2xl">{{ $why['icon'] }}</span>
                            <h3 class="mt-4 font-display font-bold text-slate-900">{{ $why['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $why['desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 text-center">
                    <a href="#consultation" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-primary-600 text-primary-700 font-bold rounded-xl hover:bg-primary-50 transition-colors">Check Your Eligibility</a>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 4: WHAT CAN YOU STUDY ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Programs</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">China-তে কী কী পড়া যায়?</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">এগুলো China-তে পড়ার program category — Banglay Chinese-এর নিয়মিত Chinese language course নয়। কোন category আপনার জন্য সম্ভব, তা program ও university-র উপর নির্ভর করে।</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach([
                        ['deg' => 'Bachelor’s', 'for' => 'HSC / equivalent শিক্ষার্থীদের জন্য', 'desc' => 'Undergraduate study plan করছেন এমন students-দের জন্য।'],
                        ['deg' => 'Master’s', 'for' => 'Graduate শিক্ষার্থীদের জন্য', 'desc' => 'Advanced study করতে চাওয়া graduates-দের জন্য।'],
                        ['deg' => 'PhD', 'for' => 'Research-focused applicants-দের জন্য', 'desc' => 'গবেষণায় আগ্রহী শিক্ষার্থীদের জন্য।'],
                        ['deg' => 'Chinese Language Programs', 'for' => 'ভাষা শিখতে আগ্রহীদের জন্য', 'desc' => 'China-তে Chinese language study করতে চাওয়া students-দের জন্য।'],
                        ['deg' => 'Diploma / Other Programs', 'for' => 'নির্দিষ্ট intake-এ উপযুক্ত applicants-দের জন্য', 'desc' => 'Institution ও intake অনুযায়ী কিছু program-এর ক্ষেত্রে প্রযোজ্য।'],
                    ] as $prog)
                        <div class="rounded-xl border border-border bg-surface-alt p-6">
                            <span class="inline-block px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-bold uppercase tracking-wide">{{ $prog['deg'] }}</span>
                            <p class="mt-4 text-sm font-semibold text-slate-800">{{ $prog['for'] }}</p>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $prog['desc'] }}</p>
                        </div>
                    @endforeach

                    <div class="rounded-xl border border-primary-200 bg-primary-50 p-6 flex flex-col justify-center">
                        <p class="text-sm text-primary-800 leading-relaxed font-bangla">কোন program আপনার জন্য সঠিক — এটা university-র requirements, আপনার background ও goals-এর উপর নির্ভর করে।</p>
                        <a href="#consultation" class="mt-4 inline-flex items-center gap-2 text-primary-700 font-bold text-sm">Check Your Eligibility →</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 5: SCHOLARSHIPS ========================================== --}}
        <section class="py-16 lg:py-20 bg-surface-alt border-y border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Scholarship</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">Scholarship-এর সম্ভাব্য সুযোগ</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">China-তে পড়তে আগ্রহী students-দের জন্য নানা ধরনের scholarship-এর সুযোগ থাকতে পারে। কোনটি আপনার জন্য প্রযোজ্য, তা university, program ও intake-এর উপর নির্ভর করে।</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 gap-5">
                    @foreach([
                        ['title' => 'University Scholarships', 'desc' => 'অনেক university নিজস্ব scholarship-এর সুযোগ দেয়।'],
                        ['title' => 'Chinese Government Scholarship', 'desc' => 'বিভিন্ন level-এ government-funded scholarship-এর সম্ভাবনা।'],
                        ['title' => 'Provincial / Institutional', 'desc' => 'কিছু region বা institution নিজস্ব funding-এর সুযোগ দেয়।'],
                        ['title' => 'University-Specific Funding', 'desc' => 'নির্দিষ্ট program বা university-এর নিজস্ব সুযোগ থাকতে পারে।'],
                    ] as $s)
                        <div class="rounded-xl border border-border bg-white p-6">
                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-accent-50 text-xl">🏅</span>
                            <h3 class="mt-4 font-display font-bold text-slate-900">{{ $s['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $s['desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 leading-relaxed font-bangla">
                    <strong>মনে রাখবেন:</strong> Scholarship-এর availability, eligibility ও funding প্রতিটি university, program ও intake-এ ভিন্ন হয়। কোনো scholarship guaranteed নয় — যা সম্ভব, তা review করে জানানো হয়।
                </p>

                <div class="mt-8 text-center">
                    <a href="#consultation" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-primary-600 text-primary-700 font-bold rounded-xl hover:bg-primary-50 transition-colors">Check Scholarship Eligibility</a>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 6: WHO CAN APPLY ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Eligibility</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">কে আবেদন করতে পারে?</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">নিচের category-র students সাধারণত China study-এর কথা ভাবতে পারেন। তবে কে যোগ্য, তা university ও program-এর requirements-এর উপর নির্ভর করে।</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach([
                        ['t' => 'HSC / Equivalent Students', 'd' => 'Bachelor’s program-এ আবেদন করতে চান এমন students।'],
                        ['t' => 'Bachelor’s Graduates', 'd' => 'Master’s-এর জন্য আবেদন করতে চান এমন graduates।'],
                        ['t' => 'Master’s Applicants', 'd' => 'Advanced study অথবা PhD-এর প্রস্তুতি নিচ্ছেন।'],
                        ['t' => 'PhD Applicants', 'd' => 'Research-focused PhD program-এ আগ্রহী students।'],
                        ['t' => 'Chinese Language Applicants', 'd' => 'ভাষা program-এ পড়তে চান এমন students।'],
                    ] as $el)
                        <div class="rounded-xl border border-border bg-surface-alt p-5">
                            <h3 class="font-display font-bold text-slate-900">{{ $el['t'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $el['d'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 rounded-xl border border-border bg-surface-alt p-6">
                    <h3 class="font-display font-bold text-slate-900">Eligibility যার উপর নির্ভর করে</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(['Academic background','Academic results','Desired program','Language requirements','University requirements','Intake','Documentation','Financial considerations'] as $f)
                            <span class="rounded-full bg-white border border-border px-3 py-1.5 text-sm text-slate-700">{{ $f }}</span>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-slate-600 font-bangla"><strong>Eligibility university ও program অনুযায়ী ভিন্ন হয়।</strong></p>
                    <a href="#consultation" class="mt-4 inline-flex items-center gap-2 text-primary-700 font-bold text-sm">Get Personal Eligibility Review →</a>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 7: APPLICATION ROADMAP ========================================== --}}
        <section class="py-16 lg:py-20 bg-surface-alt border-y border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Roadmap</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">China Application Journey-র ধাপগুলো</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">নিচের প্রক্রিয়াটি একটি সাধারণ দিকনির্দেশনা। প্রতিটি ধাপ university, program ও intake অনুযায়ী ভিন্ন হতে পারে — কোনো ধাপ বা ফলাফল guaranteed নয়।</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach([
                        ['01','Profile Evaluation','আপনার academic background ও goals বুঝে নেওয়া।'],
                        ['02','Eligibility Assessment','কোন program-এ আপনি eligible, তা যাচাই করা।'],
                        ['03','Program & University Selection','আপনার জন্য উপযুক্ত option চিহ্নিত করা।'],
                        ['04','Document Preparation','প্রয়োজনীয় documents তৈরি ও review করা।'],
                        ['05','Application Submission','University requirements অনুযায়ী application জমা দেওয়া।'],
                        ['06','Admission / Scholarship Process','পরবর্তী ধাপ ও requirements-এ সাড়া দেওয়া।'],
                        ['07','Visa Preparation','China-তে যাওয়ার প্রস্তুতির দিকনির্দেশনা।'],
                        ['08','Pre-departure & Next Steps','চীনে যাত্রা শুরুর প্রস্তুতি নেওয়া।'],
                    ] as $step)
                        <div class="rounded-xl border border-border bg-white p-5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-600 text-white font-display font-bold">{{ $step[0] }}</span>
                            <h3 class="mt-4 font-display font-bold text-slate-900">{{ $step[1] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $step[2] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 8: WHY BANGLAY CHINESE ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Why Banglay Chinese?</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">আমরা শুধু application-এ সাহায্য করি না — আমরা China-র জন্য প্রস্তুত করি</h2>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 gap-5">
                    @foreach([
                        ['01','Bangla-first Guidance','জটিল China-study তথ্য সহজ ও স্পষ্ট বাংলায় বোঝানো হয়।'],
                        ['02','Human Support','আপনার application ও profile আমাদের টিম নিজ হাতে review করে।'],
                        ['03','Student-focused Strategy','University/program পছন্দ আপনার profile ও goals বিবেচনায় করা হয়।'],
                        ['04','Long-term Mentorship','Elite students-দের জন্য application-এর বাইরেও structured support দেওয়া হয়।'],
                    ] as $pillar)
                        <div class="rounded-xl border border-border bg-surface-alt p-6">
                            <span class="text-sm font-bold text-accent-600">{{ $pillar[0] }}</span>
                            <h3 class="mt-2 font-display font-bold text-slate-900">{{ $pillar[1] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $pillar[2] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 9: MAJOR DIFFERENTIATOR ========================================== --}}
        @php $elite = $services->firstWhere('slug', 'elite-success-program'); @endphp
        <section class="py-16 lg:py-20 bg-primary-800 text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 text-sm font-semibold">⭐ 1-Year Mentorship</span>
                    <h2 class="mt-5 font-bangla text-3xl sm:text-4xl font-bold leading-tight">More Than an Application Service</h2>
                    <p class="mt-4 text-lg text-primary-100 leading-relaxed font-bangla">Admission পাওয়াই শেষ নয়। China যাওয়ার আগে এবং China journey-র শুরুতেও সঠিক guidance দরকার।</p>
                </div>

                <div class="mt-12 grid md:grid-cols-2 gap-10 items-start">
                    <div>
                        <div class="space-y-3">
                            @foreach([
                                ['Application Support','University + application guidance'],
                                ['Chinese Language','বাংলায় Chinese language training'],
                                ['Academic Guidance','Study ও student-life preparation'],
                                ['Career Direction','Long-term academic ও career support'],
                                ['1-Year Mentorship','Structured ongoing guidance'],
                            ] as $i => $diff)
                                <div class="flex items-center gap-4 rounded-xl border border-white/15 bg-white/5 px-5 py-4">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/15 font-display font-bold">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div>
                                        <p class="font-semibold">{{ $diff[0] }}</p>
                                        <p class="text-sm text-primary-100">{{ $diff[1] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/20 bg-white/10 p-8">
                        <span class="inline-block px-3 py-1 rounded-full bg-accent-600 text-white text-sm font-bold">Flagship</span>
                        <h3 class="mt-4 font-bangla text-2xl font-bold">1-Year Mentorship Program</h3>
                        <p class="mt-3 text-primary-100 leading-relaxed font-bangla">বাংলাদেশি students-দের জন্য designed একটি long-term mentorship journey — যেখানে admission support-এর সাথে Chinese language, academic guidance এবং career direction একসাথে থাকে।</p>
                        <p class="mt-3 text-primary-100 leading-relaxed font-bangla">একটি application শেষ করাই লক্ষ্য নয় — একজন শিক্ষার্থীকে China journey-র জন্য প্রস্তুত করাই লক্ষ্য।</p>
                        <a href="{{ $elite ? route('services.show', $elite->slug) : '#services' }}" class="mt-6 inline-flex items-center justify-center gap-2 px-7 py-4 bg-accent-600 text-white font-bold rounded-xl hover:bg-accent-700 transition-colors">
                            Explore Elite Success Program
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 10: OUR SERVICES ========================================== --}}
        <section id="services" class="py-16 lg:py-20 bg-surface-alt border-y border-border scroll-mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Our Services</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">Choose the Level of Support You Need</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">আপনি নিজে application করতে চান, নাকি পুরো process-এ আমাদের support চান — আপনার প্রয়োজন অনুযায়ী option বেছে নিন।</p>
                </div>

                <p class="mt-6 rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800 leading-relaxed font-bangla">
                    <strong>জেনে রাখুন:</strong> Study in China services হলো consultation ও support package — এগুলো Banglay Chinese-এর নিয়মিত Chinese language course নয়।
                </p>

                <div class="mt-10 grid lg:grid-cols-3 gap-6 items-stretch">
                    @forelse($services as $service)
                        @php
                            $meta = $serviceMeta[$service->slug] ?? ['badges' => [], 'tagline' => $service->short_description];
                            $features = is_array($service->features) ? $service->features : [];
                            $isElite = $service->slug === 'elite-success-program';
                        @endphp
                        <div class="flex flex-col rounded-2xl border bg-white p-7 {{ $isElite ? 'border-primary-600 ring-1 ring-primary-100 shadow-md lg:-my-2' : 'border-border shadow-sm' }}">
                            @if($isElite)
                                <span class="self-start rounded-full bg-primary-600 text-white text-xs font-bold px-3 py-1 uppercase tracking-wide">Most Comprehensive</span>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($meta['badges'] as $badge)
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $isElite ? 'bg-accent-50 text-accent-700 border border-accent-100' : 'bg-primary-50 text-primary-700 border border-primary-100' }}">{{ $badge }}</span>
                                @endforeach
                            </div>
                            <h3 class="mt-4 font-display text-2xl font-bold text-slate-900">{{ $service->name }}</h3>
                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-3xl font-extrabold text-slate-900">৳{{ number_format($service->price) }}</span>
                                @if($service->duration)
                                    <span class="text-sm text-slate-500">{{ $service->duration }}</span>
                                @endif
                            </div>
                            <p class="mt-4 text-slate-700 leading-relaxed font-bangla">{{ $meta['tagline'] }}</p>
                            @if($service->short_description)
                                <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ $service->short_description }}</p>
                            @endif
                            <div class="mt-5 pt-5 border-t border-border flex-1">
                                <ul class="space-y-2.5">
                                    @forelse($features as $feature)
                                        <li class="flex items-start gap-2.5 text-sm text-slate-700">
                                            <svg class="w-5 h-5 mt-0.5 text-primary-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            <span class="leading-relaxed">{{ $feature }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-400 italic">Reach out for a personalized breakdown.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="mt-6">
                                <a href="{{ route('study-in-china.consultation', ['service' => $service->slug]) }}"
                                   class="flex w-full items-center justify-center gap-2 rounded-xl px-6 py-3.5 font-bold transition-colors {{ $isElite ? 'bg-accent-600 text-white hover:bg-accent-700' : 'bg-primary-600 text-white hover:bg-primary-700' }}">
                                    {{ $service->cta_label }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                                <a href="{{ route('services.show', $service->slug) }}" class="mt-3 block w-full text-center text-sm font-semibold text-slate-500 underline hover:text-slate-700">বিস্তারিত দেখুন</a>
                            </div>
                        </div>
                    @empty
                        <div class="lg:col-span-3 text-center py-16">
                            <h3 class="text-2xl font-bold text-slate-400">আমাদের service packages শীঘ্রই আসছে।</h3>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 11: SERVICE COMPARISON ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Comparison</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">Service-গুলোর তুলনা</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">নিচের table-এ তিনটি service-এ কী কী থাকছে তার একটি সাধারণ তুলনা দেওয়া হলো।</p>
                </div>

                <div class="mt-10 rounded-2xl border border-border overflow-hidden bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[640px]">
                            <thead>
                                <tr class="bg-surface-alt border-b border-border text-left">
                                    <th class="px-5 py-4 font-bold text-slate-900">Feature</th>
                                    <th class="px-5 py-4 font-semibold text-slate-700">Guided</th>
                                    <th class="px-5 py-4 font-semibold text-slate-700">Full</th>
                                    <th class="px-5 py-4 font-bold text-primary-700 bg-primary-50">Elite</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $rows = [
                                        ['Profile evaluation', true, true, true],
                                        ['University guidance', true, true, true],
                                        ['Program selection', true, true, true],
                                        ['Document checklist', true, true, true],
                                        ['Application guidance', true, true, true],
                                        ['Application support', false, true, true],
                                        ['Admission support', false, true, true],
                                        ['Visa preparation guidance', false, true, true],
                                        ['Pre-departure support', false, true, true],
                                        ['Chinese language training', false, false, true],
                                        ['Academic guidance', false, false, true],
                                        ['Career guidance', false, false, true],
                                        ['Long-term mentorship', false, false, true],
                                    ];
                                @endphp
                                @foreach($rows as $row)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-5 py-3.5 font-medium text-slate-800">{{ $row[0] }}</td>
                                        @for($c = 1; $c <= 3; $c++)
                                            <td class="px-5 py-3.5 {{ $c === 3 ? 'bg-primary-50/40' : '' }}">
                                                @if($row[$c])
                                                    <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @else
                                                    <span class="text-slate-300">—</span>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 grid sm:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-border bg-surface-alt p-5">
                        <h3 class="font-display font-bold text-slate-900">Guided</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">আপনি application manage করবেন, expert direction-এর সাথে।</p>
                    </div>
                    <div class="rounded-xl border border-border bg-surface-alt p-5">
                        <h3 class="font-display font-bold text-slate-900">Full</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">আমরা application process-এ support করি, আপনার সাথে।</p>
                    </div>
                    <div class="rounded-xl border border-primary-200 bg-primary-50 p-5">
                        <h3 class="font-display font-bold text-primary-800">Elite</h3>
                        <p class="mt-2 text-sm text-primary-800 leading-relaxed font-bangla">আপনার broader China journey-র জন্য 1-Year support।</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 12: WHICH SERVICE IS RIGHT FOR YOU ========================================== --}}
        <section class="py-16 lg:py-20 bg-surface-alt border-y border-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Decision Helper</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">আপনার জন্য কোনটি?</h2>
                </div>

                <div class="mt-10 grid lg:grid-cols-3 gap-5">
                    <div class="rounded-xl border border-border bg-white p-6">
                        <p class="text-slate-700 leading-relaxed font-bangla">“আমি নিজে application handle করতে চাই, কিন্তু expert direction দরকার।”</p>
                        <a href="{{ route('study-in-china.consultation', ['service' => 'guided-application']) }}" class="mt-4 inline-flex items-center gap-2 text-primary-700 font-bold">Guided Application →</a>
                    </div>
                    <div class="rounded-xl border border-border bg-white p-6">
                        <p class="text-slate-700 leading-relaxed font-bangla">“আমি পুরো application process-এ professional support চাই।”</p>
                        <a href="{{ route('study-in-china.consultation', ['service' => 'full-application-service']) }}" class="mt-4 inline-flex items-center gap-2 text-primary-700 font-bold">Full Application Service →</a>
                    </div>
                    <div class="rounded-xl border border-primary-200 bg-primary-50 p-6">
                        <p class="text-primary-800 leading-relaxed font-bangla">“আমি application + Chinese language + long-term academic/career mentorship চাই।”</p>
                        <a href="{{ route('study-in-china.consultation', ['service' => 'elite-success-program']) }}" class="mt-4 inline-flex items-center gap-2 text-primary-700 font-bold">Elite Success Program →</a>
                    </div>
                </div>

                <p class="mt-8 text-sm text-slate-500 font-bangla">নিশ্চিত না? কোনো চিন্তা নেই — আমাদের টিম আপনার profile review করে সঠিক option বুঝতে সাহায্য করবে।</p>
            </div>
        </section>

        {{-- ========================================== SECTION 13: HUMAN SUPPORT ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Human-led Process</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">Your Application Is Reviewed By Our Team</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">আমরা automated application system নই। আপনার academic background, goals এবং application situation বুঝে আমাদের টিম next steps নিয়ে আপনার সাথে আলোচনা করে।</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    @foreach([
                        ['Submit Information','আপনার তথ্য জমা দিন'],
                        ['Team Reviews','টিম আপনার profile review করে'],
                        ['Consultation','goals ও options নিয়ে আলোচনা'],
                        ['Recommended Path','উপযুক্ত pathway recommend'],
                        ['Application Support','নির্বাচিত service অনুযায়ী support'],
                    ] as $i => $h)
                        <div class="rounded-xl border border-border bg-surface-alt p-5 text-center">
                            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-primary-600 text-white font-display font-bold">{{ $i + 1 }}</span>
                            <h3 class="mt-3 font-display font-bold text-slate-900">{{ $h[0] }}</h3>
                            <p class="mt-1 text-sm text-slate-600 leading-relaxed font-bangla">{{ $h[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 14: WHAT HAPPENS AFTER CONSULTATION ========================================== --}}
        <section class="py-16 lg:py-20 bg-surface-alt border-y border-border">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Next Steps</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">Consultation-এর পর কী হবে?</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">কোনো বাধ্যবাধকতা নেই — আমরা প্রথমে আপনার situation বুঝে, তারপর উপযুক্ত দিকনির্দেশনা দিই।</p>
                </div>

                <ol class="mt-10 space-y-4">
                    @foreach([
                        'আপনি consultation / eligibility form জমা দেন।',
                        'আমাদের টিম আপনার তথ্য review করে।',
                        'একজন টিম সদস্য আপনার সাথে যোগাযোগ করে।',
                        'আপনার academic background, goals ও preferred program নিয়ে আলোচনা হয়।',
                        'আমরা উপযুক্ত service ও পরবর্তী steps ব্যাখ্যা করি।',
                    ] as $i => $step)
                        <li class="flex items-start gap-4 rounded-xl border border-border bg-white p-5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white font-display font-bold">{{ $i + 1 }}</span>
                            <p class="text-slate-700 leading-relaxed font-bangla">{{ $step }}</p>
                        </li>
                    @endforeach
                </ol>

                <p class="mt-6 text-sm text-slate-500 font-bangla">আমাদের টিম আপনার তথ্য review করে পরবর্তী ধাপ সম্পর্কে যোগাযোগ করবে।</p>
            </div>
        </section>

        {{-- ========================================== SECTION 15: TESTIMONIALS / SOCIAL PROOF ========================================== --}}
        @php $testimonials = []; @endphp
        @if(count($testimonials) > 0)
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Student Stories</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">শিক্ষার্থীদের অভিজ্ঞতা</h2>
                </div>
                <div class="mt-10 grid md:grid-cols-3 gap-5">
                    @foreach($testimonials as $t)
                        <div class="rounded-xl border border-border bg-surface-alt p-6">
                            <p class="text-slate-700 leading-relaxed font-bangla">“{{ $t['quote'] }}”</p>
                            <p class="mt-4 text-sm font-bold text-slate-900">{{ $t['name'] }}</p>
                            <p class="text-sm text-slate-500">{{ $t['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- ========================================== SECTION 16: FAQ ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">FAQ</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">সাধারণ প্রশ্নাবলি</h2>
                </div>

                <div class="mt-10 space-y-3">
                    @foreach([
                        ['q' => 'China-তে কোন কোন program-এ apply করা যায়?', 'a' => 'Bachelor’s, Master’s, PhD, Chinese Language program এবং কিছু ক্ষেত্রে Diploma-সহ বিভিন্ন program-এ আবেদন করা যায়। কোনটি আপনার জন্য সম্ভব, তা university এবং আপনার academic background-এর উপর নির্ভর করে।'],
                        ['q' => 'IELTS বা English প্রমাণ কি বাধ্যতামূলক?', 'a' => 'প্রতিটি university ও program-এর নিজস্ব language requirement থাকে। কিছুতে English medium-এ পড়া যায়, কিছুতে Chinese/HSK প্রয়োজন হয়। আপনার program অনুযায়ী requirement আলাদা হতে পারে।'],
                        ['q' => 'HSK কি দরকার?', 'a' => 'Chinese medium program-এ সাধারণত HSK দরকার হয়, আবার English medium program-এ নাও লাগতে পারে। কোন program-এ কী লাগবে, তা review করে জানানো হয়।'],
                        ['q' => 'আমি HSC পাস করে Bachelor’s করতে পারব?', 'a' => 'হ্যাঁ, HSC বা equivalent শিক্ষার্থীরা সাধারণত Bachelor’s program-এ আবেদন করতে পারেন। তবে যোগ্যতা নির্দিষ্ট university-র requirements-এর উপর নির্ভর করে।'],
                        ['q' => 'Master’s-এর জন্য কী কী লাগে?', 'a' => 'সাধারণত Bachelor’s degree এবং প্রাসঙ্গিক documents লাগে। Language ও অন্যান্য requirements university অনুযায়ী ভিন্ন হতে পারে।'],
                        ['q' => 'Scholarship পাওয়ার সুযোগ আছে?', 'a' => 'University, government ও provincial level-এ বিভিন্ন scholarship-এর সুযোগ থাকতে পারে। তবে এগুলো guaranteed নয় — availability ও eligibility university, program ও intake অনুযায়ী ভিন্ন।'],
                        ['q' => 'আমি কি নিজের university বেছে নিতে পারি?', 'a' => 'আপনার পছন্দকে গুরুত্ব দেওয়া হয়। তবে কোন university আপনার profile ও goals-এর সাথে মানানসই, তা review করে আপনাকে understand করতে সাহায্য করা হয়।'],
                        ['q' => 'Banglay Chinese কি application করে দেয়?', 'a' => 'আপনার নেওয়া service-এর উপর নির্ভর করে। Guided Application-এ আপনি নিজে করেন, Full ও Elite-তে আমাদের টিম application process-এ support করে।'],
                        ['q' => 'Guided Application মানে কী?', 'a' => 'এটি তাদের জন্য, যারা নিজে application করতে চান কিন্তু expert direction ও strategy support চান। Application নিজেই করবেন, দিকনির্দেশনা পাবেন আমাদের কাছ থেকে।'],
                        ['q' => 'Full Application Service-এ কী কী পাব?', 'a' => 'University/program guidance, document ও application support, admission process support, pre-departure preparation — application-এর মূল ধাপগুলোতে team-এর hands-on support।'],
                        ['q' => 'Elite Success Program কী?', 'a' => 'এটি একটি premium 1-Year Mentorship Program — যেখানে admission support-এর সাথে Chinese language training, academic guidance ও career-oriented mentorship একসাথে থাকে।'],
                        ['q' => '1-Year Mentorship-এর মধ্যে কী থাকে?', 'a' => 'Elite program-এ admission support-সহ Chinese language, academic ও career guidance এবং ongoing mentor support — application-এর বাইরে দীর্ঘমেয়াদি structured support।'],
                        ['q' => 'আপনারা কি Chinese language training দেন?', 'a' => 'হ্যাঁ, Banglay Chinese বাংলায় Chinese language course ও training দিয়ে থাকে। Elite Success Program-এ language training-এর সুযোগও থাকে।'],
                        ['q' => 'Admission / Scholarship / Visa কি guaranteed?', 'a' => 'না। Admission, scholarship বা visa-এর কোনোটি guaranteed নয় — এগুলো university ও সংশ্লিষ্ট প্রতিষ্ঠানের সিদ্ধান্তের উপর নির্ভর করে। আমরা প্রক্রিয়াটিতে সঠিক guidance দেওয়ার চেষ্টা করি।'],
                        ['q' => 'Consultation-এর পর কী হবে?', 'a' => 'আপনার তথ্য জমা দিলে আমাদের টিম তা review করে পরবর্তী ধাপ সম্পর্কে আপনার সাথে যোগাযোগ করবে। কোনো বাধ্যবাধকতা নেই।'],
                    ] as $faq)
                        <details class="group rounded-xl border border-border bg-surface-alt">
                            <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 font-semibold text-slate-800 list-none">
                                <span class="font-bangla leading-relaxed">{{ $faq['q'] }}</span>
                                <svg class="w-5 h-5 text-primary-600 flex-shrink-0 transition-transform group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </summary>
                            <p class="px-5 pb-5 text-slate-600 leading-relaxed font-bangla">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 17: FINAL CTA ========================================== --}}
        <section class="py-16 lg:py-20 bg-primary-800 text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="font-bangla text-3xl sm:text-4xl font-bold leading-tight">Ready to Explore Your China Study Options?</h2>
                <p class="mt-5 text-lg text-primary-100 leading-relaxed font-bangla">আপনার academic background, goals এবং preferred program সম্পর্কে আমাদের জানান। আমাদের টিম আপনার জন্য সম্ভাব্য pathway বুঝতে সাহায্য করবে।</p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="#consultation" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-primary-700 font-bold rounded-xl hover:bg-primary-50 transition-colors text-lg">Get Eligibility Review</a>
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-white/40 text-white font-bold rounded-xl hover:bg-white/10 transition-colors text-lg">Talk to Our Team</a>
                </div>
                <p class="mt-6 text-sm text-primary-200">অথবা WhatsApp-এ সরাসরি মেসেজ করুন — এটি একটি secondary contact option।</p>
            </div>
        </section>

        {{-- ========================================== SECTION 18: CONSULTATION FORM ========================================== --}}
        <span id="booking" class="block scroll-mt-20"></span>
        <section id="consultation" class="py-16 lg:py-20 bg-surface-alt border-t border-border scroll-mt-20">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">Eligibility Review</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">আপনার তথ্য জমা দিন</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">কোনো বাধ্যবাধকতা নেই। আমাদের টিম আপনার profile review করে পরবর্তী ধাপ সম্পর্কে যোগাযোগ করবে।</p>
                </div>

                @if(session('success'))
                    <div class="mt-8 rounded-xl border border-primary-200 bg-primary-50 p-6 text-center">
                        <p class="text-primary-800 font-semibold font-bangla leading-relaxed">{{ session('success') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mt-8 rounded-xl border border-red-200 bg-red-50 p-5">
                        <ul class="list-disc pl-5 space-y-1 text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('study-in-china.consultation.store') }}" method="POST" class="mt-8 rounded-2xl border border-border bg-white p-6 sm:p-8 shadow-sm">
                    @csrf

                    @php
                        $preselectedServiceId = old('interested_service_id', optional(\App\Models\Service::where('slug', request('service'))->first())->id);
                    @endphp

                    <div>
                        <label for="interested_service_id" class="block text-sm font-bold text-slate-700 mb-2">আপনি কোন Service সম্পর্কে জানতে চান?</label>
                        <select name="interested_service_id" id="interested_service_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                            <option value="">-- বেছে নিন (optional) --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ (string) $preselectedServiceId === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-5 grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="block text-sm font-bold text-slate-700 mb-2">সম্পূর্ণ নাম (Full Name) <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="আপনার নাম লিখুন" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-bold text-slate-700 mb-2">ফোন নম্বর / WhatsApp <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="+8801XXXXXXXXX" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="email" class="block text-sm font-bold text-slate-700 mb-2">ইমেইল (Email Address) <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="example@email.com" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-5 grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="highest_qualification" class="block text-sm font-bold text-slate-700 mb-2">সর্বোচ্চ শিক্ষাগত যোগ্যতা <span class="text-red-500">*</span></label>
                            <select name="highest_qualification" id="highest_qualification" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                                <option value="">-- বেছে নিন --</option>
                                @foreach(['SSC / O-Level','HSC / A-Level','Diploma',"Bachelor's Degree","Master's Degree"] as $hq)
                                    <option value="{{ $hq }}" {{ old('highest_qualification') === $hq ? 'selected' : '' }}>{{ $hq }}</option>
                                @endforeach
                            </select>
                            @error('highest_qualification') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="desired_program" class="block text-sm font-bold text-slate-700 mb-2">কাঙ্ক্ষিত Program <span class="text-red-500">*</span></label>
                            <select name="desired_program" id="desired_program" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                                <option value="">-- বেছে নিন --</option>
                                @foreach(["Bachelor's","Master's","PhD","Chinese Language Program","Diploma / Other"] as $dp)
                                    <option value="{{ $dp }}" {{ old('desired_program') === $dp ? 'selected' : '' }}>{{ $dp }}</option>
                                @endforeach
                            </select>
                            @error('desired_program') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5 grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="target_intake" class="block text-sm font-bold text-slate-700 mb-2">Preferred Intake <span class="text-red-500">*</span></label>
                            <select name="target_intake" id="target_intake" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                                <option value="">-- বেছে নিন --</option>
                                @foreach(['September 2026','February/March 2027','September 2027','Other'] as $int)
                                    <option value="{{ $int }}" {{ old('target_intake') === $int ? 'selected' : '' }}>{{ $int }}</option>
                                @endforeach
                            </select>
                            @error('target_intake') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="budget" class="block text-sm font-bold text-slate-700 mb-2">Budget Range</label>
                            <select name="budget" id="budget" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                                <option value="">-- বেছে নিন --</option>
                                @foreach(['Under 2 Lakh BDT','2-5 Lakh BDT','5-10 Lakh BDT','10+ Lakh BDT','Seeking Full Scholarship'] as $bg)
                                    <option value="{{ $bg }}" {{ old('budget') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="message" class="block text-sm font-bold text-slate-700 mb-2">Message / Goal</label>
                        <textarea name="message" id="message" rows="4" placeholder="আপনার goals, প্রশ্ন বা যেকোনো তথ্য জানান..." class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none resize-none">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="mt-6 w-full rounded-xl bg-primary-600 px-6 py-4 text-lg font-bold text-white shadow-sm transition-colors hover:bg-primary-700">Submit for Eligibility Review</button>
                    <p class="mt-3 text-center text-xs text-slate-400 font-bangla">আপনার তথ্য সুরক্ষিত থাকবে এবং শুধুমাত্র আমাদের টিম consultation-এর জন্য ব্যবহার করবে।</p>
                </form>
            </div>
        </section>
    </div>
</x-app-layout>

