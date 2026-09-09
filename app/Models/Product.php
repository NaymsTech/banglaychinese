<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'price',
        'cover_image',
        'file_path',
        'external_download_url',
        'category',
        'is_published',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_published' => 'boolean',
    ];

    public function digitalOrders(): HasMany
    {
        return $this->hasMany(DigitalOrder::class);
    }
}
