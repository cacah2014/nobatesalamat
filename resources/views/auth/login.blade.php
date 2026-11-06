@extends('layouts.app')

@section('title', 'ورود یا ثبت‌نام با شماره موبایل | نوبت سلامت')
@section('robots', 'noindex')

@section('content')
<section class="mx-auto grid min-h-[calc(100vh-76px)] w-full max-w-[1180px] items-center gap-10 px-4 py-10 lg:grid-cols-2">
    <div class="mx-auto w-full max-w-[420px]">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#e9f8f4] px-3 py-1.5 text-[10px] font-black text-brand">🔒 ورود امن و بدون رمز عبور</span>
        <h1 class="mt-4 text-3xl font-black">به نوبت سلامت خوش آمدید</h1>
        <p class="mt-3 text-sm leading-7 text-[#718784]">برای ورود یا ساخت حساب، شماره موبایل خود را وارد کنید.</p>

        {{-- مرحله ۱: شماره موبایل --}}
        <form id="phone-step" class="mt-8">
            <label class="mb-2 block text-[13px] font-bold">شماره موبایل</label>
            <input id="phone-input" inputmode="tel" dir="ltr" autocomplete="tel" maxlength="14" class="input-field text-left font-bold tracking-wider" placeholder="0912 123 4567">
            <p id="phone-error" class="mt-3 hidden rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600"></p>
            <button class="btn-primary mt-5 w-full" data-loading-text="در حال ارسال...">دریافت کد ورود</button>
        </form>

        {{-- مرحله ۲: کد تأیید --}}
        <form id="code-step" class="mt-8 hidden">
            <div class="mb-5 flex items-center justify-between gap-3 rounded-2xl bg-[#e9f8f4] p-3.5 text-xs leading-6 text-[#315b58]">
                <span>کد تأیید برای <b id="phone-label" dir="ltr"></b> ارسال شد.</span>
                <button type="button" id="edit-phone" class="shrink-0 text-brand">ویرایش</button>
            </div>
            <label class="mb-2 block text-[13px] font-bold">کد پنج‌رقمی</label>
            <input id="code-input" inputmode="numeric" dir="ltr" autocomplete="one-time-code" maxlength="5" class="input-field text-center text-xl font-black tracking-[.5em]" placeholder="•••••">
            <button type="button" id="dev-code-btn" class="mt-3 hidden w-full rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800"></button>
            <p id="code-error" class="mt-3 hidden rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600"></p>
            <button class="btn-primary mt-5 w-full" data-loading-text="در حال بررسی...">تأیید و ورود امن</button>
            <p class="mt-4 text-center text-xs text-[#718784]">
                <span id="resend-timer">ارسال مجدد تا <b id="resend-count" class="text-brand"></b> ثانیه دیگر</span>
                <button type="button" id="resend-btn" class="hidden font-black text-brand">ارسال دوباره کد</button>
            </p>
        </form>
    </div>

    <div class="banner-gradient hidden rounded-[30px] p-10 text-white lg:block">
        <h2 class="text-2xl font-black">همهٔ نوبت‌های پزشکی، یک‌جا</h2>
        <p class="mt-3 text-sm leading-7 text-[#d2eae6]">پزشک مناسب را پیدا کنید، اولین زمان آزاد را ببینید و بدون تماس تلفنی نوبت بگیرید.</p>
        <div class="mt-7 grid grid-cols-3 gap-3 text-center">
            <div class="rounded-2xl bg-white/10 p-3"><b class="block text-lg text-[#5eead4]">۲۴/۷</b><small class="text-[9px]">نوبت‌دهی</small></div>
            <div class="rounded-2xl bg-white/10 p-3"><b class="block text-lg text-[#5eead4]">+۲۴۰۰</b><small class="text-[9px]">پزشک معتبر</small></div>
            <div class="rounded-2xl bg-white/10 p-3"><b class="block text-lg text-[#5eead4]">۹۶٪</b><small class="text-[9px]">رضایت</small></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
const token = document.querySelector('meta[name="csrf-token"]').content;
let phone = '';
let countdown;

async function post(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    });
    return { status: response.status, data: await response.json() };
}

function showError(id, text) {
    const el = document.getElementById(id);
    el.textContent = text;
    el.classList.remove('hidden');
}

async function requestCode(button) {
    button.disabled = true;
    const { status, data } = await post('{{ route('auth.otp') }}', { phone });
    button.disabled = false;
    if (status !== 200) {
        showError('phone-error', data.error || 'ارسال کد انجام نشد.');
        return;
    }
    document.getElementById('phone-step').classList.add('hidden');
    document.getElementById('phone-error').classList.add('hidden');
    document.getElementById('code-step').classList.remove('hidden');
    document.getElementById('phone-label').textContent = phone;
    if (data.devCode) {
        const dev = document.getElementById('dev-code-btn');
        dev.textContent = 'کد ورود آزمایشی: ' + data.devCode + ' — برای درج کلیک کنید';
        dev.classList.remove('hidden');
        dev.onclick = () => (document.getElementById('code-input').value = data.devCode);
    }
    startTimer(60);
}

function startTimer(seconds) {
    clearInterval(countdown);
    document.getElementById('resend-timer').classList.remove('hidden');
    document.getElementById('resend-btn').classList.add('hidden');
    document.getElementById('resend-count').textContent = seconds;
    countdown = setInterval(() => {
        seconds -= 1;
        document.getElementById('resend-count').textContent = seconds;
        if (seconds <= 0) {
            clearInterval(countdown);
            document.getElementById('resend-timer').classList.add('hidden');
            document.getElementById('resend-btn').classList.remove('hidden');
        }
    }, 1000);
}

document.getElementById('phone-step').addEventListener('submit', (e) => {
    e.preventDefault();
    phone = document.getElementById('phone-input').value.trim();
    requestCode(e.target.querySelector('button'));
});
document.getElementById('resend-btn').addEventListener('click', (e) => requestCode(e.target));
document.getElementById('edit-phone').addEventListener('click', () => {
    document.getElementById('code-step').classList.add('hidden');
    document.getElementById('phone-step').classList.remove('hidden');
});
document.getElementById('code-step').addEventListener('submit', async (e) => {
    e.preventDefault();
    const button = e.target.querySelector('button');
    button.disabled = true;
    const code = document.getElementById('code-input').value.trim();
    const { status, data } = await post('{{ route('auth.verify') }}', { phone, code });
    button.disabled = false;
    if (status === 200) window.location.href = data.redirectTo;
    else showError('code-error', data.error || 'تأیید کد انجام نشد.');
});
</script>
@endpush
