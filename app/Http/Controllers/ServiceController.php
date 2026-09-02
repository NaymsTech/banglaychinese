<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display the Study in China service packages.
     */
    public function index()
    {
        $services = Service::active()->ordered()->get();

        $metaTitle = 'Study in China Services — Admission & Scholarship Packages | Banglay Chinese';
        $metaDescription = 'চীনে পড়তে যাওয়ার সম্পূর্ণ গাইডেন্স — Application Guide থেকে শুরু করে ১-বছরের Complete Pathway। আপনার স্কলারশিপ ও ভর্তি প্রক্রিয়া সহজ করুন।';
        $canonicalUrl = route('services.index');

        return view('study-in-china.services', compact('services', 'metaTitle', 'metaDescription', 'canonicalUrl'));
    }

    /**
     * Display a single service package.
     */
    public function show(string $slug)
    {
        $service = Service::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $metaTitle = $service->name.' | Banglay Chinese';
        $metaDescription = $service->short_description ?? $service->description;
        $canonicalUrl = route('services.show', $service->slug);

        return view('study-in-china.service-show', compact('service', 'metaTitle', 'metaDescription', 'canonicalUrl'));
    }
}
