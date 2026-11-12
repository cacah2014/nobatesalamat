<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['question', 'answer', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** سؤالات فعال به ترتیب نمایش */
    public static function activeList(): Collection
    {
        try {
            return static::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        } catch (\Throwable) {
            return new Collection();
        }
    }
}
