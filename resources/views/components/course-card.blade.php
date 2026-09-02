@props(['course'])

@php
    $waNumber = \App\Services\SettingsService::get('whatsapp_number', '8618223249514');

    // Dynamic course-based badge
    $badge = match ($course->slug) {
        'fun-chinese-for-kids'     => ['label' => 'Kids Program', 'filter' => 'kids', 'icon' => '🧒'],
        'chinese-speaking-mastery' => ['label' => 'Popular', 'filter' => 'speaking', 'icon' => '🗣️'],
        'hsk-intensive-program'    => ['label' => 'Intensive', 'filter' => 'scholarship', 'icon' => '⚡'],
        default                    => ['label' => 'HSK Prep', 'filter' => 'hsk', 'icon' => '📖'],
    };

    $headerGradient = 'bg-gradient-to-br from-primary-800 to-primary-950';

    $descriptionText = $course->description
        ? \Illuminate\Support\Str::limit(strip_tags($course->description), 110)
        : 'আরো বিস্তারিত জানতে ক্লিক করুন।';
@endphp

<article
    class="course-card group w-full h-full flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm border border-emerald-100/60 transition duration-300 hover:-translate-y-1.5 hover:shadow-xl"
    data-filter="{{ $badge['filter'] }}"
>
    {{-- Gradient Header --}}
    <div class="relative h-40 {{ $headerGradient }} p-6">
        <div class="pointer-events-none absolute -bottom-8 -right-8 h-28 w-28 rounded-full bg-white/10"></div>
        <div class="flex items-start justify-between">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white backdrop-blur">
                {!! $badge['icon'] !!} {{ $badge['label'] }}
            </span>
            @if($course->is_featured)
                <span class="rounded-full bg-amber-400 px-2.5 py-1 text-xs font-extrabold text-amber-950">★ FEATURED</span>
            @endif
        </div>
        <div class="mt-10 flex flex-wrap items-center gap-2">
            @if($course->hsk_level)
                <span class="rounded-lg bg-white/20 px-2.5 py-1 text-xs font-bold text-white">HSK {{ $course->hsk_level }}</span>
            @endif
            <span class="rounded-lg bg-accent-600 px-2.5 py-1 text-xs font-extrabold text-white">
                @if($course->price > 0)
                    ৳{{ number_format($course->price) }}
                @else
                    ফ্রি
                @endif
            </span>
            @if($course->duration_weeks)
                <span class="rounded-lg bg-emerald-400/90 px-2.5 py-1 text-xs font-bold text-emerald-950">{{ $course->duration_weeks }} সপ্তাহ</span>
            @endif
        </div>
    </div>

    {{-- Card Body --}}
    <div class="flex flex-1 flex-col p-6">
        <h3 class="text-xl font-bold text-slate-900 transition group-hover:text-primary-800">
            <a href="{{ route('courses.show', $course->slug) }}">{{ $course->title }}</a>
        </h3>
        <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-500">
            {{ $descriptionText }}
        </p>

        {{-- Feature list --}}
        <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-primary-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                লাইভ ছোট গ্রুপ ক্লাস
            </li>
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-primary-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                নিয়মিত মক টেস্ট ও ফিডব্যাক
            </li>
        </ul>

        {{-- Urgency / Social Proof Ribbon --}}
        @if($course->is_featured)
            <div class="mt-4 flex items-center gap-2 rounded-xl bg-green-50 px-3 py-2 text-xs font-semibold text-green-800">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-green-200 text-xs">⚡</span>
                <span>{{ $course->duration_weeks ? $course->duration_weeks . ' সপ্তাহের ইন্টেন্সিভ প্রোগ্রাম' : 'সর্বোচ্চ চাহিদাসম্পন্ন কোর্স' }}</span>
            </div>
        @endif

        {{-- Dual CTA Buttons --}}
        <div class="mt-4 grid grid-cols-2 gap-3">
            <a href="{{ route('courses.show', $course->slug) }}"
               class="inline-flex items-center justify-center gap-1.5 rounded-full border-2 border-primary-800 px-4 py-2.5 text-sm font-bold text-primary-800 transition hover:bg-emerald-50">
                বিস্তারিত দেখুন
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('আমি ' . $course->title . ' কোর্সে ভর্তি হতে চাই') }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center justify-center gap-1.5 rounded-full bg-[#25D366] px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-green-500/25 transition hover:bg-[#1fb857]">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
        </div>
    </div>
</article>

