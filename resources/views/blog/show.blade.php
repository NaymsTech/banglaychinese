<x-app-layout
    :metaTitle="$post->title . ' | Banglay Chinese'"
    :metaDescription="($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 155))"
    :canonicalUrl="route('posts.show', $post->slug)"
>
    <article>
        {{-- Post Hero --}}
        <header class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-14 text-white sm:py-20">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
                    @if($post->category)
                        <span class="rounded-full bg-white/15 px-3 py-1 font-bold backdrop-blur">{{ $post->category->name }}</span>
                    @endif
                    @if($post->hsk_level)
                        <span class="rounded-full bg-accent-600 px-3 py-1 font-bold">HSK {{ $post->hsk_level }}</span>
                    @endif
                    <span class="text-emerald-200">{{ $post->published_at?->format('F d, Y') }}</span>
                </div>
                <h1 class="mt-5 text-3xl font-extrabold leading-tight font-display sm:text-4xl">{{ $post->title }}</h1>
                @if($post->author)
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-300 font-bold text-emerald-950">
                            {{ strtoupper(substr($post->author->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold">{{ $post->author->name }}</p>
                            <p class="text-xs text-emerald-200">Banglay Chinese মেন্টর</p>
                        </div>
                    </div>
                @endif
            </div>
        </header>

        {{-- Content --}}
        <div class="bg-white py-12 sm:py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                @if($post->featured_image)
                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="mb-10 w-full rounded-3xl object-cover shadow-lg">
                @endif

                <div class="prose prose-lg max-w-none prose-headings:font-display prose-headings:text-slate-900 prose-p:text-slate-600 prose-a:text-primary-800 prose-strong:text-slate-900">
                    {!! $post->content !!}
                </div>

                {{-- Share / CTA --}}
                <div class="mt-12 rounded-3xl bg-[#F0FDF4] p-8 ring-1 ring-emerald-100">
                    <h2 class="text-2xl font-extrabold text-slate-900 font-display">এই আর্টিকেলটি কি helpful ছিল?</h2>
                    <p class="mt-2 text-slate-500">চীনা ভাষা শেখা বা চায়না স্কলারশিপ নিয়ে আরও জানতে আমাদের সাথে যোগাযোগ করুন।</p>
                    <div class="mt-6 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('study-in-china') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-accent-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-accent-600/25 transition hover:bg-accent-700">
                            🎓 Study in China
                        </a>
                        <a href="https://wa.me/{{ \App\Services\SettingsService::get('whatsapp_number', '8618223249514') }}?text={{ urlencode('হ্যালো, ব্লগ পড়ে জানতে চাই...') }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-[#25D366] px-6 py-3 text-sm font-bold text-[#148a3f] transition hover:bg-[#25D366] hover:text-white">
                            💬 WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </article>

    {{-- Related Posts --}}
    @if($related->isNotEmpty())
        <section class="bg-[#F0FDF4] py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-extrabold text-slate-900 font-display sm:text-3xl">আরও পড়ুন</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach($related as $item)
                        <a href="{{ route('posts.show', $item->slug) }}" class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex h-32 items-center justify-center bg-gradient-to-br from-primary-800 to-primary-950">
                                @if($item->featured_image)
                                    <img src="{{ asset('storage/' . $item->featured_image) }}" alt="{{ $item->title }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-3xl">🇨🇳</span>
                                @endif
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold leading-snug text-slate-900 transition group-hover:text-primary-800">{{ $item->title }}</h3>
                                <p class="mt-2 text-xs text-slate-400">{{ $item->published_at?->format('M d, Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-app-layout>
