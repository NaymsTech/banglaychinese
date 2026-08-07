<x-app-layout>
    @section('title', 'About Us - BanglayChinese')

    {{-- ==================== HERO SECTION ==================== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-950 via-primary-900 to-primary-800 text-white">
        {{-- Decorative elements --}}
        <div class="pointer-events-none absolute -top-20 -right-20 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/4 h-64 w-64 rounded-full bg-primary-500/10 blur-2xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 sm:py-24 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                {{-- Image Column --}}
                <div class="relative mx-auto max-w-sm lg:max-w-none">
                    <div class="aspect-[3/4] overflow-hidden rounded-3xl bg-primary-700/30 ring-2 ring-white/10 shadow-2xl">
                        @if(!empty($hero_image))
                            <img src="{{ asset('storage/' . $hero_image) }}" alt="{{ $hero_name ?? 'Founder' }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-600 to-primary-800 text-8xl">
                                👤
                            </div>
                        @endif
                    </div>
                    {{-- Decorative accent --}}
                    <div class="absolute -bottom-4 -right-4 -z-10 h-28 w-28 rounded-2xl bg-accent-500/20 blur-xl"></div>
                    <div class="absolute -top-4 -left-4 -z-10 h-20 w-20 rounded-2xl bg-primary-400/20 blur-lg"></div>
                </div>

                {{-- Content Column --}}
                <div class="text-center lg:text-left">
                    <span class="inline-flex items-center gap-2 rounded-full border border-primary-400/20 bg-primary-500/15 px-4 py-1.5 text-xs font-semibold tracking-wider text-primary-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                        {{ $hero_badge ?? 'ABOUT US' }}
                    </span>

                    <h1 class="mt-6 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                        {{ $hero_heading ?? 'Meet the Founder' }}
                    </h1>

                    <p class="mt-4 font-display text-4xl font-extrabold text-primary-300 sm:text-5xl lg:text-6xl">
                        {{ $hero_name ?? 'Md. Naymur Rahman' }}
                    </p>

                    <p class="mt-3 text-lg font-medium text-primary-200/80">
                        {{ $hero_title ?? 'Founder & Lead Instructor' }}
                    </p>

                    {{-- Highlight Cards --}}
                    @if(!empty($hero_highlights))
                        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach($hero_highlights as $highlight)
                                <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur-sm transition hover:bg-white/10 hover:-translate-y-0.5 duration-200">
                                    <span class="flex-shrink-0 text-xl">{{ $highlight['icon'] ?? '✦' }}</span>
                                    <span class="text-sm leading-tight text-white/90">{{ $highlight['text'] ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- CTA Buttons --}}
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row justify-center lg:justify-start">
                        @if(!empty($hero_cta_primary_text))
                            <a href="{{ $hero_cta_primary_url ?? '/courses' }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-accent-600/25 transition hover:bg-accent-500 duration-200">
                                {{ $hero_cta_primary_text }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @endif
                        @if(!empty($hero_cta_secondary_text))
                            <a href="{{ $hero_cta_secondary_url ?? '/study-in-china/consultation' }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20 hover:border-white/30 duration-200">
                                {{ $hero_cta_secondary_text }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== STORY SECTION ==================== --}}
    @if(!empty($story_content))
    <section class="relative bg-white py-16 sm:py-24">
        {{-- Subtle top gradient border --}}
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-300/30 to-transparent"></div>

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-700">আমার গল্প</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $story_heading_bn ?? 'আমার গল্প' }}
                </h2>
                <p class="mt-3 text-lg text-primary-700/70">
                    {{ $story_subheading ?? 'শূন্য থেকে চীনে — একটি বাস্তব যাত্রা' }}
                </p>
            </div>

            <div class="relative mt-10 rounded-3xl border border-slate-100 bg-white p-8 shadow-lg shadow-slate-200/50 sm:p-12">
                {{-- Left accent bar --}}
                <div class="absolute inset-y-6 left-0 w-1 rounded-r-full bg-primary-500/30"></div>

                @php $storyParagraphs = explode("\n\n", $story_content); @endphp
                <div class="space-y-5 text-base leading-relaxed text-slate-600 sm:text-lg">
                    @foreach($storyParagraphs as $paragraph)
                        @php $paragraph = trim($paragraph); @endphp
                        @if(!empty($paragraph))
                            <p>{{ $paragraph }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== TIMELINE SECTION ==================== --}}
    @if(!empty($timeline_items))
    <section class="relative bg-primary-50 py-16 sm:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-700">My Journey</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $timeline_heading ?? 'My Journey' }}
                </h2>
            </div>

            <div class="relative mt-14">
                {{-- Vertical line --}}
                <div class="absolute left-4 sm:left-1/2 top-0 bottom-0 w-px bg-gradient-to-b from-primary-500/40 via-primary-400/20 to-primary-300/10"></div>

                <div class="space-y-8">
                    @foreach($timeline_items as $index => $item)
                        <div class="relative flex items-start gap-6 sm:gap-8 {{ $index % 2 == 0 ? 'sm:flex-row' : 'sm:flex-row-reverse sm:text-right' }}">
                            {{-- Dot --}}
                            <div class="absolute left-2.5 sm:left-1/2 sm:-translate-x-1/2 z-10 flex h-4 w-4 items-center justify-center mt-1.5">
                                <div class="h-3 w-3 rounded-full bg-primary-500 ring-4 ring-primary-50"></div>
                            </div>

                            {{-- Content --}}
                            <div class="ml-10 sm:ml-0 sm:w-[calc(50%-2rem)] {{ $index % 2 == 0 ? 'sm:pr-8' : 'sm:pl-8' }}">
                                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-md shadow-slate-200/50 transition hover:-translate-y-1 hover:shadow-lg duration-300 sm:p-6">
                                    <span class="inline-block rounded-full bg-primary-100 px-3 py-1 text-xs font-bold text-primary-800">
                                        {{ $item['year'] ?? '' }}
                                    </span>
                                    <h3 class="mt-3 text-lg font-bold text-slate-900">
                                        {{ $item['title'] ?? '' }}
                                    </h3>
                                    @if(!empty($item['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                            {{ $item['description'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== EXPERIENCE SECTION ==================== --}}
    @if(!empty($experience_cards))
    <section class="relative bg-white py-16 sm:py-24">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-300/30 to-transparent"></div>

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-700">Recognition</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $experience_heading ?? 'Experience & Recognition' }}
                </h2>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach($experience_cards as $card)
                    <div class="group rounded-3xl border border-slate-100 bg-white p-8 shadow-lg shadow-slate-200/50 transition hover:-translate-y-1 hover:shadow-xl duration-300">
                        <div class="mb-1 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-3xl">
                            {{ $card['icon'] ?? '🏆' }}
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $card['title'] ?? '' }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-500">{{ $card['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== WHY SECTION ==================== --}}
    @if(!empty($why_content))
    <section class="relative bg-gradient-to-br from-primary-900 to-primary-800 py-16 sm:py-24">
        {{-- Decorative elements --}}
        <div class="pointer-events-none absolute top-0 right-0 h-72 w-72 rounded-full bg-primary-500/5 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 h-64 w-64 rounded-full bg-accent-500/5 blur-3xl"></div>

        <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-primary-400/20 bg-primary-500/10 px-4 py-1.5 text-xs font-semibold text-primary-300">
                💡 Behind the Platform
            </span>
            <h2 class="mt-4 font-display text-3xl font-extrabold text-white sm:text-4xl">
                {{ $why_heading ?? 'Why did I build this platform?' }}
            </h2>

            <div class="relative mt-10 rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm sm:p-12">
                {{-- Left accent --}}
                <div class="absolute inset-y-6 left-0 w-1 rounded-r-full bg-primary-400/40"></div>

                @php $whyParagraphs = explode("\n\n", $why_content); @endphp
                <div class="space-y-5 text-base leading-relaxed text-primary-50/90 sm:text-lg">
                    @foreach($whyParagraphs as $paragraph)
                        @php $paragraph = trim($paragraph); @endphp
                        @if(!empty($paragraph))
                            <p>{{ $paragraph }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== MISSION SECTION ==================== --}}
    @if(!empty($mission_cards))
    <section class="relative bg-white py-16 sm:py-24">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-300/30 to-transparent"></div>

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-700">Our Purpose</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $mission_heading ?? 'Our Mission' }}
                </h2>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($mission_cards as $card)
                    <div class="group rounded-3xl border border-slate-100 bg-white p-6 shadow-lg shadow-slate-200/50 transition hover:-translate-y-1 hover:shadow-xl duration-300 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-2xl">
                            {{ $card['icon'] ?? '🎯' }}
                        </div>
                        <h3 class="text-base font-bold text-slate-900">{{ $card['title'] ?? '' }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $card['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== COMMITMENT SECTION ==================== --}}
    @if(!empty($commitment_cards))
    <section class="relative bg-primary-50 py-16 sm:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-700">We Promise</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $commitment_heading ?? 'Our Commitment' }}
                </h2>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($commitment_cards as $card)
                    <div class="group rounded-3xl border border-primary-100 bg-white p-6 shadow-md shadow-primary-100/30 transition hover:-translate-y-1 hover:shadow-lg duration-300 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-2xl">
                            {{ $card['icon'] ?? '🤝' }}
                        </div>
                        <h3 class="text-base font-bold text-slate-900">{{ $card['title'] ?? '' }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $card['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>

            @if(!empty($commitment_quote))
                <p class="mt-10 text-center font-display text-xl italic text-primary-700 font-semibold">
                    "{{ $commitment_quote }}"
                </p>
            @endif
        </div>
    </section>
    @endif

    {{-- ==================== VISION SECTION ==================== --}}
    @if(!empty($vision_content))
    <section class="relative bg-white py-16 sm:py-24">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-300/30 to-transparent"></div>

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-sm font-bold uppercase tracking-widest text-primary-700">Looking Ahead</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl">
                {{ $vision_heading ?? 'Our Vision' }}
            </h2>

            <div class="relative mt-10 overflow-hidden rounded-3xl border-2 border-primary-200 bg-gradient-to-br from-primary-50 to-white p-8 shadow-lg sm:p-12">
                {{-- Top accent bar --}}
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-500 via-accent-500 to-primary-500"></div>

                @php $visionParagraphs = explode("\n\n", $vision_content); @endphp
                <div class="space-y-4 text-base leading-relaxed text-slate-600 sm:text-lg">
                    @foreach($visionParagraphs as $paragraph)
                        @php $paragraph = trim($paragraph); @endphp
                        @if(!empty($paragraph))
                            <p>{{ $paragraph }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== WHY CHOOSE US SECTION ==================== --}}
    @if(!empty($choose_us_cards))
    <section class="relative bg-slate-50 py-16 sm:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-700">The Difference</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $choose_us_heading ?? 'Why Choose Us' }}
                </h2>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($choose_us_cards as $card)
                    <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/50 transition hover:-translate-y-1 hover:shadow-lg hover:border-primary-200 duration-300">
                        <div class="mb-1 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-accent-50 text-xl">
                            {{ $card['icon'] ?? '⭐' }}
                        </div>
                        <h3 class="mt-3 text-base font-bold text-slate-900">{{ $card['title'] ?? '' }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $card['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>

            @if(!empty($choose_us_quote))
                <p class="mt-10 text-center font-display text-xl italic text-primary-700 font-semibold">
                    "{{ $choose_us_quote }}"
                </p>
            @endif
        </div>
    </section>
    @endif

    {{-- ==================== FAQ SECTION ==================== --}}
    @if(!empty($faq_items))
    <section class="relative bg-white py-16 sm:py-24" x-data="{ activeFaq: null }">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-300/30 to-transparent"></div>

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-bold uppercase tracking-widest text-primary-700">FAQ</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $faq_heading ?? 'Frequently Asked Questions' }}
                </h2>
            </div>

            <div class="mt-12 space-y-4">
                @foreach($faq_items as $index => $faq)
                    <div class="rounded-2xl border border-slate-200 bg-white transition hover:border-primary-300 shadow-sm">
                        <button
                            @click="activeFaq = activeFaq === {{ $index }} ? null : {{ $index }}"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
                            :aria-expanded="activeFaq === {{ $index }} ? 'true' : 'false'"
                        >
                            <span class="text-base font-bold text-slate-900 sm:text-lg">{{ $faq['question'] ?? '' }}</span>
                            <svg
                                class="h-5 w-5 flex-shrink-0 text-primary-600 transition-transform duration-300"
                                :class="activeFaq === {{ $index }} ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div
                            x-show="activeFaq === {{ $index }}"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-collapse
                        >
                            <div class="px-6 pb-5">
                                <p class="text-sm leading-relaxed text-slate-500 sm:text-base">{{ $faq['answer'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== FINAL CTA ==================== --}}
    <section class="bg-gradient-to-br from-primary-900 to-primary-950 py-16 sm:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
            <div class="rounded-3xl bg-gradient-to-br from-primary-800/60 to-primary-900/40 border border-primary-400/20 p-10 shadow-2xl sm:p-16">
                <span class="inline-flex items-center gap-2 rounded-full border border-accent-500/40 bg-accent-500/10 px-4 py-1.5 text-sm font-semibold text-accent-400">
                    🔥 {{ $cta_heading ?? 'Start Your Chinese Journey Today' }}
                </span>

                <h2 class="mt-6 font-display text-3xl font-extrabold text-white sm:text-5xl tracking-tight">
                    {{ $cta_heading ?? 'Start Your Chinese Journey Today' }}
                </h2>
                <p class="mt-4 text-lg text-primary-200/80">
                    Join hundreds of Bangladeshi students learning Chinese the right way.
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row justify-center">
                    @if(!empty($cta_primary_text))
                        <a href="{{ $cta_primary_url ?? '/courses' }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent-600 px-8 py-4 text-base font-bold text-white shadow-xl shadow-accent-600/30 transition hover:bg-accent-500 duration-200">
                            {{ $cta_primary_text }}
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    @endif
                    @if(!empty($cta_secondary_text))
                        <a href="{{ $cta_secondary_url ?? '/study-in-china/consultation' }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition hover:bg-white/20 hover:border-white/30 duration-200">
                            {{ $cta_secondary_text }}
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
