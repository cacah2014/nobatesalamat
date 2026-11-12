@extends('admin.layout')
@section('title', 'حوزه‌های کاری')

@section('content')
<details class="admin-card mb-5 border-[#cfe4e0]" open>
    <summary class="cursor-pointer text-sm font-black">+ افزودن حوزهٔ کاری جدید</summary>
    <form action="{{ route('admin.specialties.store') }}" method="POST" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @csrf
        <input name="title" class="input-field" placeholder="نام حوزه (مثلاً: قلب و عروق)" required>
        <input name="icon" class="input-field" placeholder="آیکن (ایموجی مثل ❤ یا ✨)" value="🩺">
        <select name="color" class="input-field">
            @foreach(['teal' => 'فیروزه‌ای', 'coral' => 'مرجانی', 'amber' => 'کهربایی', 'sky' => 'آبی', 'rose' => 'صورتی', 'emerald' => 'سبز'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <input name="sort_order" type="number" class="input-field" placeholder="ترتیب نمایش (۰ = اول)" value="0">
        <input name="description" class="input-field lg:col-span-2" placeholder="توضیح کوتاه (اختیاری)">
        <label class="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 text-xs"><input type="checkbox" name="is_active" value="1" checked>فعال در سایت</label>
        <div class="lg:col-span-3"><button class="btn-primary">ثبت حوزهٔ کاری</button></div>
    </form>
</details>

<div class="space-y-3">
    @forelse($specialties as $specialty)
        <details class="admin-card !p-0">
            <summary class="flex cursor-pointer list-none items-center gap-3 p-3.5">
                <span class="grid size-10 place-items-center rounded-xl bg-[#e9f8f4] text-lg">{{ $specialty->icon }}</span>
                <div class="min-w-0 flex-1">
                    <b class="block truncate text-xs">{{ $specialty->title }}</b>
                    <small class="text-[9px] text-[#7b918e]">{{ fa_digits($specialty->doctors_count) }} پزشک · <span dir="ltr">{{ $specialty->slug }}</span></small>
                </div>
                <span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $specialty->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">{{ $specialty->is_active ? 'فعال' : 'غیرفعال' }}</span>
            </summary>
            <form action="{{ route('admin.specialties.update', $specialty) }}" method="POST" class="grid gap-3 border-t border-[#edf2f1] p-4 sm:grid-cols-2 lg:grid-cols-3">
                @csrf @method('PATCH')
                <input name="title" class="input-field" value="{{ $specialty->title }}" required>
                <input name="icon" class="input-field" value="{{ $specialty->icon }}">
                <select name="color" class="input-field">
                    @foreach(['teal' => 'فیروزه‌ای', 'coral' => 'مرجانی', 'amber' => 'کهربایی', 'sky' => 'آبی', 'rose' => 'صورتی', 'emerald' => 'سبز'] as $value => $label)
                        <option value="{{ $value }}" {{ $specialty->color === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input name="sort_order" type="number" class="input-field" value="{{ $specialty->sort_order }}">
                <input name="description" class="input-field lg:col-span-2" value="{{ $specialty->description }}" placeholder="توضیح کوتاه">
                <label class="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 text-xs"><input type="checkbox" name="is_active" value="1" {{ $specialty->is_active ? 'checked' : '' }}>فعال در سایت</label>
                <div class="flex gap-2 lg:col-span-3"><button class="btn-primary">ذخیره</button></div>
            </form>
            @if($specialty->doctors_count === 0)
                <form action="{{ route('admin.specialties.destroy', $specialty) }}" method="POST" class="px-4 pb-4" onsubmit="return confirm('این حوزهٔ کاری حذف شود؟')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg border border-[#dde9e7] bg-white px-3 py-2 text-xs font-bold text-red-500">حذف کامل</button>
                </form>
            @endif
        </details>
    @empty
        <p class="admin-card text-center text-xs text-[#7b918e]">هنوز حوزهٔ کاری ثبت نشده است.</p>
    @endforelse
</div>
@endsection
