@php
    use App\Services\SettingsService;

    $siteName = SettingsService::get('site_name', 'Banglay Chinese');
    $siteTagline = SettingsService::get('site_tagline', 'বাংলায় চাইনিজ ভাষা শেখার সেরা প্লাটফর্ম');
    $contactEmail = SettingsService::get('contact_email', 'info@banglaychinese.com');
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts: Multi-Lingual Font System (matches the site layout) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@700&family=Anek+Bangla:wght@600;700&family=Hind+Siliguri:wght@400&family=Noto+Sans+SC:wght@400;500&display=swap" rel="stylesheet">

    <title>Forgot your password | {{ $siteName }}</title>

    <meta name="description" content="Request a password reset link for your {{ $siteName }} account and get back to your Chinese language course.">
    <meta name="robots" content="noindex, follow">
    <meta name="theme-color" content="#0F5132">

    <link rel="icon" type="image/png" href="{{ asset('assets/logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800">

    <div class="grid min-h-screen lg:grid-cols-2">

        {{-- ===== Left column: branding (desktop only) ===== --}}
        <section class="relative hidden overflow-hidden bg-gradient-to-br from-gray-900 via-emerald-950 to-gray-900 text-white lg:flex lg:flex-col lg:justify-between">
            {{-- Decorative overlays --}}
            <div aria-hidden="true" class="footer-pattern pointer-events-none absolute inset-0"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-emerald-600/10 blur-3xl"></div>

            <div class="relative flex flex-col justify-between gap-16 px-12 py-14 xl:px-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ $siteName }} — Home">
                    <img src="{{ asset('assets/logo-full.jpeg') }}" alt="{{ $siteName }} Logo" class="h-12 w-auto rounded-xl object-contain">
                    <span class="text-xl font-bold text-emerald-300 font-display">{{ $siteName }}</span>
                </a>

                {{-- Headline + reassurance --}}
                <div>
                    <h1 class="max-w-md text-4xl font-extrabold leading-tight font-display xl:text-5xl">
                        Let's get you back into <span class="text-emerald-400">{{ $siteName }}</span>
                    </h1>
                    <p class="mt-4 max-w-md text-lg text-emerald-100/80">
                        Enter the email linked to your account and we will send you a secure password reset link.
                    </p>

                    <ul class="mt-10 space-y-4 text-emerald-50">
                        <li class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="text-base">A secure reset link lands in your inbox</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="text-base">Reset links expire after 60 minutes</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="text-base">Back to learning in no time</span>
                        </li>
                    </ul>
                </div>

                {{-- Footer note --}}
                <div class="border-t border-white/10 pt-6">
                    <p class="text-sm leading-relaxed text-emerald-100/70 font-bangla">{{ $siteTagline }}</p>
                    <p class="mt-2 text-xs text-emerald-100/50">
                        <a href="mailto:{{ $contactEmail }}" class="transition-colors hover:text-emerald-200">{{ $contactEmail }}</a>
                    </p>
                </div>
            </div>
        </section>

        {{-- ===== Right column: forgot password form ===== --}}
        <section class="relative flex items-center justify-center bg-gradient-to-br from-emerald-50 via-white to-emerald-100 px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                {{-- Mobile brand (stacked on small screens) --}}
                <div class="mb-8 flex flex-col items-center lg:hidden">
                    <img src="{{ asset('assets/logo-full.jpeg') }}" alt="{{ $siteName }} Logo" class="h-14 w-auto rounded-xl object-contain">
                    <p class="mt-3 text-center text-lg font-bold text-emerald-800 font-display">{{ $siteName }}</p>
                </div>

                {{-- Card --}}
                <div class="rounded-2xl bg-white p-8 shadow-xl sm:p-10">
                    <h2 class="text-2xl font-bold text-gray-900">Forgot your password?</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        No problem. Just let us know your email address and we will email you a password reset link.
                    </p>

                    {{-- Session status + validation errors --}}
                    <div class="mt-6">
                        <x-form-feedback />
                        <x-form-feedback type="error" />
                    </div>

                    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-6">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-semibold text-gray-700">Email Address</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </span>
                                <input id="email" type="email" name="email" value="{{ old('email') }}"
                                       required autofocus autocomplete="email" placeholder="you@example.com"
                                       class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-11 pr-4 text-gray-900 placeholder-gray-400 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                class="w-full rounded-lg bg-emerald-700 py-4 font-bold text-white shadow-lg transition-colors duration-300 hover:bg-emerald-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Email Password Reset Link
                        </button>
                    </form>

                    {{-- Back to login --}}
                    <div class="mt-6 border-t border-gray-100 pt-6 text-center">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 transition-colors hover:text-emerald-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                            Back to Login
                        </a>
                    </div>
                </div>

                {{-- Below the card --}}
                <div class="mt-6 flex items-center justify-center gap-2 text-sm text-emerald-700">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 font-semibold transition-colors hover:text-emerald-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Back to Home
                    </a>
                </div>

                <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    Your email is only used to send you a secure reset link
                </p>
            </div>
        </section>
    </div>
</body>
</html>
