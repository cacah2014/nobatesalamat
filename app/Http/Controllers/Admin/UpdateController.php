<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpdateLog;
use App\Services\SiteUpdater;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpdateController extends Controller
{
    public function __construct(private readonly SiteUpdater $updater)
    {
    }

    public function index(): View
    {
        return view('admin.updates', [
            'enabled' => $this->updater->enabled(),
            'logs' => UpdateLog::with('admin')->latest('started_at')->limit(5)->get(),
            'gitCommand' => $this->updater->gitCommand(),
            'pendingCommits' => $this->updater->pendingCommits(),
        ]);
    }

    /**
     * ترتیب: ابتدا دستور Git (از متغیرهای محیطی) سپس
     * ۱-Dependency ۲-Database ۳-Code ۴-Update
     */
    public function run(Request $request): JsonResponse
    {
        if (! $this->updater->enabled()) {
            return response()->json(['error' => 'به‌روزرسانی وب غیرفعال است. ALLOW_WEB_UPDATES=true را تنظیم کنید.'], 503);
        }

        if ($this->updater->runningLog()) {
            return response()->json(['error' => 'یک فرایند به‌روزرسانی در حال اجراست.'], 409);
        }

        $result = $this->updater->run($request->user()?->id, 'admin-panel');

        return response()->json([
            'ok' => $result['ok'],
            'error' => $result['error'],
            'output' => $result['output'],
        ], $result['ok'] ? 200 : 500);
    }
}
