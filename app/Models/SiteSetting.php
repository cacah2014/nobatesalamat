<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'label', 'group', 'type'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings'));
        static::deleted(fn () => Cache::forget('site_settings'));
    }

    /** خواندن همهٔ تنظیمات به‌صورت کش‌شده */
    public static function all_cached(): array
    {
        return Cache::rememberForever('site_settings', fn () =>
            static::query()->pluck('value', 'key')->all()
        );
    }

    public static function get(string $key, string $default = ''): string
    {
        return static::all_cached()[$key] ?? $default;
    }
}
