<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'topic' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $waText = "নতুন কন্টাক্ট মেসেজ\n"
            . "নাম: {$validated['name']}\n"
            . "ফোন: {$validated['phone']}\n"
            . "ইমেইল: " . ($validated['email'] ?? '—') . "\n"
            . "বিষয়: " . ucfirst($validated['topic']) . "\n"
            . "মেসেজ: {$validated['message']}";

        $whatsappUrl = 'https://wa.me/8618223249514?text=' . rawurlencode($waText);

        return redirect()->away($whatsappUrl);
    }

    public function scholarship()
    {
        $courses = Course::where('is_published', true)->get();

        return view('pages.scholarship', compact('courses'));
    }
}
