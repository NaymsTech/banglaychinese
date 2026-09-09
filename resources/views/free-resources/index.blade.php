<x-app-layout
    :metaTitle="$metaTitle"
    :metaDescription="$metaDescription"
>
    {{-- Hero Section --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-white">
        <div aria-hidden="true" class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-emerald-200/50 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-10 h-72 w-72 rounded-full bg-teal-100/60 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 md:py-20 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-4 py-1.5 text-xs font-bold tracking-wide text-emerald-700 uppercase shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0l3-3m-3 3l-3-3m8.25 6.75h.008v.008h-.008v-.008zM4.5 19.5h15a.75.75 0 00.75-.75v-8.25a.75.75 0 00-.75-.75h-15a.75.75 0 00-.75.75v8.25c0 .414.336.75.75.75z"/>
                </svg>
                Free Resources
            </span>
            <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-gray-900 md:text-5xl">Free Study Resources</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg leading-relaxed text-gray-600">
                Access free PDFs, video lessons, and study guides to accelerate your Chinese learning journey.
            </p>
        </div>
    </div>

    {{-- Resources grouped by category --}}
    <div class="bg-white pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($resources->isEmpty())
                <div class="flex flex-col items-center gap-4 py-24 text-center">
                    <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50" aria-hidden="true">
                        <svg class="h-8 w-8 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </span>
                    <p class="text-lg font-medium text-gray-500">New resources are being added soon!</p>
                    <p class="max-w-md text-sm text-gray-400">Check back regularly — we publish new study guides, PDFs and video lessons every week.</p>
                </div>
            @else
                @foreach ($resources as $category => $items)
                    <section aria-labelledby="category-{{ Str::slug($category) }}">
                        <div class="mb-6 mt-12 flex items-center gap-3 first:mt-0">
                            <h2 id="category-{{ Str::slug($category) }}" class="border-l-4 border-emerald-600 pl-4 text-2xl font-bold text-gray-800">
                                {{ $category }}
                            </h2>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                {{ $items->count() }} {{ Str::plural('resource', $items->count()) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $resource)
                                @if ($resource->resource_type === 'video')
                                    {{-- Video card — watchable by everyone, no login required --}}
                                    <article class="relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:shadow-xl">
                                        <span class="pointer-events-none absolute right-3 top-3 z-10 rounded-md bg-red-600 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white uppercase">Video</span>
                                        <div class="aspect-video w-full bg-gray-900">
                                            <iframe src="{{ $resource->embed_url }}"
                                                    class="h-full w-full"
                                                    title="{{ $resource->title }}"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    allowfullscreen
                                                    loading="lazy"></iframe>
                                        </div>
                                        <div class="flex flex-1 flex-col p-6">
                                            <h3 class="text-lg font-bold text-gray-900">{{ $resource->title }}</h3>
                                            @if ($resource->description)
                                                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $resource->description }}</p>
                                            @endif
                                        </div>
                                    </article>
                                @elseif ($resource->resource_type === 'pdf')
                                    {{-- PDF card — download requires login --}}
                                    <article class="relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:shadow-xl">
                                        <span class="pointer-events-none absolute right-3 top-3 z-10 rounded-md bg-red-600 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white uppercase">PDF</span>
                                        <div class="flex items-center justify-center bg-red-50 p-8">
                                            <svg class="h-16 w-16 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3h-6"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-1 flex-col p-6">
                                            <h3 class="text-lg font-bold text-gray-900">{{ $resource->title }}</h3>
                                            @if ($resource->description)
                                                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $resource->description }}</p>
                                            @endif

                                            @if ($resource->file_path)
                                                <a href="{{ asset('storage/' . $resource->file_path) }}" download
                                                   class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 font-semibold text-white transition-colors hover:bg-emerald-700">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                                    </svg>
                                                    Download PDF
                                                </a>
                                            @endif
                                        </div>
                                    </article>
                                @else
                                    {{-- Link card --}}
                                    <article class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:shadow-xl">
                                        <div class="flex flex-1 flex-col p-6">
                                            <span class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50" aria-hidden="true">
                                                <svg class="h-6 w-6 text-sky-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                                                </svg>
                                            </span>
                                            <h3 class="text-lg font-bold text-gray-900">{{ $resource->title }}</h3>
                                            @if ($resource->description)
                                                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $resource->description }}</p>
                                            @endif
                                            @if ($resource->embed_url)
                                                <a href="{{ $resource->embed_url }}" target="_blank" rel="noopener"
                                                   class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-emerald-600 px-4 py-2.5 font-semibold text-emerald-700 transition-colors hover:bg-emerald-600 hover:text-white">
                                                    Open Resource
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
