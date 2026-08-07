<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Maintenance — {{ config('app.name', 'Banglay Chinese') }}</title>
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

            <h1 class="text-8xl font-extrabold text-emerald-600">503</h1>
            <h2 class="mt-4 text-2xl font-bold text-gray-900 text-center">
                Under Maintenance
            </h2>
            <p class="mt-3 max-w-md text-center text-gray-600">
                We are currently making improvements to the site. We'll be back very soon. Thank you!
            </p>
        </div>
    </body>
</html>
