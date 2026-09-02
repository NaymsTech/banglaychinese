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

    <title>{{ $metaTitle ?? 'Admin Panel' }} | Banglay Chinese Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800">

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 shrink-0 bg-primary-800 text-primary-50">
            <div class="flex h-full flex-col">
                <div class="px-6 py-6">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('assets/logo-full.jpeg') }}" alt="Banglay Chinese" class="h-10 w-auto rounded bg-white p-0.5 object-contain">
                        <div>
                            <p class="text-sm font-bold text-white">Admin Panel</p>
                            <p class="text-xs text-primary-200">Banglay Chinese</p>
                        </div>
                    </a>
                </div>

                <nav class="mt-4 flex-1 space-y-1 px-3">
                    {{-- Dashboard --}}
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>
                        📊 Dashboard
                    </a>
                    {{-- Students (placeholder, coming soon) --}}
                    <span class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold text-primary-400/60 cursor-not-allowed">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        👨‍🎓 Students
                    </span>
                    {{-- Courses --}}
                    <a href="{{ route('admin.courses.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.courses*') || request()->routeIs('admin.modules*') || request()->routeIs('admin.lessons*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        📚 Courses
                    </a>
                    {{-- Services --}}
                    <a href="{{ route('admin.services.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.services*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        🎓 Services
                    </a>
                    {{-- Payments --}}
                    <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.payments*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 6h14a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1z"/></svg>
                        💰 Payments
                    </a>
                    {{-- Blog --}}
                    <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.posts*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        📰 Blog
                    </a>
                    {{-- Scholarships --}}
                    <a href="{{ route('admin.scholarships.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.scholarships*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        🎓 Scholarships
                    </a>
                    {{-- Study in China CMS --}}
                    <a href="{{ route('admin.study-in-china.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.study-in-china*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        🇨🇳 Study in China
                    </a>
                    {{-- CRM --}}
                    <a href="{{ route('admin.crm.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.crm*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        📊 CRM
                    </a>
                    {{-- About Page --}}
                    <a href="{{ route('admin.about.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.about*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        📄 About Page
                    </a>
                    {{-- Settings --}}
                    <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.settings*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        ⚙️ Settings
                    </a>
                    {{-- Contact Messages --}}
                    <a href="{{ route('admin.contact-messages.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.contact-messages*') ? 'bg-primary-700 text-white' : 'hover:bg-primary-900 hover:text-white' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 16c0 4.418-8 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z"/></svg>
                        📨 Messages
                    </a>
                </nav>

                <div class="space-y-1 border-t border-primary-700 px-3 py-4">
                    <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold text-primary-100 transition hover:bg-primary-900 hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        🌐 View Site
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold text-primary-100 transition hover:bg-accent-600 hover:text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Admin Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1">
            <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex items-center justify-between px-8 py-4">
                    <h1 class="text-lg font-bold text-slate-800">@yield('page-title', 'Admin Panel')</h1>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-800 text-sm font-bold text-white">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-8">
                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm font-semibold text-primary-700">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
