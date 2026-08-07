<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Page Not Found — {{ config('app.name', 'Banglay Chinese') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/logo.jpeg') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-b from-emerald-50 to-emerald-100 px-6 py-16">
            <div class="mb-8">
                <a href="{{ url('/') }}">
                    <x-application-logo class="w-24 h-auto" />
                </a>
            </div>

            <h1 class="text-8xl font-extrabold text-emerald-600">404</h1>
            <h2 class="mt-4 text-2xl font-bold text-gray-900 text-center">
                Page Not Found
            </h2>
            <p class="mt-3 max-w-md text-center text-gray-600">
                The page you are looking for does not exist or has been moved.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ url('/') }}" class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Go to Home
                </a>
                <a href="{{ route('courses.index') }}" class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-600/20 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Browse Courses
                </a>
            </div>
        </div>
    </body>
</html>
