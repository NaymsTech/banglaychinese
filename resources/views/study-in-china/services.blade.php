@php
    use App\Services\SettingsService;
    $waNumber = SettingsService::get('whatsapp_number', '8618223249514');
@endphp
<x-app-layout>
    <x-slot name="metaTitle">Study in China Services — Admission & Scholarship Packages | Banglay Chinese</x-slot>
    <x-slot name="metaDescription">চীনে পড়তে যাওয়ার সম্পূর্ণ গাইডেন্স — Application Guide থেকে শুরু করে ১-বছরের Complete Pathway। আপনার স্কলারশিপ ও ভর্তি প্রক্রিয়া সহজ করুন।</x-slot>

<div class="bg-gradient-to-br from-red-50 via-white to-yellow-50">
    {{-- Hero Banner --}}
    <section class="relative bg-gradient-to-r from-red-700 to-red-600 text-white py-20 lg:py-28 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-yellow-300 rounded-full blur-3xl"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <span class="inline-block bg-red-800/40 text-red-100 px-4 py-1 rounded-full text-sm font-semibold mb-4">
                    🇨🇳 Study in China Services
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight mb-6">
                    চীনে পড়াশোনার <span class="text-yellow-300">সম্পূর্ণ সাপোর্ট</span>
                </h1>
                <p class="text-lg md:text-xl text-red-100 leading-relaxed">
                    আপনার স্বপ্নের চীনা বিশ্ববিদ্যালয়ে ভর্তি হতে যা যা প্রয়োজন — সবকিছু এক জায়গায়।
                    নিজে করুন বা আমাদের উপর ছেড়ে দিন, আপনার পছন্দমতো প্যাকেজ বেছে নিন।
                </p>
            </div>
        </div>
    </section>

    {{-- Why Study in China Banner --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-xl transition-shadow">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m0 0l-7-7m7 7l7-7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">Low Cost Education</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    চীনের বিশ্ববিদ্যালয়গুলোতে টিউশন ফি অনেক কম। স্কলারশিপের সুযোগ তো রয়েছেই,
                    যা Bangladesh-এর private university-এর চেয়েও সাশ্রয়ী।
                </p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-xl transition-shadow">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v6l4 3"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">World-Class Universities</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Tsinghua, Peking, Fudan, Zhejiang — China-র top universities গুলো global ranking-এ
                    শীর্ষে অবস্থান করছে। তাদের ডিগ্রি সারা বিশ্বে স্বীকৃত।
                </p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-xl transition-shadow">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">Global Career</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    চীনা ভাষা ও ডিগ্রি — এই কম্বিনেশন আপনাকে global job market-এ unique edge দেবে।
                    China-র economy world-এর 2nd largest, আর বাংলাদেশের সাথে trade relation ক্রমবর্ধমান।
                </p>
            </div>
        </div>
    </section>

    {{-- Pricing Ladder (Dynamic from DB) --}}
    <section id="pricing" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pb-24">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
                আপনার জন্য <span class="text-red-600">সঠিক প্যাকেজ</span> বেছে নিন
            </h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                আপনার প্রয়োজন ও বাজেট অনুযায়ী ৩টি প্যাকেজ থেকে বেছে নিন।
                প্রতিটি প্যাকেজ ডিজাইন করা হয়েছে ভিন্ন ভিন্ন প্রয়োজনের জন্য।
            </p>
        </div>

        @forelse($services as $service)
            @php
                // Determine the tier level based on price
                if ($service->price <= 30000) {
                    $tierLabel = 'Starter';
                    $tierBg = 'bg-white border-2 border-gray-200';
                    $tierBadge = 'bg-gray-100 text-gray-700';
                    $btnStyle = 'bg-white border-2 border-red-600 text-red-600 hover:bg-red-50';
                    $highlight = false;
                } elseif ($service->price <= 80000) {
                    $tierLabel = 'Most Popular';
                    $tierBg = 'bg-white border-2 border-red-500 shadow-xl shadow-red-100 scale-105 relative z-10';
                    $tierBadge = 'bg-red-600 text-white';
                    $btnStyle = 'bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200';
                    $highlight = true;
                } else {
                    $tierLabel = 'VIP';
                    $tierBg = 'bg-white border-2 border-gray-200';
                    $tierBadge = 'bg-gray-900 text-white';
                    $btnStyle = 'bg-red-700 text-white hover:bg-red-800 shadow-lg shadow-red-200';
                    $highlight = false;
                }
            @endphp

            <div class="{{ $tierBg }} rounded-2xl p-8 md:p-10 mb-8 hover:shadow-2xl transition-all duration-300 {{ $highlight ? 'ring-4 ring-red-100' : '' }}">
                @if ($highlight)
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-red-600 text-white px-5 py-1 rounded-full text-sm font-bold shadow-lg">
                        {{ $tierLabel }}
                    </div>
                @endif

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                    {{-- Left: Info --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <h3 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $service->title }}</h3>
                            @if($service->is_featured)
                                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Recommended
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-600 leading-relaxed mb-6 text-base">
                            {{ $service->description }}
                        </p>

                        {{-- Features for this tier --}}
                        <ul class="space-y-3 mb-8">
                            @if($service->price <= 30000)
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Step-by-step Application Guide</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Document Checklist & Templates</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">University Shortlisting Strategy</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Scholarship Interview Tips</span>
                                </li>
                            @elseif($service->price <= 80000)
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm font-semibold">Everything in Application Guide, plus:</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Personal Mentor Assigned</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Document Preparation & Verification</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Application Submission by Our Team</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Interview Preparation Sessions</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Visa Guidance Included</span>
                                </li>
                            @else
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm font-semibold">Everything in Complete Support, plus:</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">1-Year HSK Language Training</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Pre-Departure Cultural Orientation</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Accommodation Assistance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">First-Month Settlement Support in China</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                    <span class="text-gray-700 text-sm">Complete China Journey Guarantee</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Right: Price & CTA --}}
                    <div class="lg:w-72 flex-shrink-0 text-center lg:text-right">
                        <div class="mb-4">
                            <span class="text-4xl md:text-5xl font-extrabold text-gray-900">
                                ৳{{ number_format($service->price) }}
                            </span>
                            <span class="text-gray-500 text-sm block">one-time payment</span>
                        </div>

                        @if($service->consultation_link)
                            <a href="{{ $service->consultation_link }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="block w-full text-center {{ $btnStyle }} font-bold py-3.5 px-8 rounded-xl transition-all duration-200 text-lg">
                                @if($service->price <= 30000)
                                    Get the Guide
                                @elseif($service->price <= 80000)
                                    Start Application →
                                @else
                                    Book Consultation →
                                @endif
                            </a>
                        @else
                            <a href="{{ route('study-in-china.consultation') }}"
                               class="block w-full text-center {{ $btnStyle }} font-bold py-3.5 px-8 rounded-xl transition-all duration-200 text-lg">
                                Get Started →
                            </a>
                        @endif

                        @if($highlight)
                            <p class="text-red-600 text-xs mt-2 font-medium">
                                ⚡ Most students choose this package
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16">
                <h3 class="text-2xl font-bold text-gray-400">No services available at this moment.</h3>
                <p class="text-gray-500 mt-2">Please check back later or contact us directly.</p>
            </div>
        @endforelse
    </section>

    {{-- Urgency / Social Proof CTA --}}
    <section class="bg-red-50 border-t-4 border-red-600 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6">
                China Admission-এ <span class="text-red-600">সীমিত সময়</span> বাকি
            </h2>
            <p class="text-lg text-gray-700 mb-8 max-w-2xl mx-auto leading-relaxed">
                প্রতি year intake-এ seat limited থাকে এবং scholarship application window দ্রুত বন্ধ হয়ে যায়।
                দেরি করবেন না — আজই শুরু করুন আপনার প্রস্তুতি।
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('study-in-china.consultation') }}"
                   class="bg-red-600 text-white font-bold px-8 py-4 rounded-xl hover:bg-red-700 transition-all duration-200 shadow-lg shadow-red-200 text-lg">
                    Free Consultation Book করুন
                </a>
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer"
                   class="bg-green-600 text-white font-bold px-8 py-4 rounded-xl hover:bg-green-700 transition-all duration-200 shadow-lg shadow-green-200 text-lg flex items-center gap-2">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp-এ মেসেজ
                </a>
            </div>
        </div>
    </section>
</div>
</x-app-layout>
