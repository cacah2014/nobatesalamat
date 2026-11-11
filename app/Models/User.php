<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['phone', 'full_name', 'role', 'status', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPER = 'super_admin';

    public const PERMISSIONS = ['content', 'doctors', 'appointments', 'updates'];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * فعال بودن حساب.
     *
     * مقدارهای خالی/NULL (ردیف‌های قدیمی یا جدولی که ستون status را ندارد)
     * «فعال» در نظر گرفته می‌شوند تا ورود کاربران قفل نشود؛ فقط وضعیت‌های
     * blocked / inactive / disabled حساب را می‌بندند.
     */
    public function isActive(): bool
    {
        $status = strtolower(trim((string) $this->status));

        return ! in_array($status, ['blocked', 'inactive', 'disabled', 'suspended'], true);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER;
    }

    /** مدیر ارشد همیشه دسترسی کامل دارد */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->isAdmin() && in_array($permission, $this->permissions ?? [], true);
    }
}
