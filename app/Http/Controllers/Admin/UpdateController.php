<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpdateLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

class UpdateController extends Controller
{
    public function index(): View
    {
        return view('admin.updates', [
            'enabled' => config('services.nobat.allow_web_updates'),
            'logs' => UpdateLog::with('admin')->latest('started_at')->limit(5)->get(),
        ]);
    }

    /**
     * ترتیب درخواستی: ابتدا git pull --ff-only سپس
     * ۱-Dependency  ۲-Database  ۳-Code  ۴-Update
     */
    public function run(Request $request): JsonResponse
    {
        if (! config('services.nobat.allow_web_updates')) {
            return response()->json(['error' => 'به‌روزرسانی وب غیرفعال است. ALLOW_WEB_UPDATES=true را تنظیم کنید.'], 503);
        }

        if (UpdateLog::where('status', 'running')->where('started_at', '>', now()->subMinutes(10))->exists()) {
            return response()->json(['error' => 'یک فرایند به‌روزرسانی در حال اجراست.'], 409);
        }

        $log = UpdateLog::create([
            'admin_id' => $request->user()->id,
            'status' => 'running',
            'current_step' => 'git_pull',
            'output' => '',
            'started_at' => now(),
        ]);

        $output = [];
        $save = function (string $step, string $text) use ($log, &$output) {
            $output[] = "[{$step}]\n" . ($text === '' ? 'انجام شد' : $text);
            $log->update(['current_step' => $step, 'output' => implode("\n\n", $output)]);
        };

        $steps = [
            // ابتدا git pull
            ['git_pull', ['git', 'pull', '--ff-only']],
            // ۱) Dependency
            ['dependency', ['composer', 'install', '--no-dev', '--prefer-dist', '--optimize-autoloader', '--no-interaction']],
            // ۲) Database
            ['database', [PHP_BINARY, 'artisan', 'migrate', '--force']],
            // ۳) Code (کش و بهینه‌سازی نسخهٔ جدید)
            ['code', [PHP_BINARY, 'artisan', 'config:cache']],
            ['code', [PHP_BINARY, 'artisan', 'route:cache']],
            ['code', [PHP_BINARY, 'artisan', 'view:cache']],
        ];

        try {
            foreach ($steps as [$step, $command]) {
                $save($step, $this->runProcess($command));
            }

            // ۴) Update — فرمان نهایی اختیاری
            if ($final = config('services.nobat.update_final_command')) {
                $save('update', $this->runShell($final));
            } else {
                $save('update', 'نسخهٔ جدید فعال شد.');
            }

            $log->update(['status' => 'success', 'current_step' => 'completed', 'finished_at' => now()]);

            return response()->json(['ok' => true, 'output' => $output]);
        } catch (\Throwable $e) {
            $output[] = "[ERROR]\n" . $e->getMessage();
            $log->update(['status' => 'failed', 'output' => implode("\n\n", $output), 'finished_at' => now()]);

            return response()->json(['error' => 'فرایند به‌روزرسانی ناموفق بود.', 'output' => $output], 500);
        }
    }

    private function runProcess(array $command): string
    {
        $process = new Process($command, base_path(), timeout: 600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() . $process->getOutput());
        }

        return trim($process->getOutput() . $process->getErrorOutput());
    }

    private function runShell(string $command): string
    {
        return trim((string) shell_exec("cd " . escapeshellarg(base_path()) . " && " . $command . " 2>&1"));
    }
}
