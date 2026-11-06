@extends('layouts.app')

@section('title', 'جست‌وجو و رزرو نوبت پزشکان | نوبت سلامت')
@section('meta_description', 'فهرست پزشکان متخصص؛ جست‌وجو بر اساس تخصص و شهر، مشاهدهٔ امتیاز و نظرات و رزرو اولین نوبت آزاد.')
@section('canonical', route('doctors.index'))

@section('content')
<section class="border-b border-[#dceae7] bg-[#eaf7f3] py-9">
    <div class="mx-auto w-full max-w-[1180px] px-4">
        <h1 class="text-2xl font-black sm:text-3xl">پزشک مناسب شما</h1>
        <p class="mt-2 text-sm text-[#68807e]">بین پزشکان معتبر جست‌وجو و نزدیک‌ترین نوبت را رزرو کنید.</p>
        <form class="mt-6 grid gap-2 rounded-2xl bg-white p-2 shadow-lg sm:grid-cols-[1fr_170px_auto]">
            <input name="q" value="{{ $filters['q'] }}" class="input-field !border-0 !bg-[#f7faf9] text-xs" placeholder="نام پزشک یا تخصص">
            <select name="city" class="input-field text-xs">
                <option value="">همه شهرها</option>
                @foreach(['تهران', 'شیراز', 'اصفهان'] as $city)
                    <option {{ $filters['city'] === $city ? 'selected' : '' }}>{{ $city }}</option>
                @endforeach
            </select>
            @if($filters['specialty'])<input type="hidden" name="specialty" value="{{ $filters['specialty'] }}">@endif
            <button class="btn-primary">جست‌وجو</button>
        </form>
    </div>
</section>

<section class="mx-auto grid w-full max-w-[1180px] gap-7 px-4 py-10 lg:grid-cols-[230px_1fr]">
    <aside>
        <div class="sticky top-28 rounded-[20px] border border-[#e0ebe9] bg-white p-4">
            <b class="mb-4 block border-b border-[#edf2f1] pb-3 text-sm">فیلتر نتایج</b>
            <p class="mb-2 text-[10px] font-black text-[#718784]">تخصص</p>
            <a href="{{ route('doctors.index') }}" class="block rounded-xl px-3 py-2 text-xs {{ !$filters['specialty'] ? 'bg-[#e9f8f4] font-black text-brand' : 'text-[#526f6c] hover:bg-[#f5f9f8]' }}">همه تخصص‌ها</a>
            @foreach($specialties as $specialty)
                <a href="{{ route('doctors.index', ['specialty' => $specialty->slug]) }}" class="block rounded-xl px-3 py-2 text-xs {{ $filters['specialty'] === $specialty->slug ? 'bg-[#e9f8f4] font-black text-brand' : 'text-[#526f6c] hover:bg-[#f5f9f8]' }}">{{ $specialty->title }}</a>
            @endforeach
            <a href="{{ route('doctors.index', ['visit' => 'online']) }}" class="mt-4 block rounded-xl border px-3 py-2.5 text-xs font-bold {{ $filters['visit'] === 'online' ? 'border-brand bg-[#e9f8f4] text-brand' : 'border-[#e2ecea]' }}">فقط مشاوره آنلاین</a>
        </div>
    </aside>

    <div>
        <p class="mb-5 text-[10px] text-[#7b908d]">{{ fa_digits($doctors->total()) }} پزشک مطابق جست‌وجوی شما</p>
        @if($doctors->count())
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($doctors as $doctor)
                    @include('partials.doctor-card')
                @endforeach
            </div>
            <div class="mt-8" dir="ltr">{{ $doctors->links() }}</div>
        @else
            <div class="grid min-h-80 place-items-center rounded-[24px] border border-dashed border-[#cbdedb] bg-white p-8 text-center">
                <div>
                    <h3 class="font-black">پزشکی پیدا نشد</h3>
                    <p class="mt-2 text-xs text-[#718784]">فیلترها را تغییر دهید یا عبارت دیگری جست‌وجو کنید.</p>
                    <a href="{{ route('doctors.index') }}" class="btn-primary mt-5 text-xs">پاک کردن فیلترها</a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
