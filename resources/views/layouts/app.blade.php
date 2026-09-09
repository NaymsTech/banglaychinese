@php
    use App\Services\SettingsService;
    use App\Support\WhatsAppNumber;
    $waNumber = SettingsService::get('whatsapp_number', '8618223249514');
    $contactEmail = SettingsService::get('contact_email', 'info@banglaychinese.com');
    $address = SettingsService::get('physical_address', 'Chongqing, China');
    $footerCopyright = SettingsService::get('footer_copyright_text');
    $gaId = SettingsService::get('google_analytics_id');
    $fbPixelId = SettingsService::get('facebook_pixel_id');
    $siteName = SettingsService::get('site_name', 'Banglay Chinese');
    $metaDesc = SettingsService::get('meta_description', 'Banglay Chinese — Best learn Chinese for Bangladeshi students. HSK 1–4 preparation, live speaking classes, and China scholarship mentorship in Bengali.');
    $siteTagline = SettingsService::get('site_tagline', 'বাংলায় চাইনিজ ভাষা শেখার সেরা প্লাটফর্ম');
    $facebookUrl = SettingsService::get('facebook_url', 'https://facebook.com/banglaychinese');
    $youtubeUrl = SettingsService::get('youtube_url', 'https://youtube.com/@banglaychinese');
    $linkedinUrl = SettingsService::get('linkedin_url');
    $instagramUrl = SettingsService::get('instagram_url');
    $twitterUrl = SettingsService::get('twitter_url');
    $tiktokUrl = SettingsService::get('tiktok_url');
    $siteLogo = SettingsService::get('site_logo');
    $siteFavicon = SettingsService::get('site_favicon');
    // Brand images live on the public disk; fall back to the bundled assets
    // until an admin uploads replacements in Settings.
    $assetFrom = static function (?string $path, string $fallback): string {
        $path = trim((string) $path);

        return $path === ''
            ? $fallback
            : (str_starts_with($path, 'http') ? $path : asset('storage/' . ltrim($path, '/')));
    };
    $logoUrl = $assetFrom($siteLogo, asset('assets/logo-full.jpeg'));
    $faviconUrl = $assetFrom($siteFavicon, asset('assets/logo.jpeg'));
    // Display/number helpers use the shared formatter so every page renders
    // the same grouping ("8618223249514" → "+86 182-2324-9514").
    $phoneFormatted = WhatsAppNumber::display($waNumber);
    $waDigits = WhatsAppNumber::normalize($waNumber);
    $waDisplay = WhatsAppNumber::display($waNumber);
    $navItems = [
        ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Courses', 'url' => route('courses.index'), 'active' => request()->routeIs('courses.*')],
        ['label' => 'Shop', 'url' => route('shop.index'), 'active' => request()->routeIs('shop.*')],
        ['label' => 'Free Resources', 'url' => route('free-resources.index'), 'active' => request()->routeIs('free-resources.*')],
        ['label' => 'Study in China', 'url' => route('study-in-china'), 'active' => request()->routeIs('study-in-china', 'study-in-china.*', 'services.*')],
        ['label' => 'Blog', 'url' => route('posts.index'), 'active' => request()->routeIs('posts.*')],
        ['label' => 'About', 'url' => route('about'), 'active' => request()->routeIs('about*')],
    ];
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts: Multi-Lingual Font System -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@700&family=Anek+Bangla:wght@600;700&family=Hind+Siliguri:wght@400&family=Noto+Sans+SC:wght@400;500&display=swap" rel="stylesheet">

    <title>{{ $metaTitle ?? $siteName }} | {{ $siteName }}</title>

    <meta name="description" content="{{ $metaDescription ?? $metaDesc }}">
    <meta name="keywords" content="learn chinese, bangla to chinese, HSK preparation, study in china for bangladeshi, china scholarship, chinese language course">
    <meta name="author" content="{{ $siteName }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0F5132">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle ?? $siteName . ' | Best Learn Chinese for Bangladeshi Students' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'HSK preparation, live classes & China scholarship mentorship in Bengali.' }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    <meta property="og:image" content="{{ $metaImage ?? asset('assets/logo-full.jpeg') }}">
    <meta property="og:locale" content="bn_BD">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle ?? $siteName }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'Learn Chinese in Bengali — HSK, speaking, kids & scholarship mentorship.' }}">
    <meta name="twitter:image" content="{{ $metaImage ?? asset('assets/logo-full.jpeg') }}">

    @stack('meta')

    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "EducationalOrganization",
        "name": "{{ $siteName }}",
        "alternateName": "banglaychinese.com",
        "slogan": "Best Learn Chinese for Bangladeshi students",
        "description": "Online Chinese language learning platform for Bengali speakers — HSK preparation, live classes and China scholarship mentorship.",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('assets/logo.jpeg') }}",
        "image": "{{ asset('assets/logo-full.jpeg') }}",
        "email": "{{ $contactEmail }}",
        "telephone": "{{ $phoneFormatted }}",
        "address": {
            "@@type": "PostalAddress",
            "addressLocality": "{{ $address }}",
            "addressCountry": "CN"
        },
        "areaServed": "Bangladesh",
        "contactPoint": {
            "@@type": "ContactPoint",
            "telephone": "{{ $phoneFormatted }}",
            "contactType": "customer support",
            "availableLanguage": ["Bengali", "English", "Chinese"]
        },
        "sameAs": ["https://wa.me/{{ $waNumber }}"]
    }
    </script>

    @isset($courseJsonLd)
        <script type="application/ld+json">{!! $courseJsonLd !!}</script>
    @endisset

    @isset($faqJsonLd)
        <script type="application/ld+json">{!! $faqJsonLd !!}</script>
    @endisset

    @if($gaId)
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
    @endif

    @if($fbPixelId)
    <!-- Facebook Pixel -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $fbPixelId }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $fbPixelId }}&ev=PageView&noscript=1"
    /></noscript>
    @endif
</head>
<body class="font-sans antialiased bg-white text-slate-800">

    {{-- ===== HEADER / NAVBAR ===== --}}
    <header x-data="{ open: false }"
            @keydown.escape.window="open = false"
            class="sticky top-0 z-50 w-full border-b border-gray-100 bg-white/80 backdrop-blur-md">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8" aria-label="Main navigation">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="{{ $siteName }} — Home">
                <img src="{{ $logoUrl }}" alt="{{ $siteName }} Logo" class="h-10 w-auto object-contain sm:h-12">
            </a>

            {{-- Desktop navigation (lg+) --}}
            <div class="hidden items-center gap-7 lg:flex">
                @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}"
                       class="text-sm font-semibold transition-colors {{ $item['active'] ? 'text-emerald-700' : 'text-slate-600 hover:text-emerald-700' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- Desktop actions (lg+) --}}
            <div class="hidden items-center gap-3 lg:flex">
                <a href="{{ $waLink ?? 'https://wa.me/' . $waNumber }}" target="_blank" rel="noopener" aria-label="WhatsApp"
                   class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#25D366]/10 text-[#128C4A] transition-colors hover:bg-[#25D366] hover:text-white">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </a>
                @auth
                    <a href="{{ route('dashboard.index') }}"
                       class="inline-flex h-10 items-center rounded-full bg-[#0F5132] px-5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-[#0d452c]">Dashboard</a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex h-10 items-center rounded-full border border-[#0F5132]/40 px-5 text-sm font-bold text-[#0F5132] transition-colors hover:bg-[#0F5132] hover:text-white">Login</a>
                    <a href="{{ route('contact') }}"
                       class="inline-flex h-10 items-center rounded-full bg-[#0F5132] px-5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-[#0d452c]">Contact Us</a>
                @endauth
            </div>

            {{-- Mobile / tablet toggle --}}
            <div class="flex items-center lg:hidden">
                <button type="button"
                        @click="open = !open"
                        :aria-expanded="open ? 'true' : 'false'"
                        aria-controls="mobile-menu"
                        aria-label="Menu"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-lg text-slate-700 transition-colors hover:bg-emerald-50 hover:text-emerald-700">
                    <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </nav>

        {{-- Mobile drawer (below lg) --}}
        <div id="mobile-menu"
             x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="border-t border-gray-100 bg-white/95 backdrop-blur-md lg:hidden">
            <div class="space-y-1 px-4 pb-6 pt-3 sm:px-6">
                @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}"
                       @click="open = false"
                       class="block rounded-lg px-4 py-3 text-base font-semibold transition-colors {{ $item['active'] ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-700' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <div class="grid gap-2.5 pt-4">
                    @auth
                        <a href="{{ route('dashboard.index') }}" @click="open = false"
                           class="inline-flex h-11 items-center justify-center rounded-full bg-[#0F5132] px-5 text-sm font-bold text-white transition-colors hover:bg-[#0d452c]">Dashboard</a>
                    @else
                        <a href="{{ route('contact') }}" @click="open = false"
                           class="inline-flex h-11 items-center justify-center rounded-full bg-[#0F5132] px-5 text-sm font-bold text-white transition-colors hover:bg-[#0d452c]">Contact Us</a>
                        <a href="{{ route('login') }}" @click="open = false"
                           class="inline-flex h-11 items-center justify-center rounded-full border border-gray-300 px-5 text-sm font-bold text-slate-700 transition-colors hover:border-emerald-600 hover:text-emerald-700">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main>
        {{ $slot }}
    </main>

    {{-- ===== FOOTER ===== --}}
    @php
        // Footer link groups + socials (only networks with a configured URL render).
        $menuLinks = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'About Us', 'url' => route('about')],
            ['label' => 'Study In China', 'url' => route('study-in-china')],
            ['label' => 'Contact Us', 'url' => route('contact')],
            ['label' => 'Blog', 'url' => route('posts.index')],
        ];

        // Account/legal links are state-aware: guests get Login/Register and
        // password recovery; signed-in students get their Dashboard. Legal
        // pages are always listed.
        $usefulLinks = [
            ['label' => 'Terms And Conditions', 'url' => route('pages.show', 'terms-and-conditions')],
            ['label' => 'Privacy Policy', 'url' => route('pages.show', 'privacy-policy')],
            ['label' => 'Refund And Returns Policy', 'url' => route('pages.show', 'refund-and-returns-policy')],
            ['label' => 'FAQ', 'url' => route('pages.show', 'faq')],
        ];

        if (auth()->check()) {
            array_unshift($usefulLinks, ['label' => 'Dashboard', 'url' => route('dashboard.index')]);
        } else {
            array_unshift($usefulLinks, ['label' => 'Login', 'url' => route('login')]);
            array_unshift($usefulLinks, ['label' => 'Lost Password', 'url' => route('password.request')]);

            if (Route::has('register')) {
                array_unshift($usefulLinks, ['label' => 'Register', 'url' => route('register')]);
            }
        }

        $socials = [
            ['label' => 'Facebook', 'url' => $facebookUrl, 'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
            ['label' => 'Instagram', 'url' => $instagramUrl, 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'],
            ['label' => 'X (Twitter)', 'url' => $twitterUrl, 'path' => 'M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z'],
            ['label' => 'LinkedIn', 'url' => $linkedinUrl, 'path' => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z'],
            ['label' => 'TikTok', 'url' => $tiktokUrl, 'path' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z'],
            ['label' => 'YouTube', 'url' => $youtubeUrl, 'path' => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
        ];
    @endphp

    <footer class="relative overflow-hidden bg-gradient-to-br from-emerald-900 via-emerald-800 to-gray-900 text-white">
        <h2 class="sr-only">Footer</h2>

        {{-- Subtle geometric pattern overlay --}}
        <div aria-hidden="true" class="footer-pattern pointer-events-none absolute inset-0"></div>
        <div aria-hidden="true"
             class="pointer-events-none absolute -top-40 left-1/2 h-96 w-[42rem] -translate-x-1/2 rounded-full bg-emerald-400/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-6 py-16">
            {{-- Top row: brand + social icons --}}
            <div x-data="revealItem(0)"
                 :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                 :style="visible ? 'transition-delay: ' + delay + 'ms' : ''"
                 class="flex flex-col items-center justify-between gap-8 transition-all duration-700 ease-out lg:flex-row">
                <a href="{{ route('home') }}" class="group flex flex-col items-center gap-3 lg:flex-row lg:gap-4" aria-label="{{ $siteName }} — Home">
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }} Logo" class="h-14 w-auto rounded-lg object-contain">
                    <p class="text-center text-2xl font-bold text-emerald-300 drop-shadow-[0_0_16px_rgba(52,211,153,0.35)] transition group-hover:drop-shadow-[0_0_22px_rgba(52,211,153,0.55)] lg:text-left font-display">
                        Learn Chinese In Bangla
                    </p>
                </a>

                <ul class="flex flex-wrap items-center justify-center gap-x-4 gap-y-3" aria-label="Social media">
                    @foreach($socials as $social)
                        @if($social['url'])
                            <li>
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] }}"
                                   class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-emerald-600 bg-white/5 text-emerald-200 transition-all duration-300 hover:scale-110 hover:border-emerald-400 hover:bg-emerald-500 hover:text-white hover:shadow-[0_0_18px_rgba(16,185,129,0.55)]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $social['path'] }}"/></svg>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            {{-- Divider --}}
            <div class="my-10 border-t border-emerald-700/50" aria-hidden="true"></div>

            {{-- Main columns: About / Menu / Useful Links / Newsletter --}}
            <div class="grid grid-cols-1 gap-x-8 gap-y-12 md:grid-cols-2 lg:grid-cols-12">

                {{-- Column 1: About --}}
                <div x-data="revealItem(0)"
                     :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                     :style="visible ? 'transition-delay: ' + delay + 'ms' : ''"
                     class="transition-all duration-700 ease-out lg:col-span-4 lg:pr-8">
                    <h3 class="mb-4 text-xl font-bold text-white font-display">About</h3>
                    <p class="mb-4 leading-relaxed text-emerald-100/80 font-bangla">{{ $siteTagline }}</p>

                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-3 text-emerald-200">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $address }}</span>
                        </li>

                        <li x-data="{ copied: false }" class="flex items-start gap-2 text-sm">
                            <a href="{{ 'https://wa.me/' . $waNumber }}" target="_blank" rel="noopener"
                               class="flex min-w-0 flex-1 items-start gap-3 text-emerald-200 transition-colors hover:text-white">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span class="break-all">{{ $waDisplay }}</span>
                            </a>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $waDisplay }}'); copied = true; setTimeout(() => copied = false, 1800)"
                                    class="mt-1 inline-flex shrink-0 items-center gap-1 rounded-full border border-emerald-700/60 px-2 py-1 text-[10px] font-bold tracking-wide text-emerald-300 transition-colors hover:border-emerald-500 hover:text-white"
                                    aria-label="Copy phone number">
                                <svg x-show="!copied" x-cloak class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </li>

                        <li x-data="{ copied: false }" class="flex items-start gap-2 text-sm">
                            <a href="mailto:{{ $contactEmail }}"
                               class="flex min-w-0 flex-1 items-start gap-3 text-emerald-200 transition-colors hover:text-white">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="break-all">{{ $contactEmail }}</span>
                            </a>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $contactEmail }}'); copied = true; setTimeout(() => copied = false, 1800)"
                                    class="mt-1 inline-flex shrink-0 items-center gap-1 rounded-full border border-emerald-700/60 px-2 py-1 text-[10px] font-bold tracking-wide text-emerald-300 transition-colors hover:border-emerald-500 hover:text-white"
                                    aria-label="Copy email address">
                                <svg x-show="!copied" x-cloak class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </li>
                    </ul>

                    {{-- Payment methods --}}
                    <div class="mt-8">
                        <h4 class="text-base font-semibold text-white">We accept</h4>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center rounded-lg bg-[#D1206B] px-3 py-1.5 text-sm font-extrabold text-white shadow-md" aria-label="bKash">bKash</span>
                            <span class="inline-flex items-center rounded-lg bg-[#E41E26] px-3 py-1.5 text-sm font-extrabold text-white shadow-md" aria-label="Nagad">Nagad</span>
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-400/40 bg-white/5 px-3 py-1.5 text-sm font-bold text-emerald-200" aria-label="Bank Transfer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                                Bank Transfer
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Column 2: Menu --}}
                <div x-data="revealItem(100)"
                     :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                     :style="visible ? 'transition-delay: ' + delay + 'ms' : ''"
                     class="transition-all duration-700 ease-out lg:col-span-2">
                    <h3 class="mb-4 text-xl font-bold text-white font-display">Menu</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach($menuLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}"
                                   class="relative text-emerald-100/80 transition-all duration-200 hover:translate-x-1 hover:text-white after:absolute after:-bottom-1 after:left-0 after:h-px after:w-0 after:bg-emerald-400 after:transition-all after:duration-300 hover:after:w-full">
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Column 3: Useful Links --}}
                <div x-data="revealItem(200)"
                     :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                     :style="visible ? 'transition-delay: ' + delay + 'ms' : ''"
                     class="transition-all duration-700 ease-out lg:col-span-3">
                    <h3 class="mb-4 text-xl font-bold text-white font-display">Useful Links</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach($usefulLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}"
                                   class="relative text-emerald-100/80 transition-all duration-200 hover:translate-x-1 hover:text-white after:absolute after:-bottom-1 after:left-0 after:h-px after:w-0 after:bg-emerald-400 after:transition-all after:duration-300 hover:after:w-full">
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Column 4: Email subscription --}}
                <div x-data="revealItem(300)"
                     :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                     :style="visible ? 'transition-delay: ' + delay + 'ms' : ''"
                     class="transition-all duration-700 ease-out lg:col-span-3">
                    <h3 class="mb-4 text-xl font-bold text-white font-display">Serious About Studying in China?</h3>
                    <p class="mb-4 text-sm leading-relaxed text-emerald-100/80">
                        Universities don't wait. Join our private email list for early alerts and step-by-step guidance.
                    </p>

                    <form method="POST" action="{{ route('newsletter.subscribe') }}"
                          x-data="{ submitting: false }"
                          @submit="submitting = true"
                          aria-label="Email subscription form">
                        @csrf
                        <label for="footer-newsletter-email" class="sr-only">Email address</label>
                        <input id="footer-newsletter-email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               autocomplete="email"
                               required
                               placeholder="Your Email Address"
                               :disabled="submitting"
                               class="w-full rounded-lg border border-emerald-700 bg-emerald-900/50 px-4 py-3 text-white placeholder-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-60">

                        @error('email')
                            <p class="mt-2 text-xs font-medium text-red-300" role="alert">{{ $message }}</p>
                        @enderror

                        @if (session('newsletter'))
                            <p class="mt-3 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2.5 text-xs leading-relaxed text-emerald-200" role="status">
                                {{ session('newsletter') }}
                            </p>
                        @endif

                        <button type="submit"
                                :disabled="submitting"
                                class="mt-3 w-full rounded-lg bg-emerald-600 py-3 font-semibold text-white transition-colors duration-300 hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-60">
                            <span x-show="!submitting">Subscribe</span>
                            <span x-show="submitting" x-cloak>Subscribing…</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bottom copyright bar --}}
        <div class="relative mt-16 border-t border-emerald-700/40 bg-emerald-950 px-6 py-6">
            <p class="text-center text-sm text-emerald-400/60">
                @if(filled($footerCopyright))
                    {{ str_replace(['{year}', '{site}'], [date('Y'), $siteName], $footerCopyright) }}
                @else
                    Copyright © {{ date('Y') }} {{ $siteName }}
                @endif
            </p>
        </div>
    </footer>

    {{-- Sticky Mobile Bottom Nav --}}
    <div class="fixed bottom-0 inset-x-0 z-50 bg-white border-t border-emerald-100 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] lg:hidden">
        <div class="grid grid-cols-4 text-center">
            <a href="{{ url('/') }}" class="flex flex-col items-center py-2.5 text-[#0F5132]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>
                <span class="mt-0.5 text-[10px] font-bold">Home</span>
            </a>
            <a href="{{ route('courses.index') }}" class="flex flex-col items-center py-2.5 text-slate-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span class="mt-0.5 text-[10px] font-bold">Courses</span>
            </a>
            <a href="{{ route('study-in-china') }}" class="flex flex-col items-center py-2.5 text-slate-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-6.16-3.42M19 11v4"/></svg>
                <span class="mt-0.5 text-[10px] font-bold">Study in China</span>
            </a>
            <a href="{{ $waLink ?? 'https://wa.me/'.$waNumber }}" target="_blank" rel="noopener" class="flex flex-col items-center py-2.5 text-[#25D366]">
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
