<x-app-layout
    :metaTitle="$meta_title"
    :metaDescription="$meta_description"
>
    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-emerald-950 to-gray-900 text-white">
        <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-emerald-700/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 sm:py-20">
            <nav class="text-xs font-semibold tracking-wider text-emerald-300/70" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('home') }}" class="transition-colors hover:text-white">Home</a></li>
                    <li aria-hidden="true" class="text-emerald-500/60">/</li>
                    <li aria-current="page" class="text-white">{{ $title }}</li>
                </ol>
            </nav>
            <h1 class="mt-4 max-w-3xl text-3xl font-extrabold sm:text-4xl font-display">{{ $title }}</h1>
            <p class="mt-3 text-sm font-medium text-emerald-300/80">Last updated: {{ $updated_at }}</p>
        </div>
    </section>

    {{-- ===== BODY ===== --}}
    <section class="bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <p class="text-lg leading-relaxed text-slate-600">{{ $intro }}</p>

            <div class="mt-10 space-y-10">
                @foreach($sections as $section)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">{{ $section['heading'] }}</h2>
                        <div class="mt-4 space-y-3">
                            @foreach($section['body'] as $paragraph)
                                <p class="leading-relaxed text-slate-600">{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Contact CTA --}}
            <div class="mt-12 rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-gray-900 p-8 text-center sm:p-10">
                <h2 class="text-2xl font-bold text-white sm:text-3xl">Still have questions?</h2>
                <p class="mx-auto mt-3 max-w-xl text-emerald-100/80">We are happy to help — reach out through the contact page and our team will get back to you quickly.</p>
                <a href="{{ route('contact') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-full bg-emerald-600 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-950/40 transition-all hover:bg-emerald-500 hover:shadow-emerald-600/30">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
