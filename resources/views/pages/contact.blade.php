<x-app-layout
    :metaTitle="'Contact Us | Banglay Chinese'"
    :metaDescription="'Contact Banglay Chinese — WhatsApp, email, or send a message. We respond quickly to help you learn Chinese and get China scholarships.'"
>
    {{-- ===== Page Header ===== --}}
    <section class="bg-gradient-to-br from-emerald-800 via-emerald-900 to-emerald-950 py-16 text-white sm:py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="text-sm font-bold uppercase tracking-widest text-emerald-300">Contact</span>
            <h1 class="mt-3 font-display text-3xl font-extrabold sm:text-5xl">Get in Touch</h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg leading-relaxed text-emerald-100">
                Questions about our Chinese courses, HSK preparation, scholarships, or studying in China?
                Send us a message — we reply quickly.
            </p>
        </div>
    </section>

    {{-- ===== Contact Info + Form ===== --}}
    <section class="bg-gray-50 py-16 sm:py-20">
        @php
            $waNumber = \App\Services\SettingsService::get('whatsapp_number', '8618223249514');
            $waDisplay = \App\Support\WhatsAppNumber::display($waNumber);
            $contactEmail = \App\Services\SettingsService::get('contact_email', 'info@banglaychinese.com');
            $address = \App\Services\SettingsService::get('physical_address', 'Chongqing, China');

            $fieldClass = 'w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500';
        @endphp

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- ===== Left column: contact information ===== --}}
                <div class="space-y-6">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                        <h2 class="text-lg font-bold text-slate-900">Contact Information</h2>
                        <p class="mt-1 text-sm text-slate-500">Prefer to reach us directly? These are the fastest ways.</p>

                        <ul class="mt-6 space-y-6">
                            <li class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">WhatsApp</p>
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                                       class="mt-0.5 block font-bold text-slate-900 transition hover:text-emerald-700">{{ $waDisplay }}</a>
                                    <p class="mt-0.5 text-xs text-slate-500">Fastest response — usually within a few hours.</p>
                                </div>
                            </li>

                            <li class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</p>
                                    <a href="mailto:{{ $contactEmail }}"
                                       class="mt-0.5 block break-all font-bold text-slate-900 transition hover:text-emerald-700">{{ $contactEmail }}</a>
                                    <p class="mt-0.5 text-xs text-slate-500">For detailed questions and documents.</p>
                                </div>
                            </li>

                            <li class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm4.5 0c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Address</p>
                                    <p class="mt-0.5 font-bold text-slate-900">{{ $address }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Online classes for students anywhere in the world.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl bg-emerald-700 p-6 text-white shadow-sm sm:p-8">
                        <h3 class="text-lg font-bold">Prefer WhatsApp?</h3>
                        <p class="mt-2 text-sm leading-relaxed text-emerald-100">
                            Tap below and we will reply as fast as possible during business hours.
                        </p>
                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                           class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-bold text-emerald-800 shadow-md transition hover:bg-emerald-50">
                            <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Chat on WhatsApp
                        </a>
                    </div>
                </div>

                {{-- ===== Right column: contact form ===== --}}
                <div class="lg:col-span-2">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-10">
                        <h2 class="font-display text-2xl font-extrabold text-slate-900">Send Us a Message</h2>
                        <p class="mt-2 text-sm text-slate-500">Fill in the form below and our team will get back to you within 24 hours.</p>

                        <div class="mt-6">
                            <x-form-feedback />
                            <x-form-feedback type="error" />
                        </div>

                        <form id="contact-form" method="POST" action="{{ route('contact.send') }}" class="mt-8 space-y-5">
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Your full name"
                                           class="{{ $fieldClass }} @error('name') border-red-400 @enderror">
                                    @error('name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email <span class="text-red-500">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com"
                                           class="{{ $fieldClass }} @error('email') border-red-400 @enderror">
                                    @error('email')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label for="phone" class="mb-1.5 block text-sm font-semibold text-slate-700">WhatsApp Number <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="+880 1XXX-XXXXXX"
                                       class="{{ $fieldClass }} @error('phone') border-red-400 @enderror">
                                @error('phone')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="topic" class="mb-1.5 block text-sm font-semibold text-slate-700">Service Interest <span class="text-red-500">*</span></label>
                                <select id="topic" name="topic" required
                                        class="{{ $fieldClass }} @error('topic') border-red-400 @enderror">
                                    <option value="" disabled {{ old('topic') ? '' : 'selected' }}>Select a topic…</option>
                                    @foreach (['General Inquiry', 'Chinese Language Course', 'Study in China Consultancy', 'Digital Products', 'Other'] as $topicOption)
                                        <option value="{{ $topicOption }}" {{ old('topic') === $topicOption ? 'selected' : '' }}>{{ $topicOption }}</option>
                                    @endforeach
                                </select>
                                @error('topic')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="message" class="mb-1.5 block text-sm font-semibold text-slate-700">Message <span class="text-red-500">*</span></label>
                                <textarea id="message" name="message" rows="5" required placeholder="How can we help you?"
                                          class="{{ $fieldClass }} @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                                @error('message')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <button type="submit" id="contact-submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-emerald-700 px-8 py-4 text-base font-bold text-white shadow-lg shadow-emerald-700/25 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
                                <span id="contact-submit-label">Send Message</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('contact-form')?.addEventListener('submit', function () {
            const button = document.getElementById('contact-submit');
            const label = document.getElementById('contact-submit-label');
            button.disabled = true;
            label.textContent = 'Sending…';
        });
    </script>
</x-app-layout>
