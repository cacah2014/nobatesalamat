@extends('admin.layout')
@section('title', 'محتوای سایت')

@section('content')
<div class="mb-5 rounded-[18px] border border-[#cfe5e1] bg-[#e9f8f4] p-4 text-xs leading-6">
    <b>ویرایش زندهٔ محتوای سایت</b>
    <p class="text-[10px]">همهٔ متن‌های سایت (صفحهٔ اصلی، فوتر، اطلاعات تماس، شبکه‌های اجتماعی) و تصاویر را از اینجا تغییر دهید؛ بلافاصله روی سایت اعمال می‌شود.</p>
</div>

<form action="{{ route('admin.content.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf @method('PATCH')

    @foreach($settings as $group => $items)
        @php($groupNames = ['general' => 'تنظیمات عمومی', 'homepage' => 'صفحه اصلی', 'footer' => 'فوتر', 'social' => 'شبکه‌های اجتماعی', 'contact' => 'اطلاعات تماس'])
        <fieldset class="admin-card">
            <legend class="px-2 text-sm font-black">{{ $groupNames[$group] ?? $group }}</legend>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($items as $setting)
                    @php($isImage = ($setting->type ?? 'text') === 'image')
                    @php($isLong = ($setting->type ?? 'text') === 'textarea' || mb_strlen((string) $setting->value) > 90)

                    <label class="{{ $isLong || $isImage ? 'sm:col-span-2' : '' }}">
                        <span class="mb-1.5 block text-[13px] font-bold text-[#315b58]">{{ $setting->label }}</span>

                        @if($isImage)
                            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-[#e0ebe9] p-3">
                                <img src="{{ media_url($setting->value) }}" alt="" class="size-16 rounded-xl bg-[#eaf2f0] object-cover">
                                <div class="min-w-0 flex-1 space-y-2">
                                    <input type="file" name="settings_images[{{ $setting->key }}]" accept="image/*" class="input-field !py-1.5 text-[10px]">
                                    <input name="settings[{{ $setting->key }}]" dir="ltr" class="input-field" value="{{ $setting->value }}" placeholder="/uploads/... یا آدرس کامل">
                                    @if($setting->value !== '')
                                        <label class="flex items-center gap-2 text-[10px] text-red-500"><input type="checkbox" name="remove_image[{{ $setting->key }}]" value="1">حذف تصویر فعلی</label>
                                    @endif
                                </div>
                            </div>
                        @elseif($isLong)
                            <textarea name="settings[{{ $setting->key }}]" class="input-field min-h-24 resize-y">{{ $setting->value }}</textarea>
                        @else
                            <input name="settings[{{ $setting->key }}]" class="input-field" value="{{ $setting->value }}">
                        @endif

                        <small class="mt-1 block text-[9px] text-[#93a6a3]" dir="ltr">{{ $setting->key }}</small>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach

    <div class="flex justify-end"><button class="btn-primary">ذخیره و انتشار</button></div>
</form>

<div class="mt-6 grid items-start gap-5 lg:grid-cols-[1fr_360px]">
    {{-- افزودن کلید جدید --}}
    <form action="{{ route('admin.content.store') }}" method="POST" enctype="multipart/form-data" class="admin-card">
        @csrf
        <h2 class="text-sm font-black">افزودن متن/تصویر جدید به سایت</h2>
        <p class="mt-1 text-[10px] leading-5 text-[#7b918e]">اگر متنی در سایت هست که اینجا نیست (مثل عنوان یک بخش یا بنر)، با یک کلید جدید بسازید و مقدارش را وارد کنید.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <input name="key" dir="ltr" class="input-field" placeholder="hero_cta_title" required>
            <input name="label" class="input-field" placeholder="برچسب نمایشی" required>
            <select name="group" class="input-field">
                @foreach($groups as $group)
                    <option value="{{ $group }}">{{ ['general' => 'عمومی', 'homepage' => 'صفحه اصلی', 'footer' => 'فوتر', 'social' => 'شبکه‌های اجتماعی', 'contact' => 'تماس'][$group] ?? $group }}</option>
                @endforeach
            </select>
            <select name="type" class="input-field">
                <option value="text">متن کوتاه</option>
                <option value="textarea">متن بلند</option>
                <option value="image">تصویر</option>
            </select>
            <input name="value" class="input-field sm:col-span-2" placeholder="مقدار اولیه (اختیاری)">
            <input type="file" name="image" accept="image/*" class="input-field sm:col-span-2 !py-1.5 text-[10px]">
        </div>
        <button class="btn-primary mt-4">ساخت کلید</button>
    </form>

    {{-- حذف کلید --}}
    <div class="admin-card">
        <h2 class="text-sm font-black">حذف کلید از سایت</h2>
        <p class="mt-1 text-[10px] leading-5 text-[#7b918e]">حذف کلید باعث می‌شود آن متن/تصویر از قالب حذف شود. مطمئن شوید در قالب استفاده نشده است.</p>
        <div class="mt-3 max-h-72 space-y-2 overflow-auto">
            @foreach($settings->flatten() as $setting)
                <div class="flex items-center justify-between gap-2 rounded-xl border border-[#edf2f1] bg-[#fafcfb] px-3 py-2">
                    <span class="min-w-0">
                        <b class="block truncate text-[11px]">{{ $setting->label }}</b>
                        <small class="block truncate text-[9px] text-[#93a6a3]" dir="ltr">{{ $setting->key }}</small>
                    </span>
                    <form action="{{ route('admin.content.destroy', $setting->key) }}" method="POST" onsubmit="return confirm('کلید {{ $setting->key }} حذف شود؟')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg px-2 py-1 text-xs font-bold text-red-400 hover:bg-red-50 hover:text-red-600">✕</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
