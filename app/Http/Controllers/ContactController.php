<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function send(StoreContactMessageRequest $request)
    {
        $validated = $request->validated();

        ContactMessage::create($validated);

        $waText = "নতুন কন্টাক্ট মেসেজ\n"
            . "নাম: {$validated['name']}\n"
            . "ফোন: {$validated['phone']}\n"
            . "ইমেইল: " . ($validated['email'] ?? '—') . "\n"
            . "বিষয়: " . ucfirst($validated['topic']) . "\n"
            . "মেসেজ: {$validated['message']}";

        $whatsappUrl = 'https://wa.me/8618223249514?text=' . rawurlencode($waText);

        return redirect()->away($whatsappUrl);
    }
}
