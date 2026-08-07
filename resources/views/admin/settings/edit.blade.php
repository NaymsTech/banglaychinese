@extends('layouts.admin')

@section('page-title', 'Settings')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            {{-- Site Info --}}
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Site Information</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Site Name</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? 'BanglayChinese') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tagline</label>
                        <input type="text" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Logo</label>
                        @if(!empty($settings['site_logo']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['site_logo']) }}" class="h-10 rounded" alt="Logo">
                            </div>
                        @endif
                        <input type="file" name="site_logo" accept="image/*"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Favicon</label>
                        @if(!empty($settings['site_favicon']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['site_favicon']) }}" class="h-8 w-8 rounded" alt="Favicon">
                            </div>
                        @endif
                        <input type="file" name="site_favicon" accept="image/*"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Contact Information</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Contact Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Physical Address</label>
                        <textarea name="physical_address" rows="2"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">{{ old('physical_address', $settings['physical_address'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">bKash Number</label>
                        <input type="text" name="bkash_number" value="{{ old('bkash_number', $settings['bkash_number'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nagad Number</label>
                        <input type="text" name="nagad_number" value="{{ old('nagad_number', $settings['nagad_number'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Homepage --}}
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Homepage</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Hero Title</label>
                        <input type="text" name="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Hero Subtitle</label>
                        <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $settings['hero_subtitle'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Stats - Students</label>
                        <input type="text" name="stats_students" value="{{ old('stats_students', $settings['stats_students'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Stats - Courses</label>
                        <input type="text" name="stats_courses" value="{{ old('stats_courses', $settings['stats_courses'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Stats - Years</label>
                        <input type="text" name="stats_years" value="{{ old('stats_years', $settings['stats_years'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">SEO Settings</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="2"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Google Analytics ID</label>
                        <input type="text" name="google_analytics_id" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Facebook Pixel ID</label>
                        <input type="text" name="facebook_pixel_id" value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Social Links --}}
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Social Links</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Facebook URL</label>
                        <input type="text" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">YouTube URL</label>
                        <input type="text" name="youtube_url" value="{{ old('youtube_url', $settings['youtube_url'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">LinkedIn URL</label>
                        <input type="text" name="linkedin_url" value="{{ old('linkedin_url', $settings['linkedin_url'] ?? '') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit"
                    class="rounded-lg bg-[#0F5132] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0d452c] transition">
                Save Settings
            </button>
        </div>
    </form>
@endsection
