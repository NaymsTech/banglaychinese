<x-app-layout :metaTitle="$metaTitle" :metaDescription="$metaDescription">
    <section class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-10 text-white sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-4 text-sm text-emerald-200">
                <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <span class="text-white">Shop</span>
            </nav>
            <h1 class="text-2xl font-extrabold sm:text-3xl">Digital Shop</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-emerald-100">
                E-books, practice tests ও স্টাডি ম্যাটেরিয়াল — ডাউনলোড করুন আপনার প্রস্তুতির জন্য।
            </p>
        </div>
    </section>

    <section class="bg-white py-10 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($products->isEmpty())
                <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/50 p-12 text-center">
                    <p class="text-3xl">🛍️</p>
                    <h2 class="mt-4 text-lg font-extrabold text-slate-800">No products yet</h2>
                    <p class="mt-2 text-sm text-slate-500">New digital products will appear here soon. Stay tuned!</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($products as $product)
                        <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <a href="{{ route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]) }}" class="block aspect-[4/3] w-full overflow-hidden bg-emerald-50">
                                @if($product->cover_image)
                                    <img src="{{ asset('storage/'.$product->cover_image) }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-5xl">📘</div>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col p-5">
                                @if($product->category)
                                    <span class="self-start rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-[#0F5132]">{{ $product->category }}</span>
                                @endif
                                <h2 class="mt-3 font-bold leading-snug text-slate-800">
                                    <a href="{{ route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]) }}" class="transition hover:text-[#0F5132]">{{ $product->title }}</a>
                                </h2>
                                <div class="mt-auto flex items-center justify-between pt-5">
                                    <span class="text-lg font-extrabold text-[#0F5132]">৳{{ number_format($product->price, 2) }}</span>
                                    <a href="{{ route('checkout.unified', ['type' => 'product', 'slug' => $product->slug]) }}"
                                       class="inline-flex items-center gap-1 rounded-full bg-[#0F5132] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#0d452c]">
                                        Buy Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
