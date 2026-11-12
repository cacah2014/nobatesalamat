@extends('admin.layout')
@section('title', 'بروزرسانی پروژه')

@section('content')
<div class="admin-card">
    <div class="flex items-start gap-4">
        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#e9f8f4] text-lg text-brand">♻</span>
        <div class="min-w-0">
            <h2 class="text-sm font-black">به‌روزرسانی امن از مخزن Git</h2>
            <p class="mt-1 text-[10px] leading-6 text-[#718784]">دستور Git (قابل تنظیم با متغیرهای EnvKit) اجرا می‌شود و سپس مراحل Dependency → Database → Code → Update طی می‌گردد. اگر مرحلهٔ Git موفق باشد ولی یکی از مراحل بعدی خطا بدهد، به‌جای توقف، هشدار نمایش داده می‌شود تا کش‌ها در هر حال بازسازی شوند.</p>
            <code dir="ltr" class="mt-2 inline-block max-w-full overflow-x-auto rounded bg-[#edf2f1] px-2 py-1 text-[10px]">{{ $gitCommand }}</code>

            @if(! $pending['ok'])
                <p class="mt-2 rounded-lg bg-red-50 px-2 py-1 text-[10px] font-bold leading-5 text-red-600">وضعیت مخزن خوانده نشد: {{ \Illuminate\Support\Str::limit((string) $pending['error'], 200) }}</p>
            @elseif((int) $pending['behind'] > 0)
                <p class="mt-2 rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-bold leading-5 text-amber-700">{{ fa_digits((string) $pending['behind']) }} کامیت جدید آمادهٔ دریافت است · سرور: <span dir="ltr">{{ $pending['local'] }}</span> ← مخزن: <span dir="ltr">{{ $pending['remote'] }}</span></p>
            @else
                <p class="mt-2 rounded-lg bg-emerald-50 px-2 py-1 text-[10px] font-bold leading-5 text-emerald-700">سرور به‌روز است (کامیت <span dir="ltr">{{ $pending['local'] }}</span> = مخزن <span dir="ltr">{{ $pending['remote'] }}</span>)</p>
            @endif
        </div>
    </div>

    <div class="mt-5 grid gap-2 sm:grid-cols-2">
        @foreach($preflight as $check)
            <div class="rounded-xl border p-3 text-[10px] leading-5 {{ $check['ok'] ? 'border-[#d8ece7] bg-[#f4fbf9]' : 'border-amber-200 bg-amber-50' }}">
                <b class="block text-[11px]">{{ $check['ok'] ? '✅' : '⚠' }} {{ $check['label'] }}</b>
                <span class="mt-1 block break-all text-[#718784]">{{ $check['detail'] }}</span>
            </div>
        @endforeach
    </div>

<div class="mt-6 grid gap-3 sm:grid-cols-4">
        @foreach([['۱. Dependency', 'نصب/هماهنگ‌سازی پکیج‌ها (composer install)'], ['۲. Database', 'اعمال تغییرات ساختار دیتابیس (migrate)'], ['۳. Code', 'کش و بهینه‌سازی نسخهٔ جدید (config/route/view cache)'], ['۴. Update', 'فعال‌سازی نسخهٔ جدید']] as [$title, $text])
            <div id="step-{{ $loop->index }}" class="rounded-2xl border border-[#e5eeec] p-3">
                <b class="block text-[11px]">{{ $title }}</b>
                <small class="mt-1 block text-[9px] leading-4 text-[#849895]">{{ $text }}</small>
            </div>
        @endforeach
    </div>

    @unless($enabled)
        <p class="mt-5 rounded-xl bg-amber-50 p-3 text-[10px] leading-5 text-amber-800">⚠ به‌روزرسانی از پنل غیرفعال شده است (متغیر <code dir="ltr">ALLOW_PANEL_UPDATES=false</code>). برای فعال‌کردن آن را حذف کنید یا <code dir="ltr">true</code> بگذارید.</p>
    @endunless

    @if(! empty($localChanges))
        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-[10px] leading-5 text-amber-900">
            <b class="block text-[11px]">⚠ این فایل‌ها روی سرور تغییر کرده‌اند و `git pull` را متوقف می‌کنند:</b>
            <ul dir="ltr" class="mt-2 space-y-1 font-mono">
                @foreach($localChanges as $change)
                    <li>{{ $change }}</li>
                @endforeach
            </ul>
            <p class="mt-2">با «به‌روزرسانی معمولی» این فایل‌ها خودکار در <code dir="ltr">git stash</code> ذخیره و سپس pull می‌شوند؛ اگر باز هم گیر کرد، «به‌روزرسانی اجباری» را بزنید.</p>
        </div>
    @endif

    <div class="mt-5 flex flex-wrap gap-2">
        <button id="update-btn" data-update-mode="safe" class="btn-primary" {{ ! $enabled ? 'disabled style=opacity:.5' : '' }}>شروع به‌روزرسانی پروژه</button>
        <button id="update-force-btn" data-update-mode="force" class="rounded-xl border border-[#f0c9bf] bg-[#fff4f1] px-4 py-2.5 text-xs font-black text-[#b4442c]" {{ ! $enabled ? 'disabled style=opacity:.5' : '' }}>به‌روزرسانی اجباری (نادیده‌گرفتن تغییرات محلی)</button>
    </div>
    <p id="update-message" class="mt-3 hidden rounded-xl px-3 py-2 text-[10px] font-bold"></p>
</div>

<pre id="update-output" dir="ltr" class="mt-5 hidden max-h-96 overflow-auto whitespace-pre-wrap rounded-[18px] bg-[#102c2b] p-4 text-left text-[10px] leading-5 text-[#b8e0da]"></pre>

@if($logs->count())
    <div class="admin-card mt-5">
        <h2 class="mb-3 text-sm font-black">تاریخچه بروزرسانی</h2>
        @foreach($logs as $log)
            <div class="flex items-center justify-between rounded-xl bg-[#f7faf9] p-3 text-[10px]">
                <span>{{ fa_date($log->started_at) }} · {{ \App\Models\UpdateLog::STEP_LABELS[$log->current_step] ?? $log->current_step }}</span>
                <span class="rounded-full px-2 py-1 font-bold {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-600' : ($log->status === 'failed' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600') }}">{{ ['success' => 'موفق', 'failed' => 'ناموفق', 'running' => 'در حال اجرا'][$log->status] ?? $log->status }}</span>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('scripts')
<script>
var updateButtons = document.querySelectorAll('[data-update-mode]');

for (var i = 0; i < updateButtons.length; i++) {
    updateButtons[i].addEventListener('click', function () {
        var force = this.getAttribute('data-update-mode') === 'force';
        var question = force
            ? 'تغییرات محلی سرور در stash ذخیره شود و سرور دقیقاً با مخزن هم‌تراز گردد؟'
            : 'ابتدا git pull و سپس مراحل Dependency، Database، Code و Update اجرا شود؟';

        if (!confirm(question)) {
            return;
        }

        runUpdate(this, force);
    });
}

async function runUpdate(button, force) {
    var allButtons = document.querySelectorAll('[data-update-mode]');

    for (var j = 0; j < allButtons.length; j++) {
        allButtons[j].disabled = true;
    }

    button.textContent = force ? 'در حال هم‌ترازی با مخزن...' : 'در حال اجرا...';

    var steps = document.querySelectorAll('[id^="step-"]');
    for (var s = 0; s < steps.length; s++) {
        steps[s].classList.add('animate-pulse', 'bg-[#f0faf7]');
    }

    var message = document.getElementById('update-message');
    message.className = 'hidden';

    try {
        var response = await fetch('{{ route('admin.update.run') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ force: force })
        });

        var data = await response.json();
        var text = response.ok ? 'به‌روزرسانی با موفقیت کامل شد.' : (data.error || 'ناموفق بود.');

        if (data.warnings && data.warnings.length) {
            text += ' · هشدار: ' + data.warnings.join(' | ');
        }

        message.className = 'mt-3 rounded-xl px-3 py-2 text-[10px] font-bold ' + (response.ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600');
        message.textContent = text;

        if (data.output && data.output.length) {
            var pre = document.getElementById('update-output');
            pre.textContent = data.output.join('\n\n');
            pre.classList.remove('hidden');
        }
    } catch (error) {
        message.className = 'mt-3 rounded-xl bg-red-50 px-3 py-2 text-[10px] font-bold text-red-600';
        message.textContent = 'ارتباط با سرور برقرار نشد. دوباره تلاش کنید.';
    } finally {
        button.textContent = force ? 'به‌روزرسانی اجباری (نادیده‌گرفتن تغییرات محلی)' : 'شروع به‌روزرسانی پروژه';

        for (var k = 0; k < allButtons.length; k++) {
            allButtons[k].disabled = false;
        }

        for (var m = 0; m < steps.length; m++) {
            steps[m].classList.remove('animate-pulse');
        }

        location.reload();
    }
}
</script>
@endpush
