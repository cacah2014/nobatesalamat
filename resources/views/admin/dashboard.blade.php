@extends('admin.layout')
@section('title', 'نمای کلی')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([['کل کاربران', fa_digits($stats['users']), 'from-teal-500 to-emerald-600'], ['پزشکان فعال', fa_digits($stats['doctors']), 'from-orange-400 to-rose-500'], ['کل نوبت‌ها', fa_digits($stats['appointments']), 'from-sky-500 to-indigo-500'], ['ارزش نوبت‌ها', fa_money($stats['revenue']), 'from-amber-400 to-orange-500']] as [$label, $value, $gradient])
        <div class="admin-card">
            <span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br {{ $gradient }} text-white shadow-md">◆</span>
            <b class="mt-4 block text-xl font-black">{{ $value }}</b>
            <small class="text-[10px] text-[#718784]">{{ $label }}</small>
        </div>
    @endforeach
</div>

<div class="mt-6 grid items-start gap-5 xl:grid-cols-[1fr_310px]">
    <div class="admin-card">
        <h2 class="mb-4 text-sm font-black">آخرین نوبت‌ها</h2>
        @forelse($recentAppointments as $appointment)
            <div class="flex items-center justify-between border-b border-[#f0f4f3] py-3 text-[10px] last:border-0">
                <div><b class="block text-xs">{{ $appointment->patient_name }}</b><span dir="ltr" class="text-[#829592]">{{ $appointment->user?->phone ?? '—' }}</span></div>
                <div class="text-center">{{ $appointment->doctor?->full_name ?? '—' }}<br><span class="text-[#829592]">{{ $appointment->appointment_date }} {{ fa_digits($appointment->appointment_time) }}</span></div>
                <span class="rounded-full px-2 py-1 font-bold {{ $appointment->status === 'cancelled' ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-600' }}">{{ ['confirmed' => 'تأییدشده', 'completed' => 'انجام‌شده', 'cancelled' => 'لغوشده'][$appointment->status] ?? $appointment->status }}</span>
            </div>
        @empty
            <p class="py-10 text-center text-xs text-[#829592]">هنوز نوبتی ثبت نشده است.</p>
        @endforelse
    </div>
    <div class="space-y-5">
        <div class="rounded-[20px] bg-gradient-to-br from-[#0d4d49] to-[#123030] p-5 text-white">
            <h3 class="text-sm font-black">وضعیت سامانه پایدار است</h3>
            <p class="mt-2 text-[10px] leading-5 text-[#b5cfcc]">دیتابیس MySQL و سرویس نوبت‌دهی در دسترس هستند.</p>
            <span class="mt-4 inline-flex items-center gap-1 rounded-full bg-emerald-400/10 px-2 py-1 text-[9px] font-bold text-[#8ce9d2]">● همه سرویس‌ها آنلاین</span>
        </div>
        @if(auth()->user()->hasPermission('updates'))
            <div class="admin-card">
                <h3 class="text-sm font-black">آخرین آپدیت</h3>
                @if($lastUpdate)
                    <p class="mt-3 text-[10px] text-[#718784]">{{ fa_date($lastUpdate->started_at) }}</p>
                    <span class="mt-2 inline-block rounded-full bg-[#e9f8f4] px-2 py-1 text-[9px] font-bold text-brand">{{ ['success' => 'موفق', 'failed' => 'ناموفق', 'running' => 'در حال اجرا'][$lastUpdate->status] ?? $lastUpdate->status }}</span>
                @else
                    <p class="mt-3 text-[10px] text-[#829592]">هنوز آپدیتی ثبت نشده است.</p>
                @endif
                <a href="{{ route('admin.update') }}" class="mt-4 block text-[10px] font-black text-brand">مدیریت بروزرسانی</a>
            </div>
        @endif
    </div>
</div>
@endsection
