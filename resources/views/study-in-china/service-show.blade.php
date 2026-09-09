@php
    use App\Services\SettingsService;
    $waNumber = SettingsService::get('whatsapp_number', '8618223249514');
    $features = is_array($service->features) ? $service->features : [];
@endphp
<x-app-layout>
    <x-slot name="metaTitle">{{ $metaTitle ?? $service->name }}</x-slot>
    <x-slot name="metaDescription">{{ $metaDescription ?? $service->short_description ?? $service->description }}</x-slot>
    <x-slot name="canonicalUrl">{{ $canonicalUrl ?? route('services.show', $service->slug) }}</x-slot>

    @push('meta')
        @php
            $serviceSchema = json_encode([
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                'name'        => $service->name,
                'serviceType' => 'Education Consulting',
                'description' => $service->short_description ?: $service->description,
                'provider'    => [
                    '@type' => 'Organization',
                    'name'  => 'Banglay Chinese',
                    'url'   => url('/'),
                ],
                'areaServed' => 'Bangladesh',
                'offers'     => [
                    '@type'           => 'Offer',
                    'price'           => $service->price,
                    'priceCurrency'   => 'BDT',
                    'availability'    => 'https://schema.org/InStock',
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        @endphp
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $service->name }} | Banglay Chinese">
        <meta property="og:description" content="{{ $service->short_description ?? $service->description }}">
        <meta property="og:url" content="{{ $canonicalUrl ?? route('services.show', $service->slug) }}">
        <script type="application/ld+json">{!! $serviceSchema !!}</script>
    @endpush

<div class="bg-surface font-sans text-text min-h-screen">
    {{-- Header --}}
    <section class="relative overflow-hidden bg-primary-800 text-white py-16">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-10 left-10 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-primary-600/40 rounded-full blur-3xl"></div>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('study-in-china') }}#services" class="inline-flex min-h-[44px] items-center text-primary-100 text-sm font-semibold hover:text-white">
                ← All Study in China Services
            </a>
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mt-4 font-bangla">{{ $service->name }}</h1>
            @if($service->duration)
                <p class="mt-3 inline-flex items-center gap-2 bg-white/10 text-primary-100 px-3 py-1 rounded-full text-sm font-semibold">🕒 {{ $service->duration }}</p>
            @endif
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        @if($service->short_description)
            <p class="text-lg text-gray-700 font-medium mb-6">{{ $service->short_description }}</p>
        @endif

        @if($service->description)
            <div class="prose prose-slate max-w-none text-gray-700 leading-relaxed mb-8">
                {!! $service->description !!}
            </div>
        @endif

        @if($features)
            <h2 class="text-2xl font-extrabold text-gray-900 mb-4">এই প্যাকেজে যা যা থাকছে</h2>
            <ul class="space-y-3">
                @foreach($features as $feature)
                    <li class="flex items-start gap-3 bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
                        <svg class="w-6 h-6 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                        <span class="text-gray-800">{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Price & CTA --}}
        <div class="mt-10 bg-white rounded-2xl shadow-md border border-slate-100 p-5 sm:p-8 text-center">
            <span class="text-4xl sm:text-5xl font-extrabold text-gray-900">৳{{ number_format($service->price) }}</span>
            <span class="text-gray-500 text-sm block mt-1">one-time payment</span>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('study-in-china.consultation', ['service' => $service->slug]) }}"
                   class="bg-primary-600 text-white font-bold px-8 py-4 rounded-xl hover:bg-primary-700 transition-all duration-200 shadow-sm">
                    {{ $service->cta_label }} →
                </a>
                <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('আমি ' . $service->name . ' প্যাকেজ সম্পর্কে জানতে চাই') }}" target="_blank" rel="noopener noreferrer"
                   class="bg-green-600 text-white font-bold px-8 py-4 rounded-xl hover:bg-green-700 transition-all duration-200 shadow-lg shadow-green-200 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
            </div>
        </div>
    </section>
</div>
</x-app-layout>
