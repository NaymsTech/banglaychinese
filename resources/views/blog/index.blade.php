<x-app-layout
    :metaTitle="'Blog | Banglay Chinese'"
    :metaDescription="'Learn Chinese tips, HSK preparation guides, and China scholarship updates in Bengali — Banglay Chinese Blog.'"
>
    <section class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-16 text-white sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <span class="text-sm font-bold uppercase tracking-widest text-emerald-300">Banglay Chinese ব্লগ</span>
                <h1 class="mt-3 text-3xl font-extrabold font-display sm:text-4xl">চীনা ভাষা, HSK ও চায়না স্কলারশিপ গাইড</h1>
                <p class="mt-4 text-lg text-emerald-100">চীনে পড়তে যাওয়া, HSK প্রস্তুতি আর ভাষা শেখার টিপস — সব বাংলায়।</p>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($posts->isEmpty())
                <p class="py-16 text-center text-slate-400">কোনো ব্লগ পোস্ট পাওয়া যায়নি। শীঘ্রই নতুন আর্টিকেল আসছে!</p>
            @else
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($posts as $post)
                        <article class="group flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">
                            @if($post->featured_image)
                                <div class="h-48 overflow-hidden">
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                </div>
                            @else
                                <div class="flex h-48 items-center justify-center bg-gradient-to-br from-primary-800 to-primary-950">
                                    <span class="text-5xl">🇨🇳</span>
                                </div>
                            @endif
                            <div class="flex flex-1 flex-col p-6">
                                <div class="flex items-center gap-3 text-xs font-semibold text-slate-400">
                                    @if($post->category)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 font-bold text-primary-800">{{ $post->category->name }}</span>
                                    @endif
                                    @if($post->hsk_level)
                                        <span class="rounded-full bg-red-50 px-3 py-1 font-bold text-accent-600">HSK {{ $post->hsk_level }}</span>
                                    @endif
                                    <span>{{ $post->published_at?->format('M d, Y') }}</span>
                                </div>
                                <h2 class="mt-4 text-xl font-bold leading-snug text-slate-900 transition group-hover:text-primary-800">
                                    <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                                </h2>
                                <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-500">
                                    {{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}
                                </p>
                                <a href="{{ route('posts.show', $post->slug) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-primary-800 transition hover:gap-3">
                                    পড়ুন
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
