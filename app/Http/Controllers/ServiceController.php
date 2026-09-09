<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
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
