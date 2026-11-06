@extends('layouts.app')

@section('title', 'پنل کاربری | نوبت سلامت')
@section('robots', 'noindex')

@section('content')
<section class="mx-auto w-full max-w-[1180px] px-4 py-8">
    <div class="banner-gradient mb-7 flex items-center justify-between gap-4 rounded-[24px] p-5 text-white shadow-lg sm:p-6">
        <div class="flex items-center gap-3.5">
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white/15 text-xl font-black">{{ mb_substr($user->full_name, 0, 1) }}</span>
            <div>
                <p class="text-[10px] text-[#c8ece6]">سلام، {{ $user->full_name === 'کاربر نوبت سلامت' ? 'دوست عزیز' : $user->full_name }}؛ به نوبت سلامت خوش آمدی</p>
                <h1 class="mt-0.5 text-xl font-black sm:text-2xl">داشبورد سلامتی من</h1>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('doctors.index') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-black text-[#0b6e63] shadow-lg">دریافت نوبت جدید</a>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="rounded-xl bg-white/15 px-3 py-2.5 text-xs font-bold">خروج</button></form>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-[20px] border border-[#dce9e7] bg-white p-4"><span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white">📅</span><b class="mt-3 block text-2xl">{{ fa_digits($activeCount) }}</b><small class="text-[10px] text-[#718784]">نوبت فعال</small></div>
        <div class="rounded-[20px] border border-[#dce9e7] bg-white p-4"><span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-orange-400 to-rose-500 text-white">🩺</span><b class="mt-3 block text-2xl">{{ fa_digits($appointments->pluck('doctor_id')->unique()->count()) }}</b><small class="text-[10px] text-[#718784]">پزشک مراجعه‌شده</small></div>
        <div class="rounded-[20px] border border-[#dce9e7] bg-white p-4"><span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white">⏱</span><b class="mt-3 block text-2xl">{{ fa_digits($cancelledCount) }}</b><small class="text-[10px] text-[#718784]">نوبت لغوشده</small></div>
    </div>

    <div class="mt-6 grid items-start gap-6 xl:grid-cols-[1fr_310px]">
        <div class="rounded-[22px] border border-[#dce9e7] bg-white p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-black">نوبت‌های من</h2>
                <span class="rounded-lg bg-[#e9f8f4] px-2 py-1 text-[10px] font-bold text-brand">{{ fa_digits($appointments->count()) }} نوبت</span>
            </div>
            @forelse($appointments as $appointment)
                <div class="mb-3 rounded-2xl border p-3.5 {{ $appointment->status === 'cancelled' ? 'border-[#ebeeee] bg-[#f8f9f9] opacity-70' : 'border-[#e1ecea]' }}">
                    <div class="flex items-center gap-3">
                        <img src="{{ $appointment->doctor->image_url }}" alt="" class="size-16 rounded-2xl bg-[#e9f4f1] object-cover object-top">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('doctors.show', $appointment->doctor) }}" class="text-sm font-black hover:text-brand">{{ $appointment->doctor->full_name }}</a>
                            <p class="mt-1 text-[10px] text-[#718784]">{{ $appointment->doctor->title }}</p>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[10px] text-[#607976]">
                                <span>📅 {{ $appointment->appointment_date }} · {{ fa_digits($appointment->appointment_time) }}</span>
                                <span>{{ $appointment->visit_type === 'online' ? 'مشاوره آنلاین' : 'حضوری · ' . $appointment->doctor->city }}</span>
                            </div>
                        </div>
                        <span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $appointment->status === 'cancelled' ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-600' }}">{{ ['confirmed' => 'تأییدشده', 'completed' => 'انجام‌شده', 'cancelled' => 'لغوشده'][$appointment->status] }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-[#edf2f1] pt-3 text-[9px] text-[#7c918e]">
                        <span>کد رهگیری: <b dir="ltr">{{ $appointment->tracking_code }}</b> · {{ fa_money($appointment->price) }}</span>
                        @if($appointment->status !== 'cancelled')
                            <button class="cancel-btn text-[10px] font-bold text-red-500" data-id="{{ $appointment->id }}">لغو نوبت</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="grid min-h-64 place-items-center text-center">
                    <div>
                        <h3 class="text-sm font-black">هنوز نوبتی ثبت نکرده‌اید</h3>
                        <p class="mt-2 text-xs text-[#718784]">پزشک مناسب را پیدا کنید و اولین نوبت خود را بگیرید.</p>
                        <a href="{{ route('doctors.index') }}" class="btn-primary mt-4 !py-2.5 text-xs">جست‌وجوی پزشک</a>
                    </div>
                </div>
            @endforelse
        </div>

        <aside class="space-y-5">
            <div class="rounded-[22px] border border-[#dce9e7] bg-white p-5">
                <h2 class="mb-4 border-b border-[#edf2f1] pb-3 text-sm font-black">اطلاعات حساب</h2>
                <form id="profile-form" class="space-y-3">
                    @csrf @method('PATCH')
                    <div><label class="mb-1.5 block text-[13px] font-bold">نام و نام خانوادگی</label><input name="full_name" class="input-field text-xs" value="{{ $user->full_name === 'کاربر نوبت سلامت' ? '' : $user->full_name }}" placeholder="نام شما"></div>
                    <div><label class="mb-1.5 block text-[13px] font-bold">شماره موبایل</label><input class="input-field bg-[#f5f8f7] text-xs text-[#718784]" value="{{ $user->phone }}" dir="ltr" disabled></div>
                    <button class="btn-primary !px-4 !py-2.5 text-xs">ذخیره تغییرات</button>
                    <p id="profile-message" class="hidden text-[10px] font-bold text-brand"></p>
                </form>
            </div>
            <div class="rounded-[22px] bg-gradient-to-br from-[#0d4d49] to-[#0b3230] p-5 text-white">
                <b class="text-sm">نیاز به راهنمایی دارید؟</b>
                <p class="mt-2 text-[10px] leading-6 text-[#b9cfcc]">تیم پشتیبانی همه‌روزه از ساعت ۸ تا ۲۲ پاسخ‌گوی شماست.</p>
                <a href="tel:02191099090" class="mt-3 block text-xs font-black text-[#5eead4]">{{ \App\Models\SiteSetting::get('support_phone') }}</a>
            </div>
        </aside>
    </div>
</section>
@endsection

@push('scripts')
<script>
const token = document.querySelector('meta[name="csrf-token"]').content;
document.getElementById('profile-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const response = await fetch('{{ route('panel.profile') }}', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ full_name: e.target.full_name.value }),
    });
    const message = document.getElementById('profile-message');
    message.textContent = response.ok ? 'اطلاعات با موفقیت ذخیره شد.' : 'ذخیره انجام نشد.';
    message.classList.remove('hidden');
});
document.querySelectorAll('.cancel-btn').forEach((button) => button.addEventListener('click', async () => {
    if (!confirm('آیا از لغو این نوبت مطمئن هستید؟')) return;
    const response = await fetch('/appointments/' + button.dataset.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token } });
    if (response.ok) location.reload();
}));
</script>
@endpush
