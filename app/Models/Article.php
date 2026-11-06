<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['title', 'slug', 'excerpt', 'image_url', 'read_time', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];
}
