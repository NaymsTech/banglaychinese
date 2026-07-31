<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $metaTitle ?? config('app.name', 'Banglay Chinese') }} | Banglay Chinese</title>

    <meta name="description" content="{{ $metaDescription ?? 'Banglay Chinese — Best learn Chinese for Bangladeshi students. HSK 1–4 preparation, live speaking classes, and China scholarship mentorship in Bengali.' }}">
    <meta name="keywords" content="learn chinese, bangla to chinese, HSK preparation, study in china for bangladeshi, china scholarship, chinese language course">
    <meta name="author" content="Banglay Chinese">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0F5132">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Banglay Chinese">
    <meta property="og:title" content="{{ $metaTitle ?? 'Banglay Chinese | Best Learn Chinese for Bangladeshi Students' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'HSK preparation, live classes & China scholarship mentorship in Bengali.' }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    <meta property="og:image" content="{{ $metaImage ?? asset('assets/logo-full.jpeg') }}">
    <meta property="og:locale" content="bn_BD">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle ?? 'Banglay Chinese' }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'Learn Chinese in Bengali — HSK, speaking, kids & scholarship mentorship.' }}">
    <meta name="twitter:image" content="{{ $metaImage ?? asset('assets/logo-full.jpeg') }}">

    <link rel="icon" type="image/png" href="{{ asset('assets/logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "Banglay Chinese",
        "alternateName": "banglaychinese.com",
        "slogan": "Best Learn Chinese for Bangladeshi students",
        "description": "Online Chinese language learning platform for Bengali speakers — HSK preparation, live classes and China scholarship mentorship.",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('assets/logo.jpeg') }}",
        "image": "{{ asset('assets/logo-full.jpeg') }}",
        "email": "info@banglaychinese.com",
        "telephone": "+86-182-2324-9514",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Chongqing",
            "addressCountry": "CN"
        },
        "areaServed": "Bangladesh",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+86-182-2324-9514",
            "contactType": "customer support",
            "availableLanguage": ["Bengali", "English", "Chinese"]
        },
        "sameAs": ["https://wa.me/8618223249514", "https://wa.me/8618223249524"]
    }
    </script>

    @isset($courseJsonLd)
        <script type="application/ld+json">{!! $courseJsonLd !!}</script>
    @endisset

    @isset($faqJsonLd)
        {!! $faqJsonLd !!}
    @endisset
</head>
<body class="font-sans antialiased bg-white text-slate-800" style="font-family: 'Hind Siliguri', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif;">

    {{-- ===== TOP NAV ===== --}}
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-emerald-100">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center shrink-0">
                <img src="{{ asset('assets/logo-full.jpeg') }}" alt="Banglay Chinese Logo" class="h-12 w-auto object-contain">
            </a>

            <div class="hidden items-center gap-7 text-sm font-semibold text-slate-600 lg:flex">
                <a href="{{ route('home') }}#courses" class="transition hover:text-[#0F5132]">কোর্সসমূহ</a>
                <a href="{{ route('scholarship') }}" class="transition hover:text-[#0F5132]">Scholarship Mentorship</a>
                <a href="{{ route('home') }}#mentors" class="transition hover:text-[#0F5132]">Mentors</a>
                <a href="{{ route('posts.index') }}" class="transition hover:text-[#0F5132]">Blog</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="transition hover:text-[#0F5132]">ড্যাশবোর্ড</a>
                @else
                    <a href="{{ route('login') }}" class="transition hover:text-[#0F5132]">Login</a>
                    <a href="{{ route('register') }}" class="transition hover:text-[#0F5132]">Register</a>
                @endauth
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ $waLink ?? 'https://wa.me/8618223249514' }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 rounded-full bg-[#25D366] px-4 py-2 text-sm font-bold text-white shadow-md shadow-[#25D366]/30 transition hover:bg-[#1fb857]">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
            </div>
        </nav>
    </header>

    {{-- PAGE CONTENT --}}
    <main>
        {{ $slot }}
    </main>

    {{-- ===== FOOTER ===== --}}
    <footer class="bg-[#0F5132] text-emerald-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div>
                <img src="{{ asset('assets/logo-full.jpeg') }}" alt="Banglay Chinese" class="h-14 w-auto object-contain mb-4 rounded-lg bg-white p-1">
                <p class="text-sm leading-relaxed text-emerald-200">
                    বাংলা ভাষাভাষীদের জন্য চীনা ভাষা শিক্ষা, HSK প্রস্তুতি ও চায়না স্কলারশিপ মেন্টরশিপ।
                </p>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white">Quick Links</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('home') }}#courses" class="transition hover:text-white">কোর্সসমূহ</a></li>
                    <li><a href="{{ route('scholarship') }}" class="transition hover:text-white">Scholarship Mentorship</a></li>
                    <li><a href="{{ route('posts.index') }}" class="transition hover:text-white">Blog</a></li>
                    <li><a href="{{ route('about') }}" class="transition hover:text-white">About Us</a></li>
                    <li><a href="{{ route('contact') }}" class="transition hover:text-white">Contact</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white">Programs</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('home') }}#courses" class="transition hover:text-white">HSK Standard Track</a></li>
                    <li><a href="{{ route('home') }}#courses" class="transition hover:text-white">HSK Intensive Program</a></li>
                    <li><a href="{{ route('home') }}#courses" class="transition hover:text-white">Chinese Speaking Mastery</a></li>
                    <li><a href="{{ route('home') }}#courses" class="transition hover:text-white">Fun Chinese for Kids</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white">Contact</h3>
                <ul class="space-y-3 text-sm text-emerald-200">
                    <li>📧 <a href="mailto:info@banglaychinese.com" class="hover:text-white">info@banglaychinese.com</a></li>
                    <li>💬 <a href="https://wa.me/8618223249514" target="_blank" rel="noopener" class="hover:text-white">WhatsApp: +86 182 2324 9514</a></li>
                    <li>💬 <a href="https://wa.me/8618223249524" target="_blank" rel="noopener" class="hover:text-white">WhatsApp: +86 182 2324 9524</a></li>
                    <li>📍 Chongqing, China</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-emerald-800 py-6 text-center text-xs text-emerald-300">
            © {{ date('Y') }} Banglay Chinese. সর্বস্বত্ব সংরক্ষিত।
        </div>
    </footer>

    {{-- Sticky Mobile Bottom Nav --}}
    <div class="fixed bottom-0 inset-x-0 z-50 bg-white border-t border-emerald-100 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] lg:hidden">
        <div class="grid grid-cols-4 text-center">
            <a href="{{ url('/') }}" class="flex flex-col items-center py-2.5 text-[#0F5132]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>
                <span class="mt-0.5 text-[10px] font-bold">Home</span>
            </a>
            <a href="{{ route('home') }}#courses" class="flex flex-col items-center py-2.5 text-slate-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span class="mt-0.5 text-[10px] font-bold">Courses</span>
            </a>
            <a href="{{ route('scholarship') }}" class="flex flex-col items-center py-2.5 text-slate-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-6.16-3.42M19 11v4"/></svg>
                <span class="mt-0.5 text-[10px] font-bold">Scholarship</span>
            </a>
            <a href="{{ $waLink ?? 'https://wa.me/8618223249514' }}" target="_blank" rel="noopener" class="flex flex-col items-center py-2.5 text-[#25D366]">
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                <span class="mt-0.5 text-[10px] font-bold">Chat</span>
            </a>
        </div>
    </div>

    {{-- Bottom padding for mobile nav --}}
    <div class="h-16 lg:hidden"></div>

    @stack('scripts')
</body>
</html>
