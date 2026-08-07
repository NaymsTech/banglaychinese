<x-app-layout>
    @push('meta')
        <meta name="description" content="Study in China with expert guidance from BanglayChinese. Personal experience since 2017. Free consultation, scholarship guidance, and complete application support.">

        <!-- Open Graph -->
        <meta property="og:title" content="Study in China with Confidence — BanglayChinese">
        <meta property="og:description" content="Start your journey with guidance from someone who has personally studied in China since 2017. Free consultation available.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="BanglayChinese">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Study in China with Confidence — BanglayChinese">
        <meta name="twitter:description" content="Start your journey with guidance from someone who has personally studied in China since 2017.">

         <!-- Schema.org -->
         <script type="application/ld+json">
         {
             "@@context": "https://schema.org",
             "@@type": "Service",
            "name": "Study in China Consultation Services",
            "provider": {
                "@type": "Organization",
                "name": "BanglayChinese",
                "url": "{{ url('/') }}"
            },
            "description": "Expert guidance for Bangladeshi students wanting to study in China. Free consultation, scholarship guidance, and complete application support.",
            "serviceType": "Education Consulting",
            "areaServed": "Bangladesh"
        }
        </script>
    @endpush

    @php
        $hero = $sections['hero'] ?? [];
        $whyChina = $sections['why_china'] ?? [];
        $whyUs = $sections['why_us'] ?? [];
        $roadmap = $sections['roadmap'] ?? [];
        $services = $sections['services'] ?? [];
        $comparison = $sections['comparison'] ?? [];
        $scholarships = $sections['scholarships'] ?? [];
        $quote = $sections['quote'] ?? [];
        $faqs = $sections['faqs'] ?? [];
        $booking = $sections['booking'] ?? [];
        $finalCta = $sections['final_cta'] ?? [];
    @endphp

    {{-- ============================================ SECTION 1: PREMIUM HERO ============================================ --}}
    <section class="relative overflow-hidden bg-white pt-16 pb-20 lg:pt-24 lg:pb-28">
        {{-- Subtle background decoration --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-red-50/50 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-slate-50 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                {{-- Left: Text Content --}}
                <div>
                    {{-- Trust Badges --}}
                    <div class="flex flex-wrap gap-3 mb-8">
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 rounded-full text-sm font-medium">
                            {{ $hero['badge_1'] ?? '🇨🇳 Studying in China Since 2017' }}
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 rounded-full text-sm font-medium">
                            {{ $hero['badge_2'] ?? '🏆 Chinese Bridge 2024 World Top 10 Finalist' }}
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm font-medium">
                            {{ $hero['badge_3'] ?? '💼 2+ Years Professional Chinese Interpreter' }}
                        </span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-[1.1]">
                        {{ $hero['heading'] ?? 'Study in China with Confidence' }}
                    </h1>

                    <p class="mt-6 text-lg sm:text-xl text-slate-600 leading-relaxed max-w-xl">
                        {{ $hero['subtitle'] ?? 'Start your journey with guidance from someone who has personally studied in China since 2017 and understands every step of the process.' }}
                    </p>

                    {{-- CTA Buttons --}}
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="#consultation"
                           class="inline-flex items-center gap-2 px-8 py-4 bg-red-600 text-white font-semibold rounded-xl shadow-lg shadow-red-200 hover:bg-red-700 hover:shadow-red-300 transition-all duration-300 text-lg">
                            {{ $hero['cta_primary'] ?? 'Book Free Consultation' }}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#services"
                           class="inline-flex items-center gap-2 px-8 py-4 border-2 border-slate-200 text-slate-700 font-semibold rounded-xl hover:border-slate-300 hover:bg-slate-50 transition-all duration-300 text-lg">
                            {{ $hero['cta_secondary'] ?? 'Explore Service Packages' }}
                        </a>
                    </div>

                    {{-- Trust Stats --}}
                    @php $stats = $hero['stats'] ?? []; @endphp
                    @if(!empty($stats))
                        <div class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-6">
                            @foreach($stats as $stat)
                                <div class="text-center">
                                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stat['number'] }}</div>
                                    <div class="mt-1 text-sm text-slate-500">{{ $stat['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Right: Founder Image --}}
                <div class="hidden lg:flex justify-center">
                    <div class="relative">
                        <div class="w-80 h-80 lg:w-96 lg:h-96 rounded-3xl overflow-hidden shadow-2xl shadow-slate-200 border-4 border-white">
                            <img src="{{ asset($hero['founder_image'] ?? 'images/founder.png') }}"
                                 alt="Founder"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'">
                        </div>
                        {{-- Floating badge --}}
                        <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl shadow-xl px-6 py-4 border border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-xl">
                                    🎓
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Personal Experience</div>
                                    <div class="text-xs text-slate-500">Studying in China since 2017</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ SECTION 2: WHY STUDY IN CHINA ============================================ --}}
    <section class="py-20 lg:py-28 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
                    {{ $whyChina['heading'] ?? 'Why Study in China?' }}
                </h2>
            </div>

            @php $cards = $whyChina['cards'] ?? []; @endphp
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($cards as $card)
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-2xl mb-4">
                            {{ $card['icon'] ?? '✨' }}
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $card['title'] }}</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ $card['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ SECTION 3: WHY CHOOSE BANGLAYCHINESE ============================================ --}}
    <section class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                {{-- Left: Content --}}
                <div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
                        {{ $whyUs['heading'] ?? 'Why Choose BanglayChinese?' }}
                    </h2>

                    @php $descriptions = $whyUs['description'] ?? []; @endphp
                    <div class="mt-10 space-y-8">
                        @foreach($descriptions as $point)
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center text-red-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">{{ $point['title'] }}</h3>
                                    <p class="mt-1 text-slate-600 leading-relaxed">{{ $point['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Founder Card --}}
                <div class="lg:pl-8">
                    <div class="bg-slate-50 rounded-3xl p-8 lg:p-10 border border-slate-100 shadow-sm">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden mx-auto mb-6 shadow-lg">
                            <img src="{{ asset($whyUs['founder_image'] ?? 'images/founder.png') }}"
                                 alt="{{ $whyUs['founder_name'] ?? 'Founder' }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'">
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 text-center">{{ $whyUs['founder_name'] ?? 'Naymur Rahman' }}</h3>
                        <p class="text-red-600 font-medium text-center text-sm mt-1">{{ $whyUs['founder_title'] ?? 'Founder & Lead Mentor' }}</p>
                        <p class="mt-4 text-slate-600 text-center leading-relaxed text-sm">
                            {{ $whyUs['founder_bio'] ?? 'Chinese Bridge 2024 World Top 10 Finalist. Studying in China since 2017. Professional Chinese Interpreter.' }}
                        </p>
                        <div class="mt-6 flex justify-center gap-3">
                            <span class="px-3 py-1 bg-white rounded-full text-xs font-medium text-slate-600 border border-slate-200">🏆 Chinese Bridge Finalist</span>
                            <span class="px-3 py-1 bg-white rounded-full text-xs font-medium text-slate-600 border border-slate-200">🇨🇳 7+ Years in China</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ SECTION 4: ROADMAP TIMELINE ============================================ --}}
    <section class="py-20 lg:py-28 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
                    {{ $roadmap['heading'] ?? 'Our Complete Study Abroad Roadmap' }}
                </h2>
            </div>

            @php $steps = $roadmap['steps'] ?? []; @endphp
            <div class="relative">
                {{-- Vertical line --}}
                <div class="absolute left-8 top-0 bottom-0 w-px bg-slate-200 hidden sm:block"></div>

                <div class="space-y-8">
                    @foreach($steps as $step)
                        <div class="relative flex items-start gap-6">
                            {{-- Step number --}}
                            <div class="relative z-10 flex-shrink-0 w-16 h-16 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center">
                                <span class="text-xl font-extrabold text-red-600">{{ str_pad($step['step'], 2, '0', STR_PAD_LEFT) }}</span>
                            </div>

                            {{-- Content --}}
                            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex-1 hover:shadow-md transition-shadow duration-300">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900">{{ $step['title'] }}</h3>
                                        <p class="mt-2 text-slate-600 text-sm leading-relaxed">{{ $step['description'] }}</p>
                                    </div>
                                    @if(!$loop->last)
                                        <svg class="w-5 h-5 text-slate-300 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ SECTION 5: SERVICE PACKAGES ============================================ --}}
    <section id="services" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
                    {{ $services['heading'] ?? 'Choose Your Support Level' }}
                </h2>
                <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
                    {{ $services['subtitle'] ?? 'Every student has different needs. Choose the level of support that best matches your goals.' }}
                </p>
            </div>

            @php $packages = $services['packages'] ?? []; @endphp
            <div class="grid lg:grid-cols-3 gap-8 items-stretch">
                @foreach($packages as $package)
                    <div class="relative bg-white rounded-3xl border-2 {{ !empty($package['featured']) ? 'border-red-500 shadow-2xl shadow-red-100 scale-[1.02] z-10' : (!empty($package['premium']) ? 'border-slate-800 shadow-xl' : 'border-slate-200 shadow-sm') }} p-8 flex flex-col">
                        {{-- Badge --}}
                        @if(!empty($package['badge']))
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-2 {{ !empty($package['featured']) ? 'bg-red-600 text-white' : 'bg-slate-800 text-white' }} rounded-full text-sm font-bold whitespace-nowrap shadow-lg">
                                {{ $package['badge'] }}
                            </div>
                        @endif

                        {{-- Tag --}}
                        @if(!empty($package['tag']))
                            <p class="text-sm text-slate-500 font-medium mb-2">{{ $package['tag'] }}</p>
                        @endif

                        <h3 class="text-xl font-bold text-slate-900">{{ $package['name'] }}</h3>

                        {{-- Price --}}
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-sm text-slate-500">{{ $package['currency'] }}</span>
                            <span class="text-4xl font-extrabold text-slate-900">{{ $package['price'] }}</span>
                        </div>

                        <p class="mt-3 text-slate-600 text-sm leading-relaxed">{{ $package['description'] }}</p>

                        {{-- Features --}}
                        <ul class="mt-6 space-y-3 flex-1">
                            @foreach($package['features'] as $feature)
                                <li class="flex items-start gap-3 text-sm text-slate-700">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        @if(!empty($package['note']))
                            <p class="mt-4 text-xs text-slate-400 italic">{{ $package['note'] }}</p>
                        @endif

                        <a href="{{ $package['cta_url'] ?? '#consultation' }}"
                           class="mt-6 w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold transition-all duration-300
                                  {{ !empty($package['featured']) ? 'bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200' : (!empty($package['premium']) ? 'bg-slate-800 text-white hover:bg-slate-900 shadow-lg' : 'bg-slate-100 text-slate-700 hover:bg-slate-200') }}">
                            {{ $package['cta_text'] ?? 'Book Free Consultation' }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ SECTION 6: COMPARISON TABLE ============================================ --}}
    <section class="py-20 lg:py-28 bg-slate-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
                    {{ $comparison['heading'] ?? 'Compare Service Packages' }}
                </h2>
            </div>

            @php $features = $comparison['features'] ?? []; @endphp
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="text-left px-6 py-5 text-sm font-bold text-slate-900">Feature</th>
                                <th class="text-center px-6 py-5 text-sm font-bold text-slate-700">Guided</th>
                                <th class="text-center px-6 py-5 text-sm font-bold text-red-600 bg-red-50/30">Complete</th>
                                <th class="text-center px-6 py-5 text-sm font-bold text-slate-700">Elite</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($features as $row)
                                <tr class="border-b border-slate-50 hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4 text-sm text-slate-700">{{ $row['feature'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($row['guided'])
                                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center bg-red-50/10">
                                        @if($row['complete'])
                                            <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($row['elite'])
                                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ SECTION 7: SCHOLARSHIP OPPORTUNITIES ============================================ --}}
    <section class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
                    {{ $scholarships['heading'] ?? 'Scholarship Opportunities' }}
                </h2>
            </div>

            @php $scholarshipCards = $scholarships['cards'] ?? []; @endphp
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($scholarshipCards as $scholarship)
                    <div class="bg-slate-50 rounded-3xl p-6 border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">
                        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-3xl shadow-sm mb-5">
                            {{ $scholarship['icon'] ?? '🎓' }}
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $scholarship['title'] }}</h3>
                        <p class="text-sm text-red-600 font-medium mt-1">{{ $scholarship['subtitle'] }}</p>

                        <div class="mt-4 space-y-3 flex-1">
                            <div>
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Benefits</h4>
                                <p class="text-sm text-slate-600">{{ $scholarship['benefits'] }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Coverage</h4>
                                <p class="text-sm text-slate-600">{{ $scholarship['coverage'] }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Eligibility</h4>
                                <p class="text-sm text-slate-600">{{ $scholarship['eligibility'] }}</p>
                            </div>
                        </div>

                        <a href="{{ $scholarship['learn_more_url'] ?? '#' }}"
                           class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-red-600 hover:text-red-700 transition-colors">
                            Learn More
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ SECTION 8: SUCCESS PHILOSOPHY ============================================ --}}
    <section class="py-20 lg:py-28 bg-red-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <svg class="w-12 h-12 text-red-300 mx-auto mb-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
            </svg>
            <blockquote class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white leading-relaxed">
                "{{ $quote['text'] ?? 'We don\'t just help students get admitted. We help them build successful futures in China.' }}"
            </blockquote>
            <p class="mt-6 text-red-200 text-lg font-medium">
                — {{ $quote['attribution'] ?? 'Naymur Rahman, Founder, BanglayChinese' }}
            </p>
        </div>
    </section>

    {{-- ============================================ SECTION 9: FAQS ============================================ --}}
    <section class="py-20 lg:py-28 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
                    {{ $faqs['heading'] ?? 'Frequently Asked Questions' }}
                </h2>
            </div>

            @php $faqItems = $faqs['items'] ?? []; @endphp
            <div class="space-y-4" x-data="{ open: null }">
                @foreach($faqItems as $index => $faq)
                    <div class="border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200"
                         :class="open === {{ $index }} ? 'shadow-md border-red-200' : ''">
                        <button @click="open = open === {{ $index }} ? null : {{ $index }}"
                                class="w-full flex items-center justify-between px-6 py-5 text-left bg-white hover:bg-slate-50 transition-colors">
                            <span class="text-base sm:text-lg font-semibold text-slate-900 pr-4">{{ $faq['question'] }}</span>
                            <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-200"
                                 :class="open === {{ $index }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $index }}"
                             x-collapse
                             x-cloak>
                            <div class="px-6 pb-5 text-slate-600 leading-relaxed">
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ SECTION 10: CONSULTATION BOOKING ============================================ --}}
    <section id="consultation" class="py-20 lg:py-28 bg-slate-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900">
                    {{ $booking['heading'] ?? 'Let\'s Plan Your Journey' }}
                </h2>
                <p class="mt-4 text-lg text-slate-600">
                    {{ $booking['subtitle'] ?? 'Fill out the form below and we\'ll reach out to schedule your free consultation. No commitment required — just honest, experienced guidance.' }}
                </p>
            </div>

            @if(session('success'))
                <div class="mb-8 bg-green-50 border border-green-200 rounded-2xl p-6 text-center">
                    <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-green-800 font-semibold text-lg">{{ session('success') }}</p>
                </div>
            @endif

            <form action="{{ route('study-in-china.consultation.store') }}" method="POST" class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 lg:p-10">
                @csrf

                <div class="space-y-6">
                    <div class="grid sm:grid-cols-2 gap-6">
                        {{-- Full Name --}}
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Full Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                                   placeholder="Your full name">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-700 mb-2">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                                   placeholder="+8801XXXXXXXXX">
                            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address *</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                               placeholder="your@email.com">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-6">
                        {{-- Current Qualification --}}
                        <div>
                            <label for="highest_qualification" class="block text-sm font-semibold text-slate-700 mb-2">Current Qualification *</label>
                            <select name="highest_qualification" id="highest_qualification" required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all">
                                <option value="">Select...</option>
                                <option value="SSC/O-Level" {{ old('highest_qualification') === 'SSC/O-Level' ? 'selected' : '' }}>SSC / O-Level</option>
                                <option value="HSC/A-Level" {{ old('highest_qualification') === 'HSC/A-Level' ? 'selected' : '' }}>HSC / A-Level</option>
                                <option value="Bachelor's" {{ old('highest_qualification') === 'Bachelor\'s' ? 'selected' : '' }}>Bachelor's Degree</option>
                                <option value="Master's" {{ old('highest_qualification') === 'Master\'s' ? 'selected' : '' }}>Master's Degree</option>
                                <option value="Diploma" {{ old('highest_qualification') === 'Diploma' ? 'selected' : '' }}>Diploma</option>
                            </select>
                            @error('highest_qualification') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Desired Degree --}}
                        <div>
                            <label for="desired_program" class="block text-sm font-semibold text-slate-700 mb-2">Desired Degree *</label>
                            <select name="desired_program" id="desired_program" required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all">
                                <option value="">Select...</option>
                                <option value="Bachelor's" {{ old('desired_program') === 'Bachelor\'s' ? 'selected' : '' }}>Bachelor's</option>
                                <option value="Master's" {{ old('desired_program') === 'Master\'s' ? 'selected' : '' }}>Master's</option>
                                <option value="PhD" {{ old('desired_program') === 'PhD' ? 'selected' : '' }}>PhD</option>
                                <option value="Language Program" {{ old('desired_program') === 'Language Program' ? 'selected' : '' }}>Language Program</option>
                                <option value="Non-degree" {{ old('desired_program') === 'Non-degree' ? 'selected' : '' }}>Non-degree / Exchange</option>
                            </select>
                            @error('desired_program') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-6">
                        {{-- Preferred Intake --}}
                        <div>
                            <label for="target_intake" class="block text-sm font-semibold text-slate-700 mb-2">Preferred Intake *</label>
                            <select name="target_intake" id="target_intake" required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all">
                                <option value="">Select...</option>
                                <option value="September 2026" {{ old('target_intake') === 'September 2026' ? 'selected' : '' }}>September 2026</option>
                                <option value="February/March 2027" {{ old('target_intake') === 'February/March 2027' ? 'selected' : '' }}>February/March 2027</option>
                                <option value="September 2027" {{ old('target_intake') === 'September 2027' ? 'selected' : '' }}>September 2027</option>
                                <option value="Other" {{ old('target_intake') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('target_intake') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Budget --}}
                        <div>
                            <label for="budget" class="block text-sm font-semibold text-slate-700 mb-2">Budget Range</label>
                            <select name="budget" id="budget"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all">
                                <option value="">Select...</option>
                                <option value="Under 2 Lakh BDT" {{ old('budget') === 'Under 2 Lakh BDT' ? 'selected' : '' }}>Under 2 Lakh BDT</option>
                                <option value="2-5 Lakh BDT" {{ old('budget') === '2-5 Lakh BDT' ? 'selected' : '' }}>2-5 Lakh BDT</option>
                                <option value="5-10 Lakh BDT" {{ old('budget') === '5-10 Lakh BDT' ? 'selected' : '' }}>5-10 Lakh BDT</option>
                                <option value="10+ Lakh BDT" {{ old('budget') === '10+ Lakh BDT' ? 'selected' : '' }}>10+ Lakh BDT</option>
                                <option value="Seeking Full Scholarship" {{ old('budget') === 'Seeking Full Scholarship' ? 'selected' : '' }}>Seeking Full Scholarship</option>
                            </select>
                            @error('budget') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Preferred Consultation Time --}}
                    <div>
                        <label for="preferred_consultation_time" class="block text-sm font-semibold text-slate-700 mb-2">Preferred Consultation Time</label>
                        <select name="preferred_consultation_time" id="preferred_consultation_time"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all">
                            <option value="">Select...</option>
                            <option value="Morning (9AM-12PM)" {{ old('preferred_consultation_time') === 'Morning (9AM-12PM)' ? 'selected' : '' }}>Morning (9AM - 12PM)</option>
                            <option value="Afternoon (12PM-4PM)" {{ old('preferred_consultation_time') === 'Afternoon (12PM-4PM)' ? 'selected' : '' }}>Afternoon (12PM - 4PM)</option>
                            <option value="Evening (4PM-8PM)" {{ old('preferred_consultation_time') === 'Evening (4PM-8PM)' ? 'selected' : '' }}>Evening (4PM - 8PM)</option>
                            <option value="Weekend" {{ old('preferred_consultation_time') === 'Weekend' ? 'selected' : '' }}>Weekend</option>
                        </select>
                        @error('preferred_consultation_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="message" class="block text-sm font-semibold text-slate-700 mb-2">Additional Message</label>
                        <textarea name="message" id="message" rows="4"
                                  class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition-all resize-none"
                                  placeholder="Tell us about your goals, questions, or anything else you'd like us to know...">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                            class="w-full px-8 py-4 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 shadow-lg shadow-red-200 hover:shadow-red-300 transition-all duration-300 text-lg">
                        Submit Consultation Request
                    </button>

                    <p class="text-center text-xs text-slate-400 mt-4">
                        By submitting, you agree to be contacted regarding your consultation request. We respect your privacy and will never share your information.
                    </p>
                </div>
            </form>
        </div>
    </section>

    {{-- ============================================ SECTION 11: FINAL CTA ============================================ --}}
    <section class="py-20 lg:py-28 bg-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">
                {{ $finalCta['heading'] ?? 'Your Dream University in China Starts Here' }}
            </h2>
            <p class="mt-6 text-xl text-slate-300">
                {{ $finalCta['subtitle'] ?? 'Book Your Free Consultation Today' }}
            </p>
            <a href="#consultation"
               class="mt-10 inline-flex items-center gap-3 px-10 py-5 bg-red-600 text-white font-bold rounded-2xl hover:bg-red-500 shadow-2xl shadow-red-600/30 transition-all duration-300 text-xl">
                {{ $finalCta['button_text'] ?? 'Book Free Consultation' }}
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </section>
</x-app-layout>
