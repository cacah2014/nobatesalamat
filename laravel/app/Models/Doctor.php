<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'full_name', 'slug', 'specialty_id', 'title', 'city', 'address', 'rating',
        'reviews_count', 'visit_price', 'experience_years', 'next_available',
        'image_url', 'is_online', 'is_active', 'about', 'medical_code',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'float',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
