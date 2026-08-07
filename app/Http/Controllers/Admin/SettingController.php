<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => Setting::pluck('value', 'key')->toArray(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            // Site Info
            'site_name'          => ['nullable', 'string', 'max:255'],
            'site_tagline'       => ['nullable', 'string', 'max:255'],
            'site_logo'          => ['nullable', 'image', 'max:1024'],
            'site_favicon'       => ['nullable', 'image', 'max:256'],

            // Contact Info
            'whatsapp_number'    => ['nullable', 'string', 'max:30'],
            'contact_email'      => ['nullable', 'email', 'max:255'],
            'physical_address'   => ['nullable', 'string', 'max:500'],
            'bkash_number'       => ['nullable', 'string', 'max:30'],
            'nagad_number'       => ['nullable', 'string', 'max:30'],

            // Homepage
            'hero_title'         => ['nullable', 'string', 'max:255'],
            'hero_subtitle'      => ['nullable', 'string', 'max:500'],
            'stats_students'     => ['nullable', 'string', 'max:10'],
            'stats_courses'      => ['nullable', 'string', 'max:10'],
            'stats_years'        => ['nullable', 'string', 'max:10'],

            // SEO
            'meta_description'       => ['nullable', 'string', 'max:300'],
            'google_analytics_id'    => ['nullable', 'string', 'max:100'],
            'facebook_pixel_id'      => ['nullable', 'string', 'max:100'],

            // Social Links
            'facebook_url'           => ['nullable', 'string', 'max:255'],
            'youtube_url'            => ['nullable', 'string', 'max:255'],
            'linkedin_url'           => ['nullable', 'string', 'max:255'],
        ];

        $data = $request->validate($rules);

        // Handle file uploads
        foreach (['site_logo', 'site_favicon'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                // Delete old file
                $old = Setting::where('key', $fileKey)->value('value');
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
                $path = $request->file($fileKey)->store('settings', 'public');
                $data[$fileKey] = $path;
            } else {
                unset($data[$fileKey]);
            }
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        // Clear settings cache
        SettingsService::flush();

        return back()->with('success', 'Settings saved successfully.');
    }
}
