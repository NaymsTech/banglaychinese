@php
    use App\Services\SettingsService;

    $siteName = SettingsService::get('site_name', 'Banglay Chinese');
    $contactEmail = SettingsService::get('contact_email', 'info@banglaychinese.com');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify your email | {{ $siteName }}</title>
    <meta name="robots" content="noindex, follow">
    <meta name="theme-color" content="#0F5132">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-emerald-100 font-sans antialiased text-slate-800">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-lg">
            {{-- Mobile brand --}}
            <div class="mb-8 flex flex-col items-center">
                <a href="{{ route('home') }}" aria-label="{{ $siteName }} — Home">
                    <img src="{{ asset('assets/logo-full.jpeg') }}" alt="{{ $siteName }} Logo" class="h-14 w-auto rounded-xl object-contain">
                </a>
                <p class="mt-3 text-center text-lg font-bold text-emerald-800 font-display">{{ $siteName }}</p>
            </div>

            <div class="rounded-2xl bg-white p-8 shadow-xl sm:p-10">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-7 w-7 text-emerald-700" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.844a2.25 2.25 0 012.25-2.25h15a2.25 2.25 0 012.25 2.25v9.156z"/></svg>
                </div>

                <h2 class="mt-6 text-2xl font-bold text-gray-900">Verify your email address</h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-500">
                    Thanks for signing up! Before you can access your dashboard, courses and downloads, please confirm that
                    <span class="font-semibold text-gray-700">{{ auth()->user()?->email }}</span> belongs to you. We have sent you a
                    verification link — check your inbox.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
                        A fresh verification link has been sent to your email address.
                    </div>
                @endif

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-lg bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow transition-colors duration-300 hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Resend verification email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-bold text-slate-600 transition-colors duration-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Log out
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs leading-relaxed text-slate-400">
                    Did not receive it? Check your spam folder, or
                    <a href="mailto:{{ $contactEmail }}" class="font-semibold text-emerald-700 transition-colors hover:text-emerald-800">contact support</a>.
                </p>
            </div>

            <div class="mt-6 flex items-center justify-center gap-2 text-sm text-emerald-700">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 font-semibold transition-colors hover:text-emerald-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
