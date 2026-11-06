@extends('admin.layout')
@section('title', 'مدیریت پزشکان')

@section('content')
<details class="admin-card mb-5 border-[#cfe4e0]" open>
    <summary class="cursor-pointer text-sm font-black">+ افزودن پزشک جدید</summary>
    <form action="{{ route('admin.doctors.store') }}" method="POST" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @csrf
        <input name="full_name" class="input-field" placeholder="نام کامل پزشک" required>
        <input name="title" class="input-field" placeholder="عنوان تخصصی" required>
        <select name="specialty_id" class="input-field" required>
            @foreach($specialties as $specialty)<option value="{{ $specialty->id }}">{{ $specialty->title }}</option>@endforeach
        </select>
        <input name="city" class="input-field" placeholder="شهر" value="تهران" required>
        <input name="medical_code" class="input-field" placeholder="شماره نظام پزشکی">
        <input name="visit_price" type="number" class="input-field" placeholder="هزینه ویزیت (تومان)" required>
        <input name="experience_years" type="number" class="input-field" placeholder="سابقه (سال)" required>
        <input name="next_available" class="input-field" placeholder="اولین نوبت" value="امروز">
        <input name="image_url" dir="ltr" class="input-field" placeholder="آدرس URL تصویر" required>
        <input name="address" class="input-field sm:col-span-2" placeholder="آدرس مطب">
        <label class="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 text-xs"><input type="checkbox" name="is_online" value="1">مشاوره آنلاین</label>
        <textarea name="about" class="input-field min-h-20 sm:col-span-2 lg:col-span-3" placeholder="درباره پزشک"></textarea>
        <div class="lg:col-span-3"><button class="btn-primary">ثبت پزشک</button></div>
    </form>
</details>

<div class="space-y-3">
    @foreach($doctors as $doctor)
        <details class="admin-card !p-0">
            <summary class="flex cursor-pointer list-none items-center gap-3 p-3.5">
                <img src="{{ $doctor->image_url }}" alt="" class="size-11 rounded-xl bg-[#eaf2f0] object-cover object-top">
                <div class="min-w-0 flex-1">
                    <b class="block truncate text-xs">{{ $doctor->full_name }}</b>
                    <small class="text-[9px] text-[#7b918e]">{{ $doctor->title }} · {{ $doctor->city }}</small>
                </div>
                <span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $doctor->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">{{ $doctor->is_active ? 'فعال' : 'غیرفعال' }}</span>
            </summary>
            <form action="{{ route('admin.doctors.update', $doctor) }}" method="POST" class="grid gap-3 border-t border-[#edf2f1] p-4 sm:grid-cols-2 lg:grid-cols-3">
                @csrf @method('PATCH')
                <input name="full_name" class="input-field" value="{{ $doctor->full_name }}">
                <input name="title" class="input-field" value="{{ $doctor->title }}">
                <select name="specialty_id" class="input-field">
                    @foreach($specialties as $specialty)<option value="{{ $specialty->id }}" {{ $doctor->specialty_id === $specialty->id ? 'selected' : '' }}>{{ $specialty->title }}</option>@endforeach
                </select>
                <input name="city" class="input-field" value="{{ $doctor->city }}">
                <input name="medical_code" class="input-field" value="{{ $doctor->medical_code }}">
                <input name="visit_price" type="number" class="input-field" value="{{ $doctor->visit_price }}">
                <input name="experience_years" type="number" class="input-field" value="{{ $doctor->experience_years }}">
                <input name="next_available" class="input-field" value="{{ $doctor->next_available }}">
                <input name="image_url" dir="ltr" class="input-field" value="{{ $doctor->image_url }}">
                <input name="address" class="input-field" value="{{ $doctor->address }}">
                <label class="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 text-xs">
                    <input type="checkbox" name="is_online" value="1" {{ $doctor->is_online ? 'checked' : '' }}>ویزیت آنلاین
                    <input type="hidden" name="is_online" value="0">
                </label>
                <textarea name="about" class="input-field min-h-16 sm:col-span-2 lg:col-span-3">{{ $doctor->about }}</textarea>
                <div class="flex gap-2 lg:col-span-3">
                    <button class="btn-primary">ذخیره</button>
                </div>
            </form>
            @if($doctor->is_active)
                <form action="{{ route('admin.doctors.destroy', $doctor) }}" method="POST" class="px-4 pb-4" onsubmit="return confirm('این پزشک از سایت غیرفعال شود؟')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg border border-[#dde9e7] bg-white px-3 py-2 text-xs font-bold text-red-500">غیرفعال‌سازی</button>
                </form>
            @endif
        </details>
    @endforeach
</div>
@endsection
