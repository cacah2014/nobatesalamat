@extends('admin.layout')
@section('title', 'بروزرسانی پروژه')

@section('content')
<div class="admin-card">
    <div class="flex items-start gap-4">
        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#e9f8f4] text-lg text-brand">♻</span>
        <div>
            <h2 class="text-sm font-black">به‌روزرسانی امن از مخزن Git</h2>
            <p class="mt-1 text-[10px] leading-6 text-[#718784]">پیش از هر مرحله، دستور Git که در متغیرهای محیطی (EnvKit) تعیین شده اجرا می‌شود و سپس چهار مرحلهٔ Dependency → Database → Code → Update طی می‌گردد.</p>
            <code dir="ltr" class="mt-2 inline-block rounded bg-[#edf2f1] px-2 py-1 text-[10px]">{{ $gitCommand }}</code>
            @if(count($pendingCommits))
                <p class="mt-2 text-[10px] font-bold text-amber-700">{{ fa_digits(count($pendingCommits)) }} کامیت جدید روی مخزن آمادهٔ دریافت است.</p>
            @else
                <p class="mt-2 text-[10px] text-emerald-700">سرور به‌روز است؛ کامیت جدیدی در انتظار نیست.</p>
            @endif
        </div>
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
        <p class="mt-5 rounded-xl bg-amber-50 p-3 text-[10px] leading-5 text-amber-800">⚠ برای فعال‌سازی، متغیر <code dir="ltr">ALLOW_WEB_UPDATES=true</code> را در .env تنظیم کنید.</p>
    @endunless

    <button id="update-btn" class="btn-primary mt-5" {{ ! $enabled ? 'disabled style=opacity:.5' : '' }}>شروع به‌روزرسانی پروژه</button>
    <p id="update-message" class="mt-3 hidden rounded-xl px-3 py-2 text-[10px] font-bold"></p>
</div>

<pre id="update-output" dir="ltr" class="mt-5 hidden max-h-96 overflow-auto whitespace-pre-wrap rounded-[18px] bg-[#102c2b] p-4 text-left text-[10px] leading-5 text-[#b8e0da]"></pre>

@if($logs->count())
    <div class="admin-card mt-5">
        <h2 class="mb-3 text-sm font-black">تاریخچه بروزرسانی</h2>
        @foreach($logs as $log)
            <div class="flex items-center justify-between rounded-xl bg-[#f7faf9] p-3 text-[10px]">
                <span>{{ fa_date($log->started_at) }} · {{ \App\Models\UpdateLog::STEP_LABELS[$log->current_step] ?? $log->current_step }}</span>
                <span class="rounded-full px-2 py-1 font-bold {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-600' : ($log->status === 'failed' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600') }}">{{ ['success' => 'موفق', 'failed' => 'ناموفق', 'running' => 'در حال اجرا'][$log->status] }}</span>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('scripts')
<script>
var updateBtn = document.getElementById('update-btn');
if (updateBtn) updateBtn.addEventListener('click', async function () {
    if (!confirm('ابتدا git pull و سپس چهار مرحله به‌روزرسانی اجرا شود؟')) return;
    this.disabled = true;
    this.textContent = 'در حال اجرا...';
    document.querySelectorAll('[id^="step-"]').forEach((el) => el.classList.add('animate-pulse', 'bg-[#f0faf7]'));
    const message = document.getElementById('update-message');
    message.className = 'hidden';
    try {
        const response = await fetch('{{ route('admin.update.run') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        });
        const data = await response.json();
        message.className = 'mt-3 rounded-xl px-3 py-2 text-[10px] font-bold ' + (response.ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600');
        message.textContent = response.ok ? 'به‌روزرسانی با موفقیت کامل شد.' : (data.error || 'ناموفق بود.');
        if (data.output && data.output.length) {
            const pre = document.getElementById('update-output');
            pre.textContent = data.output.join('\n\n');
            pre.classList.remove('hidden');
        }
    } finally {
        this.textContent = 'شروع به‌روزرسانی پروژه';
        document.querySelectorAll('[id^="step-"]').forEach((el) => el.classList.remove('animate-pulse'));
        location.reload();
    }
});
</script>
@endpush
