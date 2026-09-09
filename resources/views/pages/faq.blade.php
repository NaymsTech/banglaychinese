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
                    <li aria-current="page" class="text-white">FAQ</li>
                </ol>
            </nav>
            <h1 class="mt-4 max-w-3xl text-3xl font-extrabold sm:text-4xl font-display">{{ $title ?? 'Frequently Asked Questions' }}</h1>
            <p class="mt-4 max-w-2xl text-emerald-100/80">{{ $intro }}</p>
        </div>
    </section>

    {{-- ===== FAQ ACCORDION ===== --}}
    <section class="bg-white py-14 sm:py-16" x-data="{ active: 0 }">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                @foreach($sections as $index => $faq)
                    <div class="overflow-hidden rounded-2xl border transition-colors {{ $index === 0 ? '' : '' }}"
                         :class="active === {{ $index }} ? 'border-emerald-300 bg-emerald-50/40' : 'border-slate-200 bg-white hover:border-emerald-200'">
                        <h2>
                            <button type="button"
                                    class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
                                    @click="active = active === {{ $index }} ? null : {{ $index }}"
                                    :aria-expanded="active === {{ $index }} ? 'true' : 'false'"
                                    aria-controls="faq-panel-{{ $index }}">
                                <span class="font-bold text-slate-900">{{ $faq['heading'] }}</span>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-emerald-700 transition-transform duration-300"
                                      :class="active === {{ $index }} ? 'rotate-45 bg-emerald-600 text-white' : 'bg-emerald-100'">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </span>
                            </button>
                        </h2>
                        <div x-show="active === {{ $index }}"
                             x-cloak
                             id="faq-panel-{{ $index }}"
                             role="region">
                            <div class="px-6 pb-6 space-y-3">
                                @foreach($faq['body'] as $paragraph)
                                    <p class="leading-relaxed text-slate-600">{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Contact CTA --}}
            <div class="mt-12 rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-gray-900 p-8 text-center sm:p-10">
                <h2 class="text-2xl font-bold text-white sm:text-3xl">Didn't find your answer?</h2>
                <p class="mx-auto mt-3 max-w-xl text-emerald-100/80">Send us your question — our team replies quickly, usually the same day.</p>
                <a href="{{ route('contact') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-full bg-emerald-600 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-950/40 transition-all hover:bg-emerald-500 hover:shadow-emerald-600/30">
                    Ask a Question
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
