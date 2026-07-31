<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $fillable = [
        'module_id',
        'title',
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
}
