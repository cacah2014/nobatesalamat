@extends('admin.layout')
@section('title', 'مدیران و دسترسی‌ها')

@section('content')
<div class="grid items-start gap-5 lg:grid-cols-[1fr_340px]">
    <div class="admin-card">
        <h2 class="mb-4 border-b border-[#edf2f1] pb-3 text-sm font-black">مدیران فعال و سطح دسترسی</h2>
        @foreach($admins as $item)
            @php($isSuper = $item->isSuperAdmin())
            <div class="mb-3 rounded-2xl border border-[#e1ecea]">
                <div class="flex items-center gap-3 p-3.5">
                    <span class="grid size-10 place-items-center rounded-xl font-black {{ $isSuper ? 'bg-gradient-to-br from-amber-400 to-orange-500 text-white' : 'bg-[#e9f8f4] text-brand' }}">{{ mb_substr($item->full_name, 0, 1) }}</span>
                    <div class="min-w-0 flex-1">
                        <b class="block truncate text-xs">{{ $item->full_name }}</b>
                        <span dir="ltr" class="block text-[10px] text-[#7b918e]">{{ $item->phone }}</span>
                    </div>
                    <span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $isSuper ? 'bg-amber-50 text-amber-700' : 'bg-[#e9f8f4] text-brand' }}">{{ $isSuper ? 'مدیر ارشد' : 'مدیر' }}</span>
                    @if($item->id !== auth()->id())
                        <form action="{{ route('admin.admins.destroy', $item) }}" method="POST" onsubmit="return confirm('دسترسی مدیریتی این شماره حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="grid size-8 place-items-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600">✕</button>
                        </form>
                    @endif
                </div>

                @if($isSuper)
                    <p class="mx-3.5 mb-3.5 rounded-xl bg-gradient-to-l from-emerald-50 to-teal-50 px-3 py-2.5 text-[10px] font-bold text-emerald-700">🔑 دسترسی کامل به همهٔ بخش‌های سایت</p>
                @else
                    <form action="{{ route('admin.admins.permissions', $item) }}" method="POST" class="mx-3.5 mb-3.5 rounded-xl border border-[#edf2f1] bg-[#fafcfb] p-3">
                        @csrf @method('PATCH')
                        <p class="mb-2.5 text-[9px] font-black text-[#718784]">بخش‌های مجاز برای این مدیر</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach($permissionLabels as $key => $label)
                                @php($checked = in_array($key, $item->permissions ?? [], true))
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border p-2.5 text-right transition {{ $checked ? 'border-brand bg-[#e9f8f4]' : 'border-[#e5eeec] bg-white' }}">
                                    <span class="min-w-0 flex-1 text-[10px] font-bold {{ $checked ? 'text-[#0b7c71]' : 'text-[#526f6c]' }}">{{ $label }}</span>
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" {{ $checked ? 'checked' : '' }} class="accent-brand">
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <button class="btn-primary !px-4 !py-2 !text-[10px]">ذخیره دسترسی‌ها</button>
                            @if(empty($item->permissions))<span class="text-[9px] text-[#93a6a3]">بدون دسترسی: فقط «نمای کلی»</span>@endif
                        </div>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <form action="{{ route('admin.admins.store') }}" method="POST" class="admin-card">
        @csrf
        <h2 class="text-sm font-black">افزودن مدیر جدید</h2>
        <p class="mt-1 text-[10px] leading-5 text-[#7b918e]">مدیر با شماره موبایل خود وارد می‌شود و فقط به بخش‌هایی دسترسی دارد که شما تعیین کنید.</p>
        <div class="mt-4 space-y-3">
            <input name="full_name" class="input-field" placeholder="نام و نام خانوادگی" required>
            <input name="phone" dir="ltr" class="input-field" placeholder="09121234567" required>
            <button class="btn-primary w-full">افزودن مدیر</button>
        </div>
    </form>
</div>
@endsection
