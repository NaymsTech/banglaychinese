<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'features',
        'price',
        'cta_label',
        'duration',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'status'   => 'boolean',
        'price'    => 'decimal:2',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getIsActiveAttribute(): bool
    {
        return (bool) $this->status;
    }

    public function getCtaLabelAttribute(): string
    {
        return $this->attributes['cta_label'] ?? 'Book Consultation';
    }
}
