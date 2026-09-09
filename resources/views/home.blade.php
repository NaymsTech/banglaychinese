{{-- resources/views/home.blade.php --}}
{{-- Phase 1: Hero + 3-card Service Grid (redesign, Nihao Bangladesh style) --}}

@php
    use App\Services\SettingsService;
    use App\Support\WhatsAppNumber;

    $waNumber = SettingsService::get('whatsapp_number', '8618223249514');

    // Editable homepage content lives in `settings` (Home Page CMS).
    $settings = $settings ?? [];

    // Resolve a stored image setting to a URL (file uploads live on the public disk).
    $cmsImage = fn (string $key, string $fallback): string => blank($settings[$key] ?? null)
        ? $fallback
        : (preg_match('~^https?://~i', (string) $settings[$key]) ? (string) $settings[$key] : asset('storage/' . $settings[$key]));

    // Emoji equivalents for the selectable CMS icons.
    $cmsIcons = [
        'video' => '🎥', 'school' => '🎓', 'clipboard' => '📋', 'book' => '📖', 'badge' => '🏅',
        'shield' => '🛡️', 'cash' => '💲', 'users' => '👥', 'globe' => '🌍', 'trophy' => '🏆',
        'briefcase' => '💼', 'chat' => '💬', 'rocket' => '🚀', 'star' => '⭐',
    ];
    $cmsIcon = fn (string $key, string $default): string => $cmsIcons[$settings[$key] ?? ''] ?? $default;

    // Headline lines: the last line gets the brand accent color.
    $heroLines = array_values(array_filter(
        array_map('trim', explode("\n", $settings['hero_title'] ?? '')),
        fn ($line) => $line !== ''
    ));

    // WhatsApp number used by the CTA band.
    $waCms = (string) ($settings['wa_number'] ?? '8618223249514');
    $waCmsLabel = WhatsAppNumber::display($waCms);
@endphp

<x-app-layout
    :metaTitle="$metaTitle ?? 'Banglay Chinese | Learn Chinese Live. Study in China. Unlock Your Future.'"
    :metaDescription="$metaDescription ?? 'বাংলায় চীনা ভাষা শিখুন + চীনে পড়াশোনার সম্পূর্ণ গাইডেন্স। লাইভ ব্যাচ, HSK প্রস্তুতি ও চায়না স্কলারশিপ সাপোর্ট — সব এক জায়গায়।'"
    :metaImage="$metaImage ?? null"
>
    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-200/50 via-emerald-50/30 to-white pb-32">
        {{-- Decorative soft blobs --}}
        <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-primary-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute top-1/2 -left-32 h-80 w-80 rounded-full bg-accent-100/50 blur-3xl"></div>
        {{-- Depth overlay --}}
        <div class="pointer-events-none absolute inset-0 bg-emerald-900/5"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">

                {{-- Left: Copy --}}
                <div class="text-center lg:text-left">
                    {{-- Live badge --}}
                    <span class="inline-flex items-center gap-2 rounded-full border border-primary-200 bg-white px-4 py-1.5 text-sm font-semibold text-primary-800 shadow-sm">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-500 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-accent-600"></span>
                        </span>
                        {{ $settings['hero_badge'] }}
                    </span>

                    <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-text font-display sm:text-5xl lg:text-6xl">
                        @foreach ($heroLines as $index => $line)
                            @if ($index > 0)<br>@endif
                            @if ($index === count($heroLines) - 1)<span class="text-primary-600">@endif{{ $line }}@if ($index === count($heroLines) - 1)</span>@endif
                        @endforeach
                    </h1>

                    <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-slate-500 font-bangla sm:text-xl lg:mx-0">
                        {{ $settings['hero_subtitle'] }}
                    </p>

                    {{-- CTAs --}}
                    <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center lg:justify-start">
                        <a href="{{ $settings['hero_cta1_link'] }}"
                           class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary-600 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-primary-600/25 transition hover:-translate-y-0.5 hover:bg-primary-700 sm:w-auto">
                            {{ $settings['hero_cta1_text'] }}
                            <svg class="h-5 w-5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="{{ $settings['hero_cta2_link'] }}"
                           target="_blank" rel="noopener"
                           class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent-600 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-accent-600/25 transition hover:-translate-y-0.5 hover:bg-accent-700 sm:w-auto">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            {{ $settings['hero_cta2_text'] }}
                        </a>
                    </div>
                </div>

                {{-- Right: Photo --}}
                <div class="relative">
                    <div class="overflow-hidden rounded-3xl shadow-2xl shadow-primary-900/15 ring-1 ring-primary-100">
                        <img
                            src="{{ $cmsImage('hero_image', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&auto=format&fit=crop') }}"
                            alt="Bangladeshi students learning Chinese together in a classroom"
                            class="aspect-[4/3] w-full object-cover"
                            loading="eager"
                        >
                    </div>

                    {{-- Floating trust chip --}}
                    <div class="absolute -bottom-5 left-4 flex items-center gap-3 rounded-2xl border border-primary-100 bg-white px-5 py-3.5 shadow-xl sm:left-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-600 text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <p class="text-lg font-extrabold leading-none text-text">৫০০+</p>
                            <p class="mt-1 text-xs font-medium text-slate-500 font-bangla">শিক্ষার্থী চীনে যাওয়ার পথে</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== 3-CARD SERVICE GRID ===== --}}
    <section class="bg-surface-alt pb-20 sm:pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative z-20 -mt-40 grid gap-8 md:grid-cols-2 lg:grid-cols-3">

                {{-- Card 1: Live Batches --}}
                <div class="group rounded-2xl border border-white/60 bg-surface p-8 shadow-xl transition duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-primary-900/10">
                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 ring-1 ring-primary-100 transition group-hover:bg-primary-600 group-hover:text-white">
                        <span class="text-2xl leading-none">{{ $cmsIcon('card_1_icon', '🎥') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-text font-display">{{ $settings['card_1_title'] }}</h3>
                    <p class="mt-2 text-sm font-medium text-primary-700 font-bangla">লাইভ ক্লাস · VooV / Zoom</p>
                    <p class="mt-3 text-base leading-relaxed text-slate-500">{{ $settings['card_1_desc'] }}</p>
                    <a href="{{ $settings['card_1_link'] }}" class="mt-6 inline-flex items-center gap-1.5 font-bold text-primary-700 transition group-hover:text-primary-800">
                        Learn More
                        <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>

                {{-- Card 2: Study in China Consultancy --}}
                <div class="group rounded-2xl border border-white/60 bg-surface p-8 shadow-xl transition duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-primary-900/10">
                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-50 text-accent-600 ring-1 ring-accent-100 transition group-hover:bg-accent-600 group-hover:text-white">
                        <span class="text-2xl leading-none">{{ $cmsIcon('card_2_icon', '🎓') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-text font-display">{{ $settings['card_2_title'] }}</h3>
                    <p class="mt-2 text-sm font-medium text-primary-700 font-bangla">ইউনিভার্সিটি অ্যাপ্লিকেশন ও স্কলারশিপ</p>
                    <p class="mt-3 text-base leading-relaxed text-slate-500">{{ $settings['card_2_desc'] }}</p>
                    <a href="{{ $settings['card_2_link'] }}" class="mt-6 inline-flex items-center gap-1.5 font-bold text-primary-700 transition group-hover:text-primary-800">
                        Learn More
                        <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>

                {{-- Card 3: CSCA Exam Preparation --}}
                <div class="group relative rounded-2xl border border-white/60 bg-surface p-8 shadow-xl transition duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-primary-900/10">
                    {{-- Coming soon badge --}}
                    <span class="absolute right-4 top-4 inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Coming Soon</span>
                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 ring-1 ring-amber-100">
                        <span class="text-2xl leading-none">{{ $cmsIcon('card_3_icon', '📋') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-text font-display">{{ $settings['card_3_title'] }}</h3>
                    <p class="mt-2 text-sm font-medium text-primary-700 font-bangla">চীনা স্কলারশিপ অ্যাসেসমেন্ট প্রস্তুতি</p>
                    <p class="mt-3 text-base leading-relaxed text-slate-500">{{ $settings['card_3_desc'] }}</p>
                    {{-- TODO: enable once the CSCA prep program ships --}}
                    <a href="#" class="mt-6 inline-flex items-center gap-1.5 font-bold text-slate-400 transition cursor-not-allowed" aria-disabled="true">
                        Coming Soon
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== OUR POPULAR COURSES ===== --}}
    <section id="home-courses" class="scroll-mt-24 bg-gray-50/50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 text-center">
                <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-emerald-700">Learning Programs</p>
                <h2 class="text-3xl font-bold text-gray-900 md:text-4xl">Our Popular Courses</h2>
                <p class="mx-auto mt-3 max-w-2xl text-lg text-gray-500">সরাসরি চীন থেকে মেন্টরশিপ — স্কলারশিপ, HSK, স্পিকিং কিংবা কিডস — যেটাই হোক, সঠিক প্রোগ্রাম এখানেই আছে।</p>
            </div>

            @if($homeCourses->isEmpty())
                <p class="py-16 text-center text-slate-400">কোনো কোর্স পাওয়া যায়নি। শীঘ্রই নতুন কোর্স আসছে!</p>
            @else
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($homeCourses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>

                <div class="mt-12 text-center">
                    <a href="{{ route('courses.index') }}"
                       class="inline-flex items-center gap-2 rounded-full border-2 border-emerald-700 px-8 py-3.5 text-base font-bold text-emerald-700 transition hover:bg-emerald-700 hover:text-white">
                        View All Courses
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ===== WHY CHOOSE US ===== --}}
    <section class="bg-white pt-32 pb-20 md:pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-4 text-center text-3xl font-bold text-gray-900 md:text-4xl">Why Choose Banglay Chinese?</h2>
            <p class="mb-16 text-center text-xl text-gray-600 font-bangla">আপনার স্বপ্ন পূরণে আমরা কেন সেরা</p>

            <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                        <span class="text-2xl leading-none">{{ $cmsIcon('why_1_icon', '🏅') }}</span>
                    </div>
                    <h3 class="mb-2 text-center text-lg font-bold text-gray-900">{{ $settings['why_1_title'] }}</h3>
                    <p class="text-center text-sm leading-relaxed text-gray-600">{{ $settings['why_1_desc'] }}</p>
                </div>

                {{-- Item 2: 95% Visa Success --}}
                <div>
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                        <span class="text-2xl leading-none">{{ $cmsIcon('why_2_icon', '🛡️') }}</span>
                    </div>
                    <h3 class="mb-2 text-center text-lg font-bold text-gray-900">{{ $settings['why_2_title'] }}</h3>
                    <p class="text-center text-sm leading-relaxed text-gray-600">{{ $settings['why_2_desc'] }}</p>
                </div>

                {{-- Item 3: Affordable Pricing --}}
                <div>
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                        <span class="text-2xl leading-none">{{ $cmsIcon('why_3_icon', '💲') }}</span>
                    </div>
                    <h3 class="mb-2 text-center text-lg font-bold text-gray-900">{{ $settings['why_3_title'] }}</h3>
                    <p class="text-center text-sm leading-relaxed text-gray-600">{{ $settings['why_3_desc'] }}</p>
                </div>

                {{-- Item 4: Complete Support --}}
                <div>
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                        <span class="text-2xl leading-none">{{ $cmsIcon('why_4_icon', '👥') }}</span>
                    </div>
                    <h3 class="mb-2 text-center text-lg font-bold text-gray-900">{{ $settings['why_4_title'] }}</h3>
                    <p class="text-center text-sm leading-relaxed text-gray-600">{{ $settings['why_4_desc'] }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== WHATSAPP CTA ===== --}}
    <section class="bg-gray-50 py-16 md:py-20">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-12 text-center shadow-lg md:p-16">
                {{-- Decorative WhatsApp icon --}}
                <svg class="pointer-events-none absolute -bottom-10 -right-10 h-56 w-56 rotate-12 text-emerald-700/10" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                {{-- Soft teal glow --}}
                <div class="pointer-events-none absolute -left-16 -top-16 h-40 w-40 rounded-full bg-teal-200/40 blur-3xl"></div>

                <div class="relative">
                    <span class="mb-5 inline-block rounded-full bg-emerald-100 px-4 py-1 text-sm font-semibold text-emerald-800">⚡ Quick Response</span>
                    <h2 class="mb-4 text-3xl font-bold text-gray-900 font-bangla md:text-4xl">{{ $settings['wa_heading'] }}</h2>
                    <p class="mx-auto mb-9 max-w-2xl text-lg leading-relaxed text-gray-600 font-bangla">{{ $settings['wa_subtext'] }}</p>
                    <a href="https://wa.me/{{ $waCms }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-full bg-green-600 px-10 py-4 text-lg font-bold text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:scale-105 hover:bg-green-700 hover:shadow-xl">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp {{ $waCmsLabel }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SERVICES ===== --}}
    <section class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Section header --}}
            <p class="mb-2 text-center text-sm font-semibold uppercase tracking-wider text-emerald-700">What We Do</p>
            <h2 class="mb-16 text-center text-3xl font-bold text-gray-900 md:text-4xl">
                <span class="block">Everything You Need,</span>
                <span class="block text-emerald-700">Under One Roof</span>
            </h2>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                {{-- Service 1: Chinese Language Courses --}}
                <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-shadow duration-300 hover:shadow-lg">
                    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">Chinese Language Courses</h3>
                    <p class="mb-4 text-sm leading-relaxed text-gray-600">HSK 1-4 preparation with expert instructors, small batches, and proven methods to achieve fluency.</p>
                    <a href="{{ route('courses.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Learn more →</a>
                </div>

                {{-- Service 2: Study in China Consultancy --}}
                <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-shadow duration-300 hover:shadow-lg">
                    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">Study in China Consultancy</h3>
                    <p class="mb-4 text-sm leading-relaxed text-gray-600">End-to-end university application support — from shortlisting to offer letter management.</p>
                    <a href="{{ route('study-in-china') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Learn more →</a>
                </div>

                {{-- Service 3: Visa Assistance --}}
                <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-shadow duration-300 hover:shadow-lg">
                    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12"/></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">Visa Assistance</h3>
                    <p class="mb-4 text-sm leading-relaxed text-gray-600">Complete visa guidance for China with 95% success rate and expert documentation support.</p>
                    <a href="{{ route('study-in-china') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Learn more →</a>
                </div>

                {{-- Service 4: Mentorship Program --}}
                <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-shadow duration-300 hover:shadow-lg">
                    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">Mentorship Program</h3>
                    <p class="mb-4 text-sm leading-relaxed text-gray-600">One-on-one mentorship sessions with experienced professionals who've studied in China.</p>
                    <a href="{{ route('study-in-china') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Learn more →</a>
                </div>

                {{-- Service 5: Scholarship Support --}}
                <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-shadow duration-300 hover:shadow-lg">
                    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0"/></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">Scholarship Support</h3>
                    <p class="mb-4 text-sm leading-relaxed text-gray-600">Identify and apply for merit and need-based scholarships that match your academic profile.</p>
                    <a href="{{ route('study-in-china') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Learn more →</a>
                </div>

                {{-- Service 6: Career Counseling --}}
                <div class="rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-shadow duration-300 hover:shadow-lg">
                    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z"/></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">Career Counseling</h3>
                    <p class="mb-4 text-sm leading-relaxed text-gray-600">Post-study career guidance to help you leverage your Chinese education for global opportunities.</p>
                    <a href="{{ route('study-in-china.consultation') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Learn more →</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FOUNDER STORY ===== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-emerald-950 to-gray-900 py-20 md:py-32">
        {{-- Subtle dot-grid overlay --}}
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-6">
            {{-- Section heading --}}
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-white md:text-4xl">{{ $settings['mentor_heading'] }}</h2>
                @if(! blank($settings['mentor_subtitle']))
                    <p class="mt-3 text-lg text-emerald-300">{{ $settings['mentor_subtitle'] }}</p>
                @endif
            </div>

            <div class="grid items-center gap-12 md:grid-cols-2">

                {{-- Left: Text --}}
                <div>
                    {{-- Large Bengali headline --}}
                    @php
                        $founderQuote = trim((string) $settings['founder_quote']);
                        $founderQuoteHtml = preg_replace('/\s+(\S+)$/u', ' <span class="text-red-600">$1</span>', e($founderQuote));
                    @endphp
                    <div class="mb-8">
                        <span class="mb-2 block text-6xl font-serif leading-none text-red-600">&ldquo;</span>
                        <h2 class="text-3xl font-bold leading-tight text-white font-bangla md:text-4xl lg:text-5xl">{!! $founderQuoteHtml !!}</h2>
                    </div>

                    {{-- Paragraph (RichEditor HTML from the Home Page CMS) --}}
                    <div class="mt-8 text-lg leading-relaxed text-gray-300 font-bangla">{!! $settings['founder_bio'] !!}</div>

                    {{-- Founder info --}}
                    <div class="mt-10 border-t border-gray-700 pt-8">
                        <div class="text-lg font-bold text-white font-bangla">{{ $settings['founder_name'] }}</div>
                        <div class="mt-1 text-sm font-semibold uppercase tracking-wider text-emerald-500">{{ $settings['founder_role'] }}</div>
                        <a href="{{ $settings['founder_cta_link'] }}" class="mt-6 inline-block rounded-lg border-2 border-emerald-500 px-6 py-3 font-semibold text-emerald-500 transition-all duration-300 hover:bg-emerald-500 hover:text-white">
                            {{ $settings['founder_cta_text'] }} →
                        </a>
                    </div>
                </div>

                {{-- Right: Photo --}}
                <div class="relative mx-auto max-w-md">
                    {{-- Corner brackets (camera viewfinder frame) --}}
                    <div class="absolute -left-4 -top-4 h-8 w-8 border-l-2 border-t-2 border-emerald-600"></div>
                    <div class="absolute -right-4 -top-4 h-8 w-8 border-r-2 border-t-2 border-emerald-600"></div>
                    <div class="absolute -bottom-4 -left-4 h-8 w-8 border-b-2 border-l-2 border-emerald-600"></div>
                    <div class="absolute -bottom-4 -right-4 h-8 w-8 border-b-2 border-r-2 border-emerald-600"></div>

                    <img
                        src="{{ $cmsImage('founder_image', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=600&auto=format&fit=crop') }}"
                        alt="Founder of Banglay Chinese"
                        class="w-full rounded-lg shadow-2xl"
                        loading="lazy"
                    >
                </div>
            </div>
        </div>
    </section>

    {{-- ===== AWARDS & RECOGNITION ===== --}}
    <section class="bg-gray-50 py-20">
        <div class="mx-auto max-w-7xl px-6">

            {{-- Section header --}}
            @php
                $awardWords = preg_split('/\s+/u', trim((string) $settings['award_heading']), -1, PREG_SPLIT_NO_EMPTY) ?: [''];
                $awardLead = implode(' ', array_slice($awardWords, 0, -1));
                $awardLast = (string) end($awardWords);
            @endphp
            <div class="mb-16 text-center">
                <div class="mb-4 inline-block border-2 border-emerald-700 px-4 py-2 text-sm font-bold tracking-wider text-emerald-700">{{ $settings['award_eyebrow'] }}</div>
                <h2 class="text-3xl font-bold md:text-4xl">
                    @if($awardLead)<span class="text-gray-900">{{ $awardLead }}</span> @endif<span class="text-emerald-700">{{ $awardLast }}</span>
                </h2>
            </div>

            {{-- Main award card --}}
            <div class="overflow-hidden rounded-xl border-l-4 border-emerald-600 bg-white shadow-xl">
                <div class="grid md:grid-cols-2">
                    {{-- Left: Award / recognition photo (uploaded via the Home Page CMS) --}}
                    <div class="flex items-center justify-center bg-gradient-to-br from-gray-900 via-emerald-950 to-gray-900 p-12">
                        <img
                            src="{{ $cmsImage('award_image', 'https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?w=800&auto=format&fit=crop') }}"
                            alt="Award certificate or stage performance"
                            class="w-full max-w-sm rounded-lg shadow-2xl"
                            loading="lazy"
                        >
                    </div>

                    {{-- Right: Award details --}}
                    <div class="p-10">
                        <span class="mb-3 block text-sm font-bold uppercase tracking-wider text-emerald-700">{{ $settings['award_subheading'] }}</span>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 md:text-3xl">{{ $settings['award_title'] }}</h3>
                        {{-- RichEditor HTML from the Home Page CMS --}}
                        <div class="mb-6 leading-relaxed text-gray-600 font-bangla">{!! $settings['award_description'] !!}</div>

                        {{-- Quote box --}}
                        <div class="border-l-4 border-emerald-600 bg-emerald-50 p-6">
                            <p class="leading-relaxed text-gray-700 font-bangla">{{ $settings['award_quote'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== GET IN TOUCH / LEAD FORM ===== --}}
    @php
        $contactEmail = SettingsService::get('contact_email', 'info@banglaychinese.com');
        $contactAddress = SettingsService::get('physical_address', 'Chongqing, China');
        $contactPhone = WhatsAppNumber::display($waNumber);
    @endphp
    <section id="contact" class="scroll-mt-24 bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-950 py-20 md:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-20">

                {{-- Left: pitch + contact info --}}
                <div>
                    <h2 class="text-3xl font-bold leading-tight text-white md:text-4xl">Ready to Start Your Journey?</h2>
                    <p class="mt-4 max-w-xl text-lg leading-relaxed text-emerald-200">
                        আমাদের গাইডেন্সে ১০০০+ শিক্ষার্থী চীনা ভাষা শিখেছে ও চীনে পড়ার পথে এগিয়েছে। আপনার গল্পটাও শুরু হোক আজই।
                    </p>

                    <div class="mt-10 space-y-5">
                        <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wider text-emerald-400">Phone / WhatsApp</p>
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="mt-1 block text-lg font-bold text-white transition hover:text-emerald-300">{{ $contactPhone }}</a>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wider text-emerald-400">Email</p>
                                <a href="mailto:{{ $contactEmail }}" class="mt-1 block text-lg font-bold text-white transition hover:text-emerald-300">{{ $contactEmail }}</a>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm4.5 0c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wider text-emerald-400">Address</p>
                                <p class="mt-1 text-lg font-bold text-white">{{ $contactAddress }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: consultation form --}}
                <div class="rounded-3xl bg-white p-8 shadow-2xl sm:p-10">
                    <h3 class="text-2xl font-bold text-gray-900">Book Free Consultation</h3>
                    <p class="mt-2 text-gray-500">Quick form — our team will reach out within 24 hours.</p>

                    <div class="mt-6">
                        <x-form-feedback />
                        <x-form-feedback type="error" />
                    </div>

                    <form method="POST" action="{{ route('contact.lead') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="lead-name" class="mb-1.5 block text-sm font-bold text-gray-700">Name <span class="text-red-500">*</span></label>
                            <input type="text" id="lead-name" name="name" value="{{ old('name') }}" required autocomplete="name"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('name') border-red-400 @enderror"
                                   placeholder="Your full name">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="lead-phone" class="mb-1.5 block text-sm font-bold text-gray-700">WhatsApp Number <span class="text-red-500">*</span></label>
                            <input type="tel" id="lead-phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('phone') border-red-400 @enderror"
                                   placeholder="01XXXXXXXXX or +8801XXXXXXXXX">
                            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="lead-email" class="mb-1.5 block text-sm font-bold text-gray-700">Email <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                            <input type="email" id="lead-email" name="email" value="{{ old('email') }}" autocomplete="email"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('email') border-red-400 @enderror"
                                   placeholder="you@example.com">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="lead-service" class="mb-1.5 block text-sm font-bold text-gray-700">Service <span class="text-red-500">*</span></label>
                            <select id="lead-service" name="service" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('service') border-red-400 @enderror">
                                <option value="" disabled {{ old('service') ? '' : 'selected' }}>What are you interested in?</option>
                                @foreach(['Study in China', 'Courses', 'Digital Products', 'Mentorship', 'General Inquiry'] as $option)
                                    <option value="{{ $option }}" {{ old('service') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('service')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit"
                                class="w-full rounded-full bg-emerald-700 px-6 py-4 text-base font-bold text-white shadow-xl shadow-emerald-700/25 transition hover:bg-emerald-800">
                            Submit Inquiry
                        </button>

                        <p class="text-center text-xs text-gray-400">
                            Want to write a longer message? Use the
                            <a href="{{ route('contact') }}" class="font-semibold text-emerald-700 hover:underline">contact page</a>.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FINAL CTA ===== --}}
    <section class="bg-gradient-to-r from-emerald-800 to-emerald-900 px-6 py-24">
        <div class="mx-auto max-w-4xl text-center">
            <h2 class="text-3xl font-bold text-white font-display md:text-5xl">{{ $settings['final_heading'] }}</h2>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-emerald-100">
                {{ $settings['final_subtext'] }}
            </p>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ $settings['final_btn1_link'] }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 text-lg font-bold text-emerald-900 shadow-xl shadow-black/20 transition-transform duration-300 hover:scale-105">
                    <svg class="h-6 w-6 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    {{ $settings['final_btn1_text'] }}
                </a>
                <a href="{{ $settings['final_btn2_link'] }}"
                   class="inline-flex items-center rounded-full border-2 border-white px-8 py-4 text-lg font-bold text-white transition-colors duration-300 hover:bg-white hover:text-emerald-900">
                    {{ $settings['final_btn2_text'] }}
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
