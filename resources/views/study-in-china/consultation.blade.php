@php
    use App\Services\SettingsService;
    $waNumber = SettingsService::get('whatsapp_number', '8618223249514');
@endphp
<x-app-layout>
    <x-slot name="metaTitle">Free Study in China Consultation | Apply Now — Banglay Chinese</x-slot>
    <x-slot name="metaDescription">ফ্রি স্টাডি ইন চায়না কনসালটেশন — আপনার প্রোফাইল মূল্যায়ন, স্কলারশিপ গাইডেন্স ও আবেদনের জন্য আমাদের এক্সপার্ট মেন্টরের সাথে সরাসরি যোগাযোগ করুন।</x-slot>

    {{-- PAGE HEADER --}}
    <section class="bg-[#0F5132] py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-2xl font-extrabold text-white sm:text-3xl lg:text-4xl" style="font-family: 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;">
                ফ্রি কনসালটেশন ও অ্যাপ্লিকেশন ফর্ম
            </h1>
            <p class="mt-3 text-base text-emerald-100 sm:text-lg">
                আপনার তথ্য জমা দিন — আমাদের টিম আপনার প্রোফাইল review করে পরবর্তী ধাপ সম্পর্কে যোগাযোগ করবে
            </p>
        </div>
    </section>

    {{-- BREADCRUMB --}}
    <div class="border-b border-emerald-100 bg-[#F2FAF5]">
        <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500">
                <a href="{{ route('home') }}" class="transition hover:text-[#0F5132]">Home</a>
                <span>/</span>
                <a href="{{ route('study-in-china') }}" class="transition hover:text-[#0F5132]">Study in China</a>
                <span>/</span>
                <span class="text-[#0F5132]">Free Consultation</span>
            </nav>
        </div>
    </div>

    {{-- SPLIT LAYOUT: FORM + TRUST SIDEBAR --}}
    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-5">

                {{-- FORM COLUMN (3/5) --}}
                <div class="lg:col-span-3">
                    <div class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-lg shadow-emerald-100/50 sm:p-8">
                        <h2 class="text-xl font-extrabold text-[#0F5132] sm:text-2xl" style="font-family: 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;">
                            আপনার তথ্য জমা দিন
                        </h2>
                        <p class="mt-2 text-sm text-slate-500">* চিহ্নিত ফিল্ডগুলো আবশ্যক</p>

                        @if(session('success'))
                        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                            {{ session('success') }}
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700">
                            <ul class="list-disc pl-4 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form action="{{ route('study-in-china.apply') }}" method="POST" class="mt-6 space-y-5">
                            @csrf

                            {{-- Full Name --}}
                            <div>
                                <label for="name" class="mb-1.5 block text-sm font-bold text-slate-700">সম্পূর্ণ নাম (Full Name) <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20"
                                    placeholder="আপনার সম্পূর্ণ নাম লিখুন">
                            </div>

                            {{-- Phone / WhatsApp --}}
                            <div>
                                <label for="phone" class="mb-1.5 block text-sm font-bold text-slate-700">ফোন নম্বর / WhatsApp (Phone Number) <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20"
                                    placeholder="+8801XXXXXXXXX">
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-bold text-slate-700">ইমেইল (Email Address) <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20"
                                    placeholder="example@email.com">
                            </div>

                            {{-- Interested Service --}}
                            <div>
                                <label for="interested_service_id" class="mb-1.5 block text-sm font-bold text-slate-700">আগ্রহী সার্ভিস (Which service are you interested in?)</label>
                                <select name="interested_service_id" id="interested_service_id"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20">
                                    @php
                                        $preselectedServiceId = old('interested_service_id', optional(\App\Models\Service::where('slug', request('service'))->first())->id);
                                    @endphp
                                    <option value="">-- বেছে নিন (optional) --</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ (string) $preselectedServiceId === (string) $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Highest Qualification --}}
                            <div>
                                <label for="educational_background" class="mb-1.5 block text-sm font-bold text-slate-700">সর্বোচ্চ শিক্ষাগত যোগ্যতা (Highest Qualification) <span class="text-red-500">*</span></label>
                                <select name="educational_background" id="educational_background" required
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20">
                                    <option value="">-- বেছে নিন --</option>
                                    <option value="SSC" {{ old('educational_background') == 'SSC' ? 'selected' : '' }}>SSC / O-Level</option>
                                    <option value="HSC" {{ old('educational_background') == 'HSC' ? 'selected' : '' }}>HSC / A-Level</option>
                                    <option value="Bachelor" {{ old('educational_background') == 'Bachelor' ? 'selected' : '' }}>Bachelor's Degree</option>
                                    <option value="Master" {{ old('educational_background') == 'Master' ? 'selected' : '' }}>Master's Degree</option>
                                    <option value="PhD" {{ old('educational_background') == 'PhD' ? 'selected' : '' }}>PhD / Doctorate</option>
                                </select>
                            </div>

                            {{-- GPA / CGPA --}}
                            <div>
                                <label for="gpa" class="mb-1.5 block text-sm font-bold text-slate-700">GPA / CGPA</label>
                                <input type="text" name="gpa" id="gpa" value="{{ old('gpa') }}"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20"
                                    placeholder="e.g., 3.80 out of 4.00">
                            </div>

                            {{-- Desired Program --}}
                            <div>
                                <label for="target_course" class="mb-1.5 block text-sm font-bold text-slate-700">কাঙ্ক্ষিত প্রোগ্রাম (Desired Program) <span class="text-red-500">*</span></label>
                                <select name="target_course" id="target_course" required
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20">
                                    <option value="">-- বেছে নিন --</option>
                                    <option value="Chinese Language" {{ old('target_course') == 'Chinese Language' ? 'selected' : '' }}>Chinese Language Program</option>
                                    <option value="Bachelors" {{ old('target_course') == 'Bachelors' ? 'selected' : '' }}>Bachelor's Degree</option>
                                    <option value="Masters" {{ old('target_course') == 'Masters' ? 'selected' : '' }}>Master's Degree</option>
                                    <option value="PhD" {{ old('target_course') == 'PhD' ? 'selected' : '' }}>PhD / Doctorate</option>
                                    <option value="Diploma" {{ old('target_course') == 'Diploma' ? 'selected' : '' }}>Diploma / Certificate</option>
                                </select>
                            </div>

                            {{-- Preferred Intake --}}
                            <div>
                                <label for="preferred_intake" class="mb-1.5 block text-sm font-bold text-slate-700">টার্গেট ইনটেক (Target Intake)</label>
                                <select name="preferred_intake" id="preferred_intake"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20">
                                    <option value="">-- বেছে নিন --</option>
                                    <option value="March 2027" {{ old('preferred_intake') == 'March 2027' ? 'selected' : '' }}>March 2027</option>
                                    <option value="September 2027" {{ old('preferred_intake') == 'September 2027' ? 'selected' : '' }}>September 2027</option>
                                    <option value="March 2028" {{ old('preferred_intake') == 'March 2028' ? 'selected' : '' }}>March 2028</option>
                                </select>
                            </div>

                            {{-- HSK / English Level --}}
                            <div>
                                <label for="hsk_english_level" class="mb-1.5 block text-sm font-bold text-slate-700">HSK / English Proficiency Level</label>
                                <input type="text" name="hsk_english_level" id="hsk_english_level" value="{{ old('hsk_english_level') }}"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20"
                                    placeholder="e.g., HSK Level 3 / IELTS 6.5 / None">
                            </div>

                            {{-- Statement / Message --}}
                            <div>
                                <label for="statement_of_purpose" class="mb-1.5 block text-sm font-bold text-slate-700">স্টেটমেন্ট / মেসেজ (Statement / Message) <span class="text-red-500">*</span></label>
                                <textarea name="statement_of_purpose" id="statement_of_purpose" rows="4" required
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 transition focus:border-[#0F5132] focus:ring-2 focus:ring-[#0F5132]/20"
                                    placeholder="কেন আপনি চীন-এ পড়তে আগ্রহী? আপনার পছন্দের সাবজেক্ট ও যেকোনো প্রশ্ন উল্লেখ করুন...">{{ old('statement_of_purpose') }}</textarea>
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                class="w-full rounded-xl bg-[#0F5132] px-6 py-3.5 text-base font-bold text-white shadow-lg shadow-[#0F5132]/20 transition hover:bg-[#0d452c] active:scale-[0.98]">
                                আবেদন জমা দিন / Free Evaluation
                            </button>
                        </form>
                    </div>
                </div>

                {{-- TRUST SIDEBAR (2/5) --}}
                <div class="lg:col-span-2">
                    <div class="space-y-6">
                        {{-- Why Choose Us --}}
                        <div class="rounded-2xl border border-emerald-100 bg-[#F2FAF5] p-6">
                            <h3 class="text-lg font-extrabold text-[#0F5132]" style="font-family: 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;">কেন আমাদের সাথে কাজ করবেন?</h3>
                            <ul class="mt-4 space-y-3">
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#0F5132] text-xs text-white">✓</span>
                                    <span class="text-sm font-medium text-slate-600">100% ফ্রি প্রোফাইল অ্যাসেসমেন্ট</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#0F5132] text-xs text-white">✓</span>
                                    <span class="text-sm font-medium text-slate-600">চায়নায় অধ্যায়নরত বাংলাদেশি মেন্টর</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#0F5132] text-xs text-white">✓</span>
                                    <span class="text-sm font-medium text-slate-600">CSC, প্রভিন্সিয়াল ও ইউনিভার্সিটি স্কলারশিপে স্পেশালাইজড</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#0F5132] text-xs text-white">✓</span>
                                    <span class="text-sm font-medium text-slate-600">সম্পূর্ণ ভিসা ও ডকুমেন্টেশন গাইডেন্স</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#0F5132] text-xs text-white">✓</span>
                                    <span class="text-sm font-medium text-slate-600">কোনো লুকানো খরচ নেই — সম্পূর্ণ ট্রান্সপারেন্ট প্রসেস</span>
                                </li>
                            </ul>
                        </div>

                        {{-- WhatsApp Quick Contact --}}
                        <div class="rounded-2xl bg-[#0F5132] p-6 text-center text-white">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white/10 text-3xl">💬</div>
                            <h3 class="mt-4 text-lg font-bold">WhatsApp-এ আমাদের সাথে কথা বলুন</h3>
                            <p class="mt-2 text-sm leading-relaxed text-emerald-100">
                                সরাসরি WhatsApp-এ আমাদের টিমের সাথে যোগাযোগ করতে পারেন — এটি একটি secondary contact option।
                            </p>
                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                                class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-[#1fb857]">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp চ্যাট
                            </a>
                        </div>

                        {{-- Timelines / Contact Info --}}
                        <div class="rounded-2xl border border-emerald-100 bg-white p-6">
                            <h3 class="text-lg font-extrabold text-[#0F5132]" style="font-family: 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;">এরপর যা হবে</h3>
                            <div class="mt-4 space-y-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F2FAF5] text-lg">⏱️</span>
                                    <span class="font-medium text-slate-600">আপনার তথ্য review করে আমাদের টিম যোগাযোগ করবে</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F2FAF5] text-lg">📅</span>
                                    <span class="font-medium text-slate-600">শনিবার–বৃহস্পতিবার (বাংলাদেশ সময় সকাল ৯টা–রাত ১০টা)</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F2FAF5] text-lg">🛡️</span>
                                    <span class="font-medium text-slate-600">আপনার তথ্য সম্পূর্ণ সুরক্ষিত (GDPR compliant)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</x-app-layout>
