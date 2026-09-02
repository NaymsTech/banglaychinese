<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Post;
use App\Models\ScholarshipApplication;
use App\Models\Service;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalStudents' => User::count(),
            'activeCourses' => Course::where('is_published', true)->count(),
            'activeServices' => Service::active()->count(),
            'pendingScholarships' => ScholarshipApplication::whereNull('application_status')->count(),
            'publishedPosts' => Post::where('is_published', true)->count(),
        ]);
    }
}
