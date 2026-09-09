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

    <title>Sign in | {{ $siteName }}</title>

    <meta name="description" content="Sign in to {{ $siteName }} to continue your Chinese language course and study-in-China journey.">
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

                {{-- Headline + features --}}
                <div>
                    <h1 class="max-w-md text-4xl font-extrabold leading-tight font-display xl:text-5xl">
                        Welcome Back to <span class="text-emerald-400">Banglay Chinese</span>
                    </h1>
                    <p class="mt-4 max-w-md text-lg text-emerald-100/80">
                        Continue your journey to mastering Chinese and studying abroad.
                    </p>

                    <ul class="mt-10 space-y-4 text-emerald-50">
                        <li class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="text-base">Access your courses</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="text-base">Track your applications</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="text-base">Book mentorship sessions</span>
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

        {{-- ===== Right column: login form ===== --}}
        <section class="relative flex items-center justify-center bg-gradient-to-br from-emerald-50 via-white to-emerald-100 px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                {{-- Mobile brand (stacked on small screens) --}}
                <div class="mb-8 flex flex-col items-center lg:hidden">
                    <img src="{{ asset('assets/logo-full.jpeg') }}" alt="{{ $siteName }} Logo" class="h-14 w-auto rounded-xl object-contain">
                    <p class="mt-3 text-center text-lg font-bold text-emerald-800 font-display">{{ $siteName }}</p>
                </div>

                {{-- Card --}}
                <div class="rounded-2xl bg-white p-8 shadow-xl sm:p-10">
                    <h2 class="text-2xl font-bold text-gray-900">Sign in to your account</h2>
                    @if (Route::has('register'))
                        <p class="mt-2 text-sm text-slate-500">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-semibold text-emerald-700 transition-colors hover:text-emerald-800">Create a new account</a>
                        </p>
                    @endif

                    {{-- Session status (e.g. "email verified — please sign in") --}}
                    <x-auth-session-status class="mt-6" :status="session('status')" />

                    {{-- Validation errors --}}
                    @if ($errors->any())
                        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700" role="alert">
                            <p class="flex items-center gap-2 text-sm font-bold">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                Unable to sign in
                            </p>
                            <ul class="mt-1.5 list-inside list-disc space-y-1 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-semibold text-gray-700">Email Address</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </span>
                                <input id="email" type="email" name="email" value="{{ old('email') }}"
                                       required autofocus autocomplete="username" placeholder="you@example.com"
                                       class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-11 pr-4 text-gray-900 placeholder-gray-400 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        {{-- Password --}}
                        <div x-data="{ show: false }">
                            <label for="password" class="mb-1.5 block text-sm font-semibold text-gray-700">Password</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                </span>
                                <input id="password" type="password" name="password"
                                       :type="show ? 'text' : 'password'"
                                       required autocomplete="current-password" placeholder="••••••••"
                                       class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-11 pr-12 text-gray-900 placeholder-gray-400 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <button type="button"
                                        @click="show = !show"
                                        :aria-pressed="show ? 'true' : 'false'"
                                        aria-label="Show or hide password"
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition-colors hover:text-emerald-700">
                                    <svg x-show="!show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Remember me + forgot password --}}
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label for="remember_me" class="flex cursor-pointer items-center">
                                <input id="remember_me" type="checkbox" name="remember"
                                       class="h-4 w-4 rounded border-gray-300 text-emerald-600 shadow-sm transition-colors focus:ring-emerald-500">
                                <span class="ml-2 select-none text-sm text-gray-600">Remember me for 30 days</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-emerald-700 transition-colors hover:text-emerald-800">
                                    Forgot your password?
                                </a>
                            @endif
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                class="w-full rounded-lg bg-emerald-700 py-4 font-bold text-white shadow-lg transition-colors duration-300 hover:bg-emerald-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Sign In
                        </button>
                    </form>
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
                    Secure Login — your details are encrypted in transit
                </p>
            </div>
        </section>
    </div>
</body>
</html>
