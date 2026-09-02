@php
    use App\Services\SettingsService;
    $waNumber = SettingsService::get('whatsapp_number', '8618223249514');
    $waLink = 'https://wa.me/' . $waNumber;

    $meta = [
        'guided-application'       => ['badges' => ['Self-Managed'], 'highlight' => false],
        'full-application-service' => ['badges' => ['Complete Support'], 'highlight' => false],
        'elite-success-program'    => ['badges' => ['1-Year Mentorship', 'Most Comprehensive'], 'highlight' => true],
    ];
@endphp

<x-app-layout>
    <x-slot name="metaTitle">Study in China Services | Scholarship & Application Packages | Banglay Chinese</x-slot>
    <x-slot name="metaDescription">চীনে পড়তে যাওয়ার সম্পূর্ণ গাইডেন্স — Application Guide থেকে ১-বছরের Complete Pathway। Scholarship ও ভর্তি প্রক্রিয়া সহজ করতে Banglay Chinese-এর consulting packages।</x-slot>

    <div class="bg-surface font-sans text-text">
        {{-- HERO --}}
        <section class="relative overflow-hidden bg-primary-800 text-white">
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <div class="absolute top-10 right-10 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-10 w-80 h-80 bg-primary-600/40 rounded-full blur-3xl"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                <div class="max-w-3xl">
                    <span class="inline-block bg-white/10 text-primary-100 px-4 py-1 rounded-full text-sm font-semibold">🇨🇳 Study in China Services</span>
                    <h1 class="mt-5 font-bangla text-4xl md:text-5xl font-bold leading-tight">চীনে পড়াশোনার জন্য Support-এর Level বেছে নিন</h1>
                    <p class="mt-4 text-lg text-primary-100 leading-relaxed font-bangla">নিজে করুন বা আমাদের উপর ছেড়ে দিন — আপনার প্রয়োজন অনুযায়ী consulting package বেছে নিন।</p>
                </div>
            </div>
        </section>

        {{-- NOTE --}}
        <section class="bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <p class="rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800 leading-relaxed font-bangla">
                    <strong>জেনে রাখুন:</strong> এগুলো Study in China-র consultation ও support package — Banglay Chinese-এর নিয়মিত Chinese language course নয়।
                </p>
            </div>
        </section>

        {{-- SERVICES --}}
        <section class="pb-16 lg:pb-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="space-y-6">
                    @forelse($services as $service)
                        @php
                            $m = $meta[$service->slug] ?? ['badges' => [], 'highlight' => false];
                            $features = is_array($service->features) ? $service->features : [];
                        @endphp
                        <div class="rounded-2xl border p-6 sm:p-8 {{ $m['highlight'] ? 'border-primary-600 ring-1 ring-primary-100 shadow-md' : 'border-border bg-surface-alt' }}">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @foreach($m['badges'] as $badge)
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $m['highlight'] ? 'bg-accent-50 text-accent-700 border border-accent-100' : 'bg-primary-50 text-primary-700 border border-primary-100' }}">{{ $badge }}</span>
                                        @endforeach
                                        @if($service->duration)
                                            <span class="rounded-full bg-slate-100 text-slate-600 px-3 py-1 text-xs font-semibold">{{ $service->duration }}</span>
                                        @endif
                                    </div>
                                    <h2 class="mt-4 font-display text-2xl md:text-3xl font-bold text-slate-900">{{ $service->name }}</h2>
                                    <p class="mt-3 text-slate-600 leading-relaxed font-bangla">{{ $service->short_description ?: $service->description }}</p>

                                    @if($features)
                                        <ul class="mt-5 grid sm:grid-cols-2 gap-2.5">
                                            @foreach($features as $feature)
                                                <li class="flex items-start gap-2 text-sm text-slate-700">
                                                    <svg class="w-5 h-5 mt-0.5 text-primary-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    <span class="leading-relaxed">{{ $feature }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <div class="lg:w-72 flex-shrink-0 text-center lg:text-right">
                                    <div class="mb-4">
                                        <span class="text-4xl md:text-5xl font-extrabold text-slate-900">৳{{ number_format($service->price) }}</span>
                                    </div>
                                    <a href="{{ route('study-in-china.consultation', ['service' => $service->slug]) }}"
                                       class="block w-full text-center font-bold py-3.5 px-8 rounded-xl transition-colors {{ $m['highlight'] ? 'bg-accent-600 text-white hover:bg-accent-700' : 'bg-primary-600 text-white hover:bg-primary-700' }}">
                                        {{ $service->cta_label }} →
                                    </a>
                                    <a href="{{ route('services.show', $service->slug) }}" class="block w-full text-center mt-3 text-sm font-semibold text-slate-500 underline hover:text-slate-700">বিস্তারিত দেখুন</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16">
                            <h3 class="text-2xl font-bold text-slate-400">কোনো service package পাওয়া যাচ্ছে না।</h3>
                            <p class="text-slate-500 mt-2">পরে আবার দেখুন অথবা আমাদের সাথে সরাসরি যোগাযোগ করুন।</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- CLOSING CTA --}}
        <section class="bg-primary-800 text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
                <h2 class="font-bangla text-3xl md:text-4xl font-bold leading-tight">কোন package আপনার জন্য সঠিক তা নিশ্চিত নন?</h2>
                <p class="mt-4 text-primary-100 leading-relaxed font-bangla">আমাদের টিম আপনার profile review করে সঠিক pathway বুঝতে সাহায্য করবে।</p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('study-in-china.consultation') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-primary-700 font-bold rounded-xl hover:bg-primary-50 transition-colors text-lg">Book Consultation</a>
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white/40 text-white font-bold rounded-xl hover:bg-white/10 transition-colors text-lg">WhatsApp Our Team</a>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
