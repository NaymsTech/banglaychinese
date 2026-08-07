<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'slug',
        'video_url',
        'content',
        'order',
        'is_free_preview',
    ];

    protected $casts = [
        'is_free_preview' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
