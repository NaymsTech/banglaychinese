<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudyInChinaSection;
use Illuminate\Http\Request;

class StudyInChinaPageController extends Controller
{
    /**
     * Show the CMS editor for the Study in China page.
     */
    public function edit()
    {
        $sections = StudyInChinaSection::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');

        $groupLabels = [
            'hero' => 'Hero Section',
            'why_china' => 'Why Study in China',
            'why_us' => 'Why Choose BanglayChinese',
            'roadmap' => 'Study Abroad Roadmap',
            'services' => 'Service Packages',
            'comparison' => 'Comparison Table',
            'scholarships' => 'Scholarship Opportunities',
            'quote' => 'Success Philosophy',
            'faqs' => 'Frequently Asked Questions',
            'booking' => 'Consultation Booking',
            'final_cta' => 'Final Call to Action',
        ];

        return view('admin.study-in-china.edit', compact('sections', 'groupLabels'));
    }

    /**
     * Update a section field.
     */
    public function update(Request $request, StudyInChinaSection $section)
    {
        $validated = $request->validate([
            'value' => 'nullable',
        ]);

        $section->update([
            'value' => $validated['value'] ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully.',
        ]);
    }

    /**
     * Update a specific field by key.
     */
    public function updateByKey(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|exists:study_in_china_sections,key',
            'value' => 'nullable',
        ]);

        $section = StudyInChinaSection::where('key', $validated['key'])->first();
        $section->update(['value' => $validated['value'] ?? '']);

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully.',
        ]);
    }

    /**
     * Bulk update for JSON sections (cards, features, etc).
     */
    public function updateJson(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|exists:study_in_china_sections,key',
            'data' => 'required',
        ]);

        $section = StudyInChinaSection::where('key', $validated['key'])->first();

        $data = is_string($validated['data'])
            ? $validated['data']
            : json_encode($validated['data'], JSON_UNESCAPED_UNICODE);

        $section->update(['value' => $data]);

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully.',
        ]);
    }

    /**
     * Upload an image for sections that use image type.
     */
    public function uploadImage(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|exists:study_in_china_sections,key',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $section = StudyInChinaSection::where('key', $validated['key'])->first();

        // Delete old image if exists
        if ($section->value && file_exists(public_path($section->value))) {
            unlink(public_path($section->value));
        }

        $path = $request->file('image')->store('study-in-china', 'public');
        $section->update(['value' => 'storage/' . $path]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'path' => 'storage/' . $path,
        ]);
    }
}
