<?php

namespace App\Http\Controllers;

use App\Models\FreeResource;

class FreeResourceController extends Controller
{
    public function index()
    {
        $resources = FreeResource::where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('category');

        $metaTitle = 'Free Study Resources | Banglay Chinese';
        $metaDescription = 'Free HSK vocabulary lists, grammar guides, study tips, PDFs and video lessons for Bangladeshi students learning Chinese.';

        return view('free-resources.index', compact('resources', 'metaTitle', 'metaDescription'));
    }
}
