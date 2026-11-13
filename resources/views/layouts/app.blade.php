<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نوبت سلامت | نوبت‌دهی آنلاین پزشکان')</title>
    <meta name="description" content="@yield('meta_description', 'جست‌وجوی پزشک، مشاهده نظرات بیماران و دریافت نوبت حضوری یا آنلاین از پزشکان معتبر سراسر ایران.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="نوبت سلامت">
    <meta property="og:title" content="@yield('og_title', View::getSection('title', 'نوبت سلامت'))">
    <meta property="og:description" content="@yield('meta_description')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.jpg'))">
    <meta name="theme-color" content="#0d9488">

    {{-- متاتگ نماد اعتماد الکترونیکی (ای‌نماد) — برای دکمهٔ «تایید متاتگ» در enamad.ir
         الزامی است و باید عیناً به این شکل در <head> صفحهٔ اصلی باشد.
         کد از پنل → محتوای سایت (کلید enamad_code) خوانده می‌شود و در هر حال
         به‌صورت عدد انگلیسی چاپ می‌گردد. --}}
    <meta name="enamad" content="{{ enamad_code() }}" />

    @yield('head')

    {{-- CSS و فونت به‌صورت محلی سرو می‌شوند (بدون CDN)؛ قبلاً اسکریپت مسدودکنندهٔ
         cdn.tailwindcss.com و استایل jsDelivr در head لود می‌شد و در شبکه‌هایی که
         به این دامنه‌ها دسترسی ندارند صفحه بالا نمی‌آمد. --}}
    <link rel="preload" href="{{ asset('fonts/vazirmatn-variable.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1' }}">
    <style>
        body { background: #fbfaf6; }
        .hero-vibrant {
            background:
                radial-gradient(900px 420px at 85% -10%, rgba(255,138,92,.22), transparent 60%),
                radial-gradient(800px 500px at 8% 115%, rgba(94,234,212,.4), transparent 60%),
                linear-gradient(180deg, #eaf9f4 0%, #f2fbf8 48%, #fbfaf6 100%);
        }
        .banner-gradient {
            background:
                radial-gradient(500px 200px at 15% 0%, rgba(94,234,212,.22), transparent 60%),
                linear-gradient(120deg, #0c4844 0%, #0d9488 62%, #14b8a6 100%);
        }
        .cta-gradient {
            background:
                radial-gradient(420px 220px at 85% 20%, rgba(255,255,255,.22), transparent 60%),
                linear-gradient(115deg, #ff6b57 0%, #ff8a5c 55%, #ffa65c 100%);
        }
        .title-gradient {
            background: linear-gradient(120deg, #0d9488 10%, #14b8a6 45%, #34d399 90%);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            border-radius: .8rem; padding: .8rem 1.4rem; color: #fff; font-weight: 800;
            background: linear-gradient(135deg, #14b8a6, #0d9488 55%, #0f766e);
            background-size: 170% 170%; box-shadow: 0 8px 20px rgba(13,148,136,.28);
            transition: .18s ease; cursor: pointer; border: 0;
        }
        .btn-primary:hover { transform: translateY(-2px); background-position: 90% 50%; box-shadow: 0 12px 28px rgba(13,148,136,.38); }
        .btn-primary:disabled { opacity: .55; transform: none; cursor: not-allowed; }
        .card-hover { transition: transform .22s ease, box-shadow .22s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 45px rgba(13,148,136,.16); }
        .input-field {
            width: 100%; border: 1px solid #dce9e7; border-radius: .8rem; background: #fff;
            padding: .75rem 1rem; color: #0f3538; outline: none; transition: .18s;
        }
        .input-field:focus { border-color: #0d9488; box-shadow: 0 0 0 4px rgba(13,148,136,.12); }
        .float-anim { animation: floating 5s ease-in-out infinite; }
        @keyframes floating { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-10px) } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(18px) } to { opacity: 1; transform: none } }
        .fade-up { animation: fadeUp .6s ease both; }
    </style>
</head>
<body class="font-sans text-ink antialiased">

@auth
    @php($panelUrl = auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel'))
@endauth

<header class="sticky top-0 z-40 border-b border-[#e6efed]/90 bg-cream/95 backdrop-blur-xl">
    <div class="mx-auto flex h-[76px] w-full max-w-[1180px] items-center justify-between gap-6 px-4">
        <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-2.5">
            <span class="grid size-11 place-items-center rounded-[15px] bg-gradient-to-br from-brand-bright to-brand text-white shadow-lg shadow-brand/20 transition group-hover:-rotate-3">
                <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/></svg>
            </span>
            <span>
                <strong class="block text-[19px] font-black leading-5">نوبت سلامت</strong>
                <small class="text-[10px] font-bold tracking-[.08em] text-[#78908e]">NOBAT SALAMAT</small>
            </span>
        </a>
        <nav class="hidden items-center gap-8 text-sm font-bold text-[#3e5f5d] lg:flex">
            <a href="{{ route('doctors.index') }}" class="transition hover:text-brand">پزشکان</a>
            <a href="{{ route('home') }}#specialties" class="transition hover:text-brand">تخصص‌ها</a>
            <a href="{{ route('doctors.index', ['visit' => 'online']) }}" class="transition hover:text-brand">مشاوره آنلاین</a>
            <a href="{{ route('articles.index') }}" class="transition hover:text-brand">مجله سلامت</a>
        </nav>
        <div class="flex items-center gap-2.5">
            @auth
                <a href="{{ $panelUrl }}" class="btn-primary !px-4 !py-2.5 text-xs">{{ auth()->user()->isAdmin() ? 'مدیریت سایت' : 'پنل من' }}</a>
            @else
                <a href="{{ route('login') }}" class="rounded-xl border border-[#7fd4c8] bg-white px-4 py-2.5 text-xs font-black text-brand transition hover:bg-[#f1fbf8]">ورود / عضویت</a>
            @endauth
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="mt-20 bg-gradient-to-br from-[#0d4d49] to-[#0b3230] text-white">
    <div class="mx-auto grid w-full max-w-[1180px] gap-10 px-4 py-14 lg:grid-cols-[1.2fr_2fr_1fr]">
        <div>
            <b class="text-xl font-black">{{ \App\Models\SiteSetting::get('site_name', 'نوبت سلامت') }}</b>
            <p class="mt-4 max-w-sm text-sm leading-7 text-[#bdd4d1]">{{ \App\Models\SiteSetting::get('footer_description') }}</p>
        </div>
        <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
            <div>
                <h3 class="mb-4 text-sm font-black">خدمات</h3>
                <ul class="space-y-3 text-xs text-[#a8c2bf]">
                    <li><a href="{{ route('doctors.index') }}" class="hover:text-white">جست‌وجوی پزشک</a></li>
                    <li><a href="{{ route('doctors.index', ['visit' => 'online']) }}" class="hover:text-white">مشاوره آنلاین</a></li>
                    <li><a href="{{ route('home') }}#specialties" class="hover:text-white">تخصص‌ها</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-black">نوبت سلامت</h3>
                <ul class="space-y-3 text-xs text-[#a8c2bf]">
                    <li><a href="#" class="hover:text-white">درباره ما</a></li>
                    <li><a href="#" class="hover:text-white">همکاری با پزشکان</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-black">حساب کاربری</h3>
                <ul class="space-y-3 text-xs text-[#a8c2bf]">
                    <li><a href="{{ route('login') }}" class="hover:text-white">ورود / ثبت‌نام</a></li>
                    <li><a href="{{ auth()->check()? route('panel') : route('login') }}" class="hover:text-white">نوبت‌های من</a></li>
                </ul>
            </div>
        </div>
        <div>
            <h3 class="mb-4 text-sm font-black">پشتیبانی</h3>
            <a href="tel:02191099090" class="flex items-center gap-3 rounded-2xl bg-white/10 p-3.5 transition hover:bg-white/15">
                <span class="grid size-10 place-items-center rounded-xl bg-coral">☎</span>
                <span><small class="block text-[10px] text-[#bfd2d0]">همه‌روزه ۸ تا ۲۲</small><b dir="ltr" class="text-sm">{{ \App\Models\SiteSetting::get('support_phone') }}</b></span>
            </a>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto flex w-full max-w-[1180px] flex-col items-center justify-between gap-3 px-4 py-5 text-[11px] text-[#a8c2bf] sm:flex-row">
            <p>© ۱۴۰۵ نوبت سلامت؛ تمام حقوق محفوظ است.</p>
            <a href="https://nobatesalamat.ir" class="hover:text-white" dir="ltr">nobatesalamat.ir</a>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
