@extends('admin.layout')
@section('title', 'محتوای سایت')

@section('content')
<div class="mb-5 rounded-[18px] border border-[#cfe5e1] bg-[#e9f8f4] p-4 text-xs leading-6">
    <b>ویرایش زنده محتوای سایت</b>
    <p class="text-[10px]">متن‌های اصلی، آمار، اطلاعات تماس و فوتر را از این بخش تغییر دهید؛ بلافاصله در سایت اعمال می‌شود.</p>
</div>

<form action="{{ route('admin.content.update') }}" method="POST" class="space-y-6">
    @csrf @method('PATCH')
    @foreach($settings as $group => $items)
        @php($groupNames = ['general' => 'تنظیمات عمومی', 'homepage' => 'صفحه اصلی', 'footer' => 'فوتر', 'social' => 'شبکه‌های اجتماعی'])
        <fieldset class="admin-card">
            <legend class="px-2 text-sm font-black">{{ $groupNames[$group] ?? $group }}</legend>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($items as $setting)
                    <label class="{{ mb_strlen($setting->value) > 80 ? 'sm:col-span-2' : '' }}">
                        <span class="mb-1.5 block text-[13px] font-bold text-[#315b58]">{{ $setting->label }}</span>
                        @if(mb_strlen($setting->value) > 80)
                            <textarea name="settings[{{ $setting->key }}]" class="input-field min-h-24 resize-y">{{ $setting->value }}</textarea>
                        @else
                            <input name="settings[{{ $setting->key }}]" class="input-field" value="{{ $setting->value }}">
                        @endif
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
    <div class="flex justify-end"><button class="btn-primary">ذخیره و انتشار</button></div>
</form>
@endsection
