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
    @php
        // FAQ answers and a few long paragraphs are edited as HTML in the Study
        // in China CMS (RichEditor). Plain-text values are still rendered
        // paragraph by paragraph, so existing content keeps its layout.
        $prose = static function (mixed $content): string {
            $content = trim((string) $content);

            if ($content === '' || str_contains($content, '<')) {
                return $content;
            }

            $paragraphs = array_filter(
                preg_split('/\n\s*\n/', $content) ?: [],
                fn (string $paragraph): bool => trim($paragraph) !== ''
            );

            return implode('', array_map(
                fn (string $paragraph): string => '<p>' . e(trim($paragraph)) . '</p>',
                $paragraphs
            ));
        };
    @endphp
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
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-50 text-primary-700 border border-primary-100 text-sm font-semibold">{{ $settings['sic_hero_badge'] ?? '' }}</span>

                        <h1 class="mt-6 font-bangla text-4xl sm:text-5xl lg:text-[3.4rem] font-bold leading-tight text-slate-900">{{ $settings['sic_hero_title'] ?? '' }}</h1>

                        <p class="mt-5 text-lg sm:text-xl text-slate-600 leading-relaxed max-w-2xl font-bangla">{{ $settings['sic_hero_subtitle'] ?? '' }}</p>

                        <p class="mt-5 inline-flex flex-wrap items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-alt border border-border text-sm font-semibold text-slate-700"><span class="text-accent-600">◆</span> {{ $settings['sic_hero_note'] ?? '' }}</p>

                        <div class="mt-8 flex flex-col sm:flex-row gap-4">
                            <a href="{{ $settings['sic_hero_cta1_url'] ?? '#consultation' }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary-600 text-white font-bold rounded-xl shadow-sm hover:bg-primary-700 transition-colors text-lg">{{ $settings['sic_hero_cta1_text'] ?? '' }}
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></a>
                            <a href="{{ $settings['sic_hero_cta2_url'] ?? '#services' }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-slate-200 text-slate-700 font-semibold rounded-xl hover:border-primary-300 hover:text-primary-700 transition-colors text-lg">{{ $settings['sic_hero_cta2_text'] ?? '' }}</a>
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        <div class="relative bg-gradient-to-br from-primary-50 to-surface border border-primary-100 rounded-2xl p-5 sm:p-8 shadow-sm">
                            <span class="inline-block px-3 py-1 rounded-full bg-white text-primary-700 text-sm font-bold shadow-sm">{{ $settings['sic_hero_checklist_title'] ?? '' }}</span>
                            <ul class="mt-6 space-y-4">
                                @foreach($settings['sic_hero_checklist'] ?? [] as $item)
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
                    @foreach($settings['sic_trust_values'] ?? [] as $value)
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
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_why_china_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_why_china_title'] ?? '' }}</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">{{ $settings['sic_why_china_intro'] ?? '' }}</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($settings['sic_why_china_cards'] ?? [] as $why)
                        <div class="rounded-xl border border-border bg-white p-6">
                            <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary-50 text-2xl">{{ $why['icon'] }}</span>
                            <h3 class="mt-4 font-display font-bold text-slate-900">{{ $why['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $why['desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 text-center">
                    <a href="#consultation" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-primary-600 text-primary-700 font-bold rounded-xl hover:bg-primary-50 transition-colors">{{ $settings['sic_why_china_cta_text'] ?? '' }}</a>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 4: WHAT CAN YOU STUDY ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_programs_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_programs_title'] ?? '' }}</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">{{ $settings['sic_programs_intro'] ?? '' }}</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($settings['sic_programs_cards'] ?? [] as $prog)
                        <div class="rounded-xl border border-border bg-surface-alt p-6">
                            <span class="inline-block px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-bold uppercase tracking-wide">{{ $prog['deg'] }}</span>
                            <p class="mt-4 text-sm font-semibold text-slate-800">{{ $prog['for'] }}</p>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $prog['desc'] }}</p>
                        </div>
                    @endforeach

                    <div class="rounded-xl border border-primary-200 bg-primary-50 p-6 flex flex-col justify-center">
                        <p class="text-sm text-primary-800 leading-relaxed font-bangla">কোন program আপনার জন্য সঠিক — এটা university-র requirements, আপনার background ও goals-এর উপর নির্ভর করে।</p>
                        <a href="#consultation" class="mt-4 inline-flex min-h-[44px] items-center gap-2 text-primary-700 font-bold text-sm">Check Your Eligibility →</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 5: SCHOLARSHIPS ========================================== --}}
        <section class="py-16 lg:py-20 bg-surface-alt border-y border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_scholarships_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_scholarships_title'] ?? '' }}</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">{{ $settings['sic_scholarships_intro'] ?? '' }}</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 gap-5">
                    @foreach($settings['sic_scholarships_cards'] ?? [] as $s)
                        <div class="rounded-xl border border-border bg-white p-6">
                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-accent-50 text-xl">🏅</span>
                            <h3 class="mt-4 font-display font-bold text-slate-900">{{ $s['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $s['desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 leading-relaxed font-bangla">
                    <strong>মনে রাখবেন:</strong> {!! $prose($settings['sic_scholarships_note'] ?? '') !!}
                </div>

                <div class="mt-8 text-center">
                    <a href="#consultation" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-primary-600 text-primary-700 font-bold rounded-xl hover:bg-primary-50 transition-colors">{{ $settings['sic_scholarships_cta_text'] ?? '' }}</a>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 6: WHO CAN APPLY ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_eligibility_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_eligibility_title'] ?? '' }}</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">{{ $settings['sic_eligibility_intro'] ?? '' }}</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($settings['sic_eligibility_cards'] ?? [] as $el)
                        <div class="rounded-xl border border-border bg-surface-alt p-5">
                            <h3 class="font-display font-bold text-slate-900">{{ $el['t'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $el['d'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 rounded-xl border border-border bg-surface-alt p-6">
                    <h3 class="font-display font-bold text-slate-900">{{ $settings['sic_eligibility_box_title'] ?? '' }}</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($settings['sic_eligibility_factors'] ?? [] as $f)
                            <span class="rounded-full bg-white border border-border px-3 py-1.5 text-sm text-slate-700">{{ $f['label'] }}</span>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-slate-600 font-bangla"><strong>{{ $settings['sic_eligibility_note'] ?? '' }}</strong></p>
                    <a href="#consultation" class="mt-4 inline-flex min-h-[44px] items-center gap-2 text-primary-700 font-bold text-sm">{{ $settings['sic_eligibility_cta_text'] ?? '' }} →</a>
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 7: APPLICATION ROADMAP ========================================== --}}
        <section class="py-16 lg:py-20 bg-surface-alt border-y border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_roadmap_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_roadmap_title'] ?? '' }}</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">{{ $settings['sic_roadmap_intro'] ?? '' }}</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($settings['sic_roadmap_steps'] ?? [] as $step)
                        <div class="rounded-xl border border-border bg-white p-5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-600 text-white font-display font-bold">{{ $step['num'] }}</span>
                            <h3 class="mt-4 font-display font-bold text-slate-900">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 8: WHY BANGLAY CHINESE ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_why_banglay_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_why_banglay_title'] ?? '' }}</h2>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 gap-5">
                    @foreach($settings['sic_why_banglay_pillars'] ?? [] as $pillar)
                        <div class="rounded-xl border border-border bg-surface-alt p-6">
                            <span class="text-sm font-bold text-accent-600">{{ $pillar['num'] }}</span>
                            <h3 class="mt-2 font-display font-bold text-slate-900">{{ $pillar['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed font-bangla">{{ $pillar['desc'] }}</p>
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
                    <h2 class="mt-5 font-bangla text-3xl sm:text-4xl font-bold leading-tight">{{ $settings['sic_diff_title'] ?? '' }}</h2>
                    <p class="mt-4 text-lg text-primary-100 leading-relaxed font-bangla">{{ $settings['sic_diff_intro'] ?? '' }}</p>
                </div>

                <div class="mt-12 grid md:grid-cols-2 gap-10 items-start">
                    <div>
                        <div class="space-y-3">
                            @foreach($settings['sic_diff_rows'] ?? [] as $i => $diff)
                                <div class="flex items-center gap-4 rounded-xl border border-white/15 bg-white/5 px-5 py-4">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/15 font-display font-bold">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div>
                                        <p class="font-semibold">{{ $diff['title'] }}</p>
                                        <p class="text-sm text-primary-100">{{ $diff['desc'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 sm:p-8">
                        <span class="inline-block px-3 py-1 rounded-full bg-accent-600 text-white text-sm font-bold">{{ $settings['sic_flagship_badge'] ?? '' }}</span>
                        <h3 class="mt-4 font-bangla text-2xl font-bold">{{ $settings['sic_flagship_title'] ?? '' }}</h3>
                        <div class="mt-3 text-primary-100 leading-relaxed font-bangla">{!! $prose($settings['sic_flagship_body_1'] ?? '') !!}</div>
                        <div class="mt-3 text-primary-100 leading-relaxed font-bangla">{!! $prose($settings['sic_flagship_body_2'] ?? '') !!}</div>
                        <a href="{{ $elite ? route('services.show', $elite->slug) : '#services' }}" class="mt-6 inline-flex items-center justify-center gap-2 px-7 py-4 bg-accent-600 text-white font-bold rounded-xl hover:bg-accent-700 transition-colors">
                            {{ $settings['sic_flagship_cta_text'] ?? '' }}
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
                                <a href="{{ route('services.show', $service->slug) }}" class="mt-3 block w-full py-2 text-center text-sm font-semibold text-slate-500 underline hover:text-slate-700">বিস্তারিত দেখুন</a>
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
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_decision_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_decision_title'] ?? '' }}</h2>
                </div>

                <div class="mt-10 grid lg:grid-cols-3 gap-5">
                    @foreach($settings['sic_decision_quotes'] ?? [] as $i => $card)
                        @php $slugs = ['guided-application', 'full-application-service', 'elite-success-program']; @endphp
                        <div class="rounded-xl border p-6 {{ $i === 2 ? 'border-primary-200 bg-primary-50' : 'border-border bg-white' }}">
                            <p class="leading-relaxed font-bangla {{ $i === 2 ? 'text-primary-800' : 'text-slate-700' }}">{{ $card['quote'] }}</p>
                            <a href="{{ route('study-in-china.consultation', ['service' => $slugs[$i] ?? 'guided-application']) }}" class="mt-4 inline-flex min-h-[44px] items-center gap-2 text-primary-700 font-bold">
                                @if($i === 2) Elite Success Program → @elseif($i === 1) Full Application Service → @else Guided Application → @endif
                            </a>
                        </div>
                    @endforeach
                </div>

                <p class="mt-8 text-sm text-slate-500 font-bangla">{{ $settings['sic_decision_note'] ?? '' }}</p>
            </div>
        </section>

        {{-- ========================================== SECTION 13: HUMAN SUPPORT ========================================== --}}
        <section class="py-16 lg:py-20 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_human_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_human_title'] ?? '' }}</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">{{ $settings['sic_human_intro'] ?? '' }}</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    @foreach($settings['sic_human_steps'] ?? [] as $i => $h)
                        <div class="rounded-xl border border-border bg-surface-alt p-5 text-center">
                            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-primary-600 text-white font-display font-bold">{{ $i + 1 }}</span>
                            <h3 class="mt-3 font-display font-bold text-slate-900">{{ $h['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-600 leading-relaxed font-bangla">{{ $h['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 14: WHAT HAPPENS AFTER CONSULTATION ========================================== --}}
        <section class="py-16 lg:py-20 bg-surface-alt border-y border-border">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_next_steps_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_next_steps_title'] ?? '' }}</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">{{ $settings['sic_next_steps_intro'] ?? '' }}</p>
                </div>

                <ol class="mt-10 space-y-4">
                    @foreach($settings['sic_next_steps_list'] ?? [] as $i => $step)
                        <li class="flex items-start gap-4 rounded-xl border border-border bg-white p-5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white font-display font-bold">{{ $i + 1 }}</span>
                            <p class="text-slate-700 leading-relaxed font-bangla">{{ $step['text'] }}</p>
                        </li>
                    @endforeach
                </ol>

                <p class="mt-6 text-sm text-slate-500 font-bangla">{{ $settings['sic_next_steps_note'] ?? '' }}</p>
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
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_faq_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_faq_title'] ?? '' }}</h2>
                </div>

                <div class="mt-10 space-y-3">
                    @foreach($settings['sic_faq_items'] ?? [] as $faq)
                        <details class="group rounded-xl border border-border bg-surface-alt">
                            <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 font-semibold text-slate-800 list-none">
                                <span class="font-bangla leading-relaxed">{{ $faq['question'] }}</span>
                                <svg class="w-5 h-5 text-primary-600 flex-shrink-0 transition-transform group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </summary>
                            <div class="px-5 pb-5 text-slate-600 leading-relaxed font-bangla">{!! $prose($faq['answer']) !!}</div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ========================================== SECTION 17: FINAL CTA ========================================== --}}
        <section class="py-16 lg:py-20 bg-primary-800 text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="font-bangla text-3xl sm:text-4xl font-bold leading-tight">{{ $settings['sic_cta_title'] ?? '' }}</h2>
                <p class="mt-5 text-lg text-primary-100 leading-relaxed font-bangla">{{ $settings['sic_cta_body'] ?? '' }}</p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="#consultation" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-primary-700 font-bold rounded-xl hover:bg-primary-50 transition-colors text-lg">{{ $settings['sic_cta_btn1_text'] ?? '' }}</a>
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-white/40 text-white font-bold rounded-xl hover:bg-white/10 transition-colors text-lg">{{ $settings['sic_cta_btn2_text'] ?? '' }}</a>
                </div>
                <p class="mt-6 text-sm text-primary-200">{{ $settings['sic_cta_note'] ?? '' }}</p>
            </div>
        </section>

        {{-- ========================================== SECTION 18: CONSULTATION FORM ========================================== --}}
        <span id="booking" class="block scroll-mt-20"></span>
        <section id="consultation" class="py-16 lg:py-20 bg-surface-alt border-t border-border scroll-mt-20">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="text-sm font-bold tracking-wide text-accent-600 uppercase">{{ $settings['sic_form_eyebrow'] ?? '' }}</span>
                    <h2 class="mt-3 font-bangla text-3xl sm:text-4xl font-bold text-slate-900">{{ $settings['sic_form_title'] ?? '' }}</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed font-bangla">{{ $settings['sic_form_intro'] ?? '' }}</p>
                </div>

                <div class="mt-8 rounded-2xl border border-border bg-white p-8 text-center shadow-sm sm:p-10">
                    <h3 class="font-bangla text-xl font-bold text-slate-900 sm:text-2xl">{{ $settings['sic_form_cta_heading'] ?? 'Ready for your free evaluation?' }}</h3>
                    <p class="mt-3 font-bangla text-slate-600 leading-relaxed">{{ $settings['sic_form_cta_body'] ?? 'ফ্রি প্রোফাইল মূল্যায়ন ও স্টেপ-বাই-স্টেপ গাইডেন্সের জন্য ফর্মটি পূরণ করুন — আমাদের এক্সপার্ট মেন্টর যোগাযোগ করবেন।' }}</p>
                    <a href="{{ route('study-in-china.consultation') }}"
                       class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-8 py-4 text-lg font-bold text-white shadow-sm transition-colors hover:bg-primary-700">
                        Apply for Free Evaluation
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <p class="mt-4 text-xs text-slate-400 font-bangla">আপনার তথ্য সুরক্ষিত থাকবে এবং শুধুমাত্র আমাদের টিম evaluation-এর জন্য ব্যবহার করবে।</p>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>

