<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use Illuminate\Http\Request;

class AboutPageController extends Controller
{
    /**
     * Display the single About Page CMS form with all sections grouped.
     */
    public function index()
    {
        $sections = AboutSection::all()->groupBy('group');
        return view('admin.about.edit', compact('sections'));
    }

    /**
     * Update all About Page content at once.
     */
    public function update(Request $request)
    {
        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            $section = AboutSection::where('key', $key)->first();

            if (! $section) {
                continue;
            }

            if ($section->type === 'json') {
                // Ensure JSON keys are stored as valid JSON
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $section->value = $value;
                    }
                }
            } elseif ($section->type === 'image') {
                if ($request->hasFile($key)) {
                    $path = $request->file($key)->store('about', 'public');
                    $section->value = $path;
                }
                // If no new file, keep existing value
            } else {
                $section->value = $value;
            }

            $section->save();
        }

        return redirect()->route('admin.about.index')
            ->with('success', 'About page content updated successfully.');
    }
}
