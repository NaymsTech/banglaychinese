<x-app-layout
    :metaTitle="'Contact Us | Banglay Chinese'"
    :metaDescription="'Contact Banglay Chinese — WhatsApp, email, or send a message. We respond quickly to help you learn Chinese and get China scholarships.'"
>
    <section class="bg-gradient-to-br from-[#0F5132] to-[#052e16] py-16 text-white sm:py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="text-sm font-bold uppercase tracking-widest text-emerald-300">যোগাযোগ</span>
            <h1 class="mt-3 text-3xl font-extrabold font-display sm:text-5xl">আমরা আছি আপনার পাশে</h1>
            <p class="mt-5 text-lg text-emerald-100">যেকোনো প্রশ্ন থাকলে — কোর্স, HSK, স্কলারশিপ বা ভর্তি — আজই যোগাযোগ করুন।</p>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        @php
            $waNumber = \App\Services\SettingsService::get('whatsapp_number', '8618223249514');
            $contactEmail = \App\Services\SettingsService::get('contact_email', 'info@banglaychinese.com');
            $address = \App\Services\SettingsService::get('physical_address', 'Chongqing, China');
        @endphp
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-5">
                {{-- Contact Info --}}
                <div class="space-y-6 lg:col-span-2">
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                       class="flex items-start gap-4 rounded-3xl bg-[#F0FDF4] p-6 ring-1 ring-emerald-100 transition hover:shadow-lg">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#25D366] text-2xl">💬</div>
                        <div>
                            <h2 class="font-bold text-slate-900">WhatsApp</h2>
                            <p class="mt-1 text-sm text-slate-500 break-all">+{{ $waNumber }}</p>
                            <p class="mt-0.5 text-xs font-semibold text-[#148a3f]">সবচেয়ে দ্রুত রেসপন্স এখানে</p>
                        </div>
                    </a>
                    <a href="mailto:{{ $contactEmail }}"
                       class="flex items-start gap-4 rounded-3xl bg-[#F0FDF4] p-6 ring-1 ring-emerald-100 transition hover:shadow-lg">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-800 text-2xl">📧</div>
                        <div>
                            <h2 class="font-bold text-slate-900">Email</h2>
                            <p class="mt-1 text-sm text-slate-500 break-all">{{ $contactEmail }}</p>
                        </div>
                    </a>
                    <div class="flex items-start gap-4 rounded-3xl bg-[#F0FDF4] p-6 ring-1 ring-emerald-100">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-accent-600 text-2xl">📍</div>
                        <div>
                            <h2 class="font-bold text-slate-900">অবস্থান</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $address }}<br>(অনলাইনে বাংলাদেশ ও বিশ্বের যেকোনো প্রান্ত থেকে)</p>
                        </div>
                    </div>
                </div>

                {{-- Message Form --}}
                <div class="lg:col-span-3">
                    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
                        <h2 class="text-2xl font-extrabold text-slate-900 font-display">মেসেজ পাঠান</h2>
                        <p class="mt-2 text-sm text-slate-500">আপনার নাম, ফোন ও প্রশ্ন লিখুন — আমরা ২৪ ঘণ্টার মধ্যে রেসপন্স করব।</p>

                        @if(session('success'))
                            <div class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 ring-1 ring-emerald-200">
                                ✅ {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.send') }}" class="mt-6 space-y-5">
                            @csrf
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-1.5 block text-sm font-bold text-slate-700">আপনার নাম *</label>
                                    <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                           class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                                    @error('name') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="phone" class="mb-1.5 block text-sm font-bold text-slate-700">ফোন / WhatsApp *</label>
                                    <input type="text" id="phone" name="phone" required value="{{ old('phone') }}" placeholder="+8801XXXXXXXXX"
                                           class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                                    @error('phone') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-bold text-slate-700">ইমেইল (ঐচ্ছিক)</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                                @error('email') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="topic" class="mb-1.5 block text-sm font-bold text-slate-700">বিষয় *</label>
                                <select id="topic" name="topic" required
                                        class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                                    <option value="">— নির্বাচন করুন —</option>
                                    <option value="hsk" {{ old('topic') === 'hsk' ? 'selected' : '' }}>HSK কোর্স</option>
                                    <option value="speaking" {{ old('topic') === 'speaking' ? 'selected' : '' }}>স্পিকিং কোর্স</option>
                                    <option value="kids" {{ old('topic') === 'kids' ? 'selected' : '' }}>কিডস কোর্স</option>
                                    <option value="scholarship" {{ old('topic') === 'scholarship' ? 'selected' : '' }}>চায়না স্কলারশিপ</option>
                                    <option value="other" {{ old('topic') === 'other' ? 'selected' : '' }}>অন্যান্য</option>
                                </select>
                                @error('topic') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="message" class="mb-1.5 block text-sm font-bold text-slate-700">আপনার মেসেজ *</label>
                                <textarea id="message" name="message" rows="4" required placeholder="আপনার প্রশ্ন বা প্রয়োজনীয় তথ্য লিখুন..."
                                          class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">{{ old('message') }}</textarea>
                                @error('message') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent-600 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-accent-600/30 transition hover:bg-accent-700">
                                📨 মেসেজ পাঠান
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
