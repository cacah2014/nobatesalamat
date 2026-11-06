<?php

namespace App\Http\Controllers;

use App\Models\UpdateLog;
use App\Services\SiteUpdater;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * وب‌هوک به‌روزرسانی سایت برای سرویس‌هایی مثل EnvKit.
 *
 * EnvKit فقط کافی است این آدرس را صدا بزند:
 *   POST https://nobatesalamat.ir/deploy/{DEPLOY_WEBHOOK_TOKEN}
 * دستور git از متغیرهای محیطی خوانده می‌شود
 * (UPDATE_GIT_COMMAND یا UPDATE_GIT_REMOTE / UPDATE_GIT_BRANCH).
 */
class DeployController extends Controller
{
    public function __construct(private readonly SiteUpdater $updater)
    {
    }

    /** وضعیت: نسخهٔ فعلی، کامیت‌های جدید و آخرین اجرا */
    public function status(Request $request, ?string $token = null): JsonResponse
    {
        if ($denied = $this->guard($request, $token)) {
            return $denied;
        }

        $lastLog = UpdateLog::latest('started_at')->first();

        return response()->json([
            'ok' => true,
            'enabled' => $this->updater->enabled(),
            'branch' => $this->updater->gitBranch(),
            'remote' => $this->updater->gitRemote(),
            'git_command' => $this->updater->gitCommand(),
            'current_commit' => $this->updater->currentCommit(),
            'pending_commits' => $this->updater->pendingCommits(),
            'running' => $this->updater->runningLog() !== null,
            'last_update' => $lastLog ? [
                'status' => $lastLog->status,
                'step' => $lastLog->current_step,
                'started_at' => optional($lastLog->started_at)->toIso8601String(),
                'finished_at' => optional($lastLog->finished_at)->toIso8601String(),
            ] : null,
        ]);
    }

    /** اجرای به‌روزرسانی. با ?dry_run=1 فقط بررسی می‌کند. */
    public function deploy(Request $request, ?string $token = null): JsonResponse
    {
        if ($denied = $this->guard($request, $token)) {
            return $denied;
        }

        if (! $this->updater->enabled()) {
            return response()->json([
                'ok' => false,
                'error' => 'به‌روزرسانی غیرفعال است. ALLOW_WEB_UPDATES=true را تنظیم کنید.',
            ], 503);
        }

        $pending = $this->updater->pendingCommits();

        if ($request->boolean('dry_run')) {
            return response()->json([
                'ok' => true,
                'dry_run' => true,
                'git_command' => $this->updater->gitCommand(),
                'current_commit' => $this->updater->currentCommit(),
                'pending_commits' => $pending,
            ]);
        }

        if ($this->updater->runningLog()) {
            return response()->json([
                'ok' => false,
                'error' => 'یک فرایند به‌روزرسانی در حال اجراست.',
            ], 409);
        }

        if ($pending === []) {
            return response()->json([
                'ok' => true,
                'updated' => false,
                'message' => 'نسخهٔ سرور همین حالا آخرین نسخهٔ مخزن است.',
                'current_commit' => $this->updater->currentCommit(),
            ]);
        }

        $result = $this->updater->run(null, 'envkit-webhook');

        return response()->json([
            'ok' => $result['ok'],
            'updated' => $result['ok'],
            'error' => $result['error'],
            'git_command' => $this->updater->gitCommand(),
            'pending_commits' => $pending,
            'output' => $result['output'],
            'log_id' => $result['log']->id,
        ], $result['ok'] ? 200 : 500);
    }

    /** بررسی توکن و محدودیت IP */
    private function guard(Request $request, ?string $routeToken): ?JsonResponse
    {
        if (! $this->updater->ipAllowed($request->ip())) {
            return response()->json(['ok' => false, 'error' => 'IP مجاز نیست.'], 403);
        }

        $token = $routeToken
            ?: (string) ($request->header('X-Deploy-Token') ?: $request->query('token', ''));

        if (! $this->updater->tokenMatches($token)) {
            return response()->json(['ok' => false, 'error' => 'توکن نامعتبر است.'], 403);
        }

        return null;
    }
}
