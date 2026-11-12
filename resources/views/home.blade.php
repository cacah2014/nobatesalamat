@extends('layouts.app')

@php
    $s = fn(string $key) => \App\Models\SiteSetting::get($key);

    // سؤالات متداول از دیتابیس (قابل ویرایش در پنل)؛ اگر خالی بود، متن پیش‌فرض
    $faqs = \App\Models\Faq::activeList()->map(fn ($faq) => [$faq->question, $faq->answer])->all();

    if ($faqs === []) {
        $faqs = [
            ['چطور از نوبت سلامت نوبت آنلاین بگیرم؟', 'پزشک یا تخصص موردنظر را جست‌وجو کنید، زمان آزاد را انتخاب و با شماره موبایل وارد شوید؛ نوبت شما در چند دقیقه با کد رهگیری قطعی می‌شود.'],
            ['آیا برای ورود رمز عبور لازم است؟', 'خیر؛ ورود فقط با شماره موبایل و کد تأیید پیامکی انجام می‌شود.'],
            ['امکان لغو نوبت وجود دارد؟', 'بله؛ از بخش «نوبت‌های من» در پنل کاربری می‌توانید نوبت را لغو کنید.'],
            ['مشاوره آنلاین چگونه برگزار می‌شود؟', 'پزشکانی که نشان «مشاوره آنلاین» دارند پاسخ‌گوی غیرحضوری هستند؛ جزئیات پس از ثبت نوبت ارسال می‌شود.'],
            ['هزینه ویزیت چگونه مشخص است؟', 'مبلغ دقیق روی پروفایل هر پزشک و پیش از ثبت نهایی نمایش داده می‌شود.'],
            ['اطلاعات پزشکان چگونه تأیید می‌شود؟', 'مدارک و شماره نظام پزشکی پیش از فعال‌شدن پروفایل بررسی می‌شود و نظرات فقط از مراجعان تأییدشده است.'],
        ];
    }

    // تصویر هیرو از پنل قابل تغییر است
    $heroImage = $s('hero_image') ?: 'https://images.pexels.com/photos/6749773/pexels-photo-6749773.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=1000&w=760';
    $supportTel = preg_replace('/[^0-9+]/', '', en_digits($s('support_phone', '02191099090')));
@endphp

@section('content')
{{-- هیرو --}}
<section class="hero-vibrant relative overflow-hidden py-10 lg:py-14">
    <div class="mx-auto grid w-full max-w-[1180px] items-center gap-10 px-4 lg:grid-cols-[1.08fr_.92fr]">
        <div class="fade-up">
            <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#b9ddd7] bg-white/80 px-3 py-1.5 text-xs font-black text-brand shadow-sm">
                <span class="grid size-6 place-items-center rounded-full bg-[#d3f2ea]">❤</span>
                {{ $s('hero_badge') }}
            </div>
            <h1 class="max-w-2xl text-[clamp(2.2rem,5vw,4rem)] font-black leading-[1.22] tracking-tight">
                پزشک مناسب، <span class="title-gradient">نزدیک‌تر</span> از همیشه
            </h1>
            <p class="mt-6 max-w-xl text-[15px] leading-8 text-[#536f6c]">{{ $s('hero_description') }}</p>

            <form action="{{ route('doctors.index') }}" class="mt-7 grid max-w-2xl gap-2 rounded-[20px] border border-white bg-white p-2.5 shadow-[0_18px_50px_rgba(11,80,75,.1)] md:grid-cols-[1fr_160px_auto]">
                <label class="flex items-center gap-3 rounded-xl bg-[#f7faf9] px-3.5 py-2.5">
                    <span class="text-brand">🔍</span>
                    <input name="q" class="w-full bg-transparent text-xs font-bold outline-none" placeholder="مثلاً متخصص قلب">
                </label>
                <select name="city" class="rounded-xl border border-[#edf2f1] bg-white px-3 text-xs font-bold outline-none">
                    <option value="">همه شهرها</option>
                    <option {{ request('city') === 'تهران' ? 'selected' : '' }}>تهران</option>
                    <option>شیراز</option>
                    <option>اصفهان</option>
                </select>
                <button class="btn-primary">جست‌وجو</button>
            </form>

            <div class="mt-7 flex flex-wrap gap-x-7 gap-y-3 text-xs text-[#587470]">
                <span><b>{{ $s('stats_doctors') }} پزشک</b></span>
                <span><b>{{ $s('stats_appointments') }} نوبت موفق</b></span>
                <span><b>{{ $s('stats_satisfaction') }} رضایت</b></span>
            </div>
        </div>

        <div class="relative hidden min-h-[480px] lg:block">
            <div class="float-anim absolute left-8 top-4 h-[420px] w-[330px] overflow-hidden rounded-[44px_44px_110px_44px] border-[7px] border-white shadow-2xl">
                <img src="{{ media_url($heroImage) }}" alt="پزشک نوبت سلامت" class="h-full w-full object-cover object-top">
            </div>
            <div class="absolute right-2 top-20 rounded-2xl bg-white/95 p-3.5 shadow-xl">
                <small class="block text-[9px] text-[#7c9390]">نوبت شما قطعی شد</small><b class="text-xs">امروز، ساعت ۱۸:۳۰</b>
            </div>
            <div class="absolute bottom-10 left-0 rounded-2xl bg-white/95 p-3.5 shadow-xl">
                <small class="block text-[9px] text-[#7c9390]">مشاوره آنلاین</small><b class="text-xs">بدون نیاز به مراجعه</b>
            </div>
        </div>
    </div>
</section>

{{-- بنر مشاوره فوری --}}
<section class="mx-auto mt-12 w-full max-w-[1180px] px-4">
    <div class="banner-gradient grid overflow-hidden rounded-[26px] text-white shadow-[0_24px_60px_rgba(13,148,136,.22)] md:grid-cols-[1fr_auto]">
        <div class="p-6 md:p-8">
            <span class="mb-1 inline-block rounded-full bg-white/10 px-2 py-0.5 text-[9px] font-bold">پزشک فوری</span>
            <h2 class="text-lg font-black">برای یک راهنمایی سریع آماده‌ای؟</h2>
            <p class="mt-1 text-xs leading-6 text-[#cfe6e2]">با پزشک عمومی آنلاین صحبت کن و مسیر درست درمان را پیدا کن.</p>
        </div>
        <a href="{{ route('doctors.index', ['visit' => 'online']) }}" class="m-5 flex items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-black text-ink transition hover:bg-coral hover:text-white md:m-7">شروع مشاوره</a>
    </div>
</section>

{{-- تخصص‌ها --}}
<section id="specialties" class="mx-auto w-full max-w-[1180px] px-4 py-20">
    <div class="mb-8">
        <span class="text-xs font-black text-coral">مسیر سریع‌تر</span>
        <h2 class="mt-2 text-3xl font-black">بر اساس تخصص پیدا کن</h2>
    </div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
        @foreach($specialties as $specialty)
            <a href="{{ route('doctors.index', ['specialty' => $specialty->slug]) }}" class="card-hover rounded-[20px] border border-[#e2ecea] bg-white p-4 text-center">
                <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-{{ $specialty->color === 'teal' ? 'teal' : $specialty->color }}-50 text-2xl">
                    {{ ['heart' => '❤', 'sparkles' => '✨', 'brain' => '🧠', 'flower' => '🌸', 'baby' => '👶', 'bone' => '🦴', 'eye' => '👁', 'stethoscope' => '🩺'][$specialty->icon] ?? '🩺' }}
                </span>
                <b class="mt-3 block text-xs">{{ $specialty->title }}</b>
                <small class="mt-1 block text-[9px] text-[#8aa09d]">مشاهده پزشکان</small>
            </a>
        @endforeach
    </div>
</section>

{{-- پزشکان منتخب --}}
<section class="bg-[#f0f7f5] py-20">
    <div class="mx-auto w-full max-w-[1180px] px-4">
        <div class="mb-8 flex items-end justify-between">
            <div><span class="text-xs font-black text-coral">انتخاب کاربران</span><h2 class="mt-2 text-3xl font-black">پزشکان محبوب این هفته</h2></div>
            <a href="{{ route('doctors.index') }}" class="text-xs font-black text-brand">مشاهده همه ←</a>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($featuredDoctors as $doctor)
                @include('partials.doctor-card')
            @endforeach
        </div>
    </div>
</section>

{{-- مجله سلامت --}}
@if($articles->isNotEmpty())
    <section class="mx-auto w-full max-w-[1180px] px-4 pb-4 pt-20" id="health-mag">
        <div class="mb-8 flex items-end justify-between">
            <div><span class="text-xs font-black text-coral">مجله سلامت</span><h2 class="mt-2 text-3xl font-black">تازه‌ترین مطالب</h2></div>
            <a href="{{ route('articles.index') }}" class="text-xs font-black text-brand">همهٔ مقالات ←</a>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($articles as $article)
                <a href="{{ route('articles.show', $article) }}" class="group overflow-hidden rounded-[22px] border border-[#e0ebe9] bg-white transition hover:-translate-y-1 hover:shadow-xl">
                    <img src="{{ media_url($article->image_url) }}" alt="{{ $article->title }}" loading="lazy" class="h-40 w-full bg-[#eaf2f0] object-cover">
                    <div class="p-4">
                        <b class="block text-sm leading-6 transition group-hover:text-brand">{{ $article->title }}</b>
                        <p class="mt-1 line-clamp-2 text-[11px] leading-5 text-[#68807e]">{{ $article->excerpt }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif

{{-- FAQ --}}
<section class="mx-auto w-full max-w-[1180px] px-4 pb-4 pt-20">
    <div class="grid items-start gap-10 lg:grid-cols-[.85fr_1.15fr]">
        <div>
            <span class="text-xs font-black text-coral">پاسخ سریع به شما</span>
            <h2 class="mt-2 text-3xl font-black leading-snug">سؤالات متداول کاربران</h2>
            <p class="mt-3 max-w-md text-sm leading-7 text-[#718784]">هر آنچه پیش از اولین نوبت باید بدانید.</p>
            <a href="tel:{{ $supportTel }}" class="btn-primary mt-6 text-xs">سؤال بیشتری دارید؟ تماس بگیرید</a>
        </div>
        <div class="space-y-3">
            @foreach($faqs as $i => [$question, $answer])
                <details class="group rounded-2xl border border-[#e0ebe9] bg-white open:border-[#7fd4c8]">
                    <summary class="flex cursor-pointer list-none items-center gap-3 p-4 text-sm font-bold">
                        <span class="grid size-8 shrink-0 place-items-center rounded-xl text-xs font-black {{ $i % 2 ? 'bg-[#fff0ec] text-coral' : 'bg-[#e9f8f4] text-brand' }} group-open:bg-brand group-open:text-white">{{ fa_digits($i + 1) }}</span>
                        <span class="flex-1">{{ $question }}</span>
                    </summary>
                    <p class="mx-4 -mt-1 border-t border-dashed border-[#e3efec] pb-4 pt-3 text-xs leading-7 text-[#5f7c7a]">{{ $answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto w-full max-w-[1180px] px-4 py-16">
    <div class="cta-gradient flex flex-col items-start justify-between gap-7 rounded-[30px] px-6 py-10 text-white shadow-[0_24px_60px_rgba(255,107,87,.3)] sm:px-12 md:flex-row md:items-center">
        <div><h2 class="text-2xl font-black">سلامتی را به فردا موکول نکن</h2><p class="mt-2 text-sm text-white/85">همین حالا پزشک موردنظرت را پیدا کن و اولین نوبت آزاد را بگیر.</p></div>
        <a href="{{ route('doctors.index') }}" class="rounded-2xl bg-white px-6 py-3.5 text-sm font-black text-[#d55440] shadow-lg">جست‌وجوی پزشک</a>
    </div>
</section>

{{-- JSON-LD سئو --}}
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'MedicalBusiness', 'name' => 'نوبت سلامت', 'url' => url('/'), 'telephone' => '+982191099090', 'address' => $s('address')], JSON_UNESCAPED_UNICODE) !!}
</script>
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs)], JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection
