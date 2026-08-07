<?php

namespace App\Http\Controllers;

use App\Models\AboutSection;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * Display the About page with all sections from the CMS.
     */
    public function index()
    {
        $data = [];
        $sections = AboutSection::all();

        foreach ($sections as $section) {
            $data[$section->key] = $section->parsed_value;
        }

        return view('about.show', $data);
    }
}
