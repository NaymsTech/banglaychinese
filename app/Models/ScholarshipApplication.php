<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipApplication extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'highest_qualification',
        'gpa_cgpa',
        'desired_program',
        'target_intake',
        'hsk_english_level',
        'target_course',
        'educational_background',
        'statement_of_purpose',
        'message',
        'status',
        'admin_notes',
        'follow_up_date',
        'budget',
        'preferred_consultation_time',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];
}
