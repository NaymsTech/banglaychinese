@php
    use App\Services\SettingsService;
    $waNumber = SettingsService::get('whatsapp_number', '8618223249514');
    $contactEmail = SettingsService::get('contact_email', 'info@banglaychinese.com');
    $address = SettingsService::get('physical_address', 'Chongqing, China');
    $gaId = SettingsService::get('google_analytics_id');
    $fbPixelId = SettingsService::get('facebook_pixel_id');
    $siteName = SettingsService::get('site_name', 'Banglay Chinese');
    $metaDesc = SettingsService::get('meta_description', 'Banglay Chinese — Best learn Chinese for Bangladeshi students. HSK 1–4 preparation, live speaking classes, and China scholarship mentorship in Bengali.');
    $phoneFormatted = '+86-' . substr($waNumber, 0, 3) . '-' . substr($waNumber, 3, 4) . '-' . substr($waNumber, 7);
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

    <link rel="icon" type="image/png" href="{{ asset('assets/logo.jpeg') }}">
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
        {!! $faqJsonLd !!}
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

    {{-- ===== TOP NAV ===== --}}
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-emerald-100">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center shrink-0">
                <img src="{{ asset('assets/logo-full.jpeg') }}" alt="Banglay Chinese Logo" class="h-12 w-auto object-contain">
            </a>

            <div class="hidden items-center gap-7 text-sm font-semibold text-slate-600 lg:flex">
                <a href="{{ route('home') }}" class="transition hover:text-[#0F5132]">Home</a>
                <a href="{{ route('courses.index') }}" class="transition hover:text-[#0F5132]">কোর্সসমূহ</a>
                <a href="{{ route('study-in-china') }}" class="transition hover:text-[#0F5132]">Study in China</a>
                <a href="{{ route('posts.index') }}" class="transition hover:text-[#0F5132]">Blog</a>
                <a href="{{ route('about') }}" class="transition hover:text-[#0F5132]">About</a>
                <a href="{{ route('contact') }}" class="transition hover:text-[#0F5132]">Contact</a>
            </div>

            <!-- Desktop Auth (lg+) -->
            <div class="hidden items-center gap-4 lg:flex">
                @guest
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-[#0F5132] px-5 py-2 text-sm font-bold text-white transition hover:bg-[#0d452c]">Login</a>
                @else
                    <a href="{{ route('dashboard.index') }}" class="inline-flex items-center rounded-full bg-[#0F5132] px-5 py-2 text-sm font-bold text-white transition hover:bg-[#0d452c]">Dashboard</a>
                @endguest
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Mobile / Tablet Auth (below lg) -->
                <div class="flex items-center gap-2 lg:hidden">
                    @guest
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-[#0F5132] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0d452c] sm:px-4 sm:py-2 sm:text-sm">Login</a>
                    @else
                        <a href="{{ route('dashboard.index') }}" class="inline-flex items-center rounded-full bg-[#0F5132] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0d452c] sm:px-4 sm:py-2 sm:text-sm">Dashboard</a>
                    @endguest
                </div>

                <a href="{{ route('study-in-china.consultation') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-[#0F5132] px-4 py-2 text-sm font-bold text-white shadow-md shadow-[#0F5132]/30 transition hover:bg-[#0d452c]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    ফ্রি কাউন্সেলিং
                </a>
                <a href="{{ $waLink ?? 'https://wa.me/'.$waNumber }}" target="_blank" rel="noopener"
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
                    <li><a href="{{ route('study-in-china') }}" class="transition hover:text-white">Study in China</a></li>
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
                    <li>📧 <a href="mailto:{{ $contactEmail }}" class="hover:text-white">{{ $contactEmail }}</a></li>
                    <li>💬 <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="hover:text-white">WhatsApp: {{ $phoneFormatted }}</a></li>
                    <li>📍 {{ $address }}</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-emerald-800 py-6 text-center text-xs text-emerald-300">
            © {{ date('Y') }} {{ $siteName }}. সর্বস্বত্ব সংরক্ষিত।
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
