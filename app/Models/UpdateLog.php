<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['admin_id', 'status', 'current_step', 'output', 'started_at', 'finished_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public const STEP_LABELS = [
        'git_pull' => 'دریافت آخرین نسخه (git pull)',
        'dependency' => '۱. Dependency (composer install)',
        'database' => '۲. Database (migrate)',
        'code' => '۳. Code (cache & build)',
        'update' => '۴. Update (فعال‌سازی)',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
