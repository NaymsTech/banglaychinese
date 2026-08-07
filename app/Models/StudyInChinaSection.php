<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyInChinaSection extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the value auto-decoded based on type.
     */
    public function getParsedValueAttribute()
    {
        if ($this->type === 'json') {
            return json_decode($this->value, true) ?? $this->value;
        }

        return $this->value;
    }
}
