<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'hsk_level',
        'price',
        'thumbnail',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
