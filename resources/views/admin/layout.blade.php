<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') | مدیریت نوبت سلامت</title>
    <link rel="preload" href="{{ asset('fonts/vazirmatn-variable.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1' }}">
    <style>
        .btn-primary { display: inline-flex; align-items: center; gap: .5rem; border-radius: .8rem; padding: .6rem 1.1rem; color: #fff; font-weight: 800; font-size: .75rem; background: linear-gradient(135deg, #14b8a6, #0d9488 55%, #0f766e); box-shadow: 0 8px 20px rgba(13,148,136,.28); transition: .18s; border: 0; cursor: pointer; }
        .btn-primary:hover { transform: translateY(-2px); }
        .input-field { width: 100%; border: 1px solid #dce9e7; border-radius: .8rem; background: #fff; padding: .6rem .9rem; font-size: .75rem; outline: none; }
        .input-field:focus { border-color: #0d9488; box-shadow: 0 0 0 4px rgba(13,148,136,.12); }
        .admin-card { border: 1px solid #e0ebe9; border-radius: 1.25rem; background: #fff; padding: 1.25rem; }
    </style>
</head>
<body class="bg-[#f3f7f6] font-sans text-[#123b3a] antialiased">

@php($admin = auth()->user())

<div class="grid min-h-screen lg:grid-cols-[260px_1fr]">
    <aside class="hidden flex-col bg-gradient-to-b from-[#0d4d49] to-[#0b3230] p-5 text-white lg:flex">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-[13px] bg-white text-brand font-black">❤</span>
            <span><b class="block text-base">نوبت سلامت</b><small class="text-[9px] text-[#9fbfbc]">مدیریت یکپارچه سایت</small></span>
        </a>
        <div class="mt-8 flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[.06] p-3">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl font-black {{ $admin->isSuperAdmin() ? 'bg-gradient-to-br from-amber-400 to-orange-500' : 'bg-gradient-to-br from-teal-400 to-emerald-500' }}">{{ mb_substr($admin->full_name, 0, 1) }}</span>
            <div class="min-w-0"><b class="block truncate text-xs">{{ $admin->full_name }}</b><small class="text-[9px] text-[#9fbfbc]">{{ $admin->isSuperAdmin() ? 'مدیر ارشد · دسترسی کامل' : 'مدیر سایت' }}</small></div>
        </div>

        <nav class="mt-6 space-y-1 text-xs font-bold">
            @php
                $menu = [
                    ['admin.dashboard', 'نمای کلی', true],
                    ['admin.content', 'محتوای سایت', $admin->hasPermission('content')],
                    ['admin.doctors', 'مدیریت پزشکان', $admin->hasPermission('doctors')],
                    ['admin.appointments', 'مدیریت نوبت‌ها', $admin->hasPermission('appointments')],
                    ['admin.admins', 'مدیران و دسترسی‌ها', $admin->isSuperAdmin()],
                    ['admin.update', 'بروزرسانی پروژه', $admin->hasPermission('updates')],
                ];
            @endphp
            @foreach($menu as [$route, $label, $allowed])
                @if($allowed)
                    <a href="{{ route($route) }}" class="block rounded-xl px-3 py-3 transition {{ request()->routeIs($route . '*') ? 'bg-white text-[#0d3837] shadow-lg' : 'text-[#a7c4c1] hover:bg-white/[.06] hover:text-white' }}">{{ $label }}</a>
                @endif
            @endforeach
        </nav>

        @if(! $admin->isSuperAdmin())
            <p class="mt-4 rounded-xl border border-dashed border-white/15 p-3 text-[9px] leading-5 text-[#8fb1ae]">
                @php($count = count(array_filter($admin->permissions ?? [])))
                {{ $count ? 'دسترسی شما به ' . fa_digits($count) . ' بخش فعال است.' : 'مدیر ارشد هنوز دسترسی بخشی را برای شما تعیین نکرده است.' }}
            </p>
        @endif

        <div class="mt-auto space-y-1">
            <a href="{{ route('home') }}" class="block rounded-xl px-3 py-2.5 text-[10px] text-[#a7c4c1] hover:bg-white/[.06]">مشاهده سایت</a>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="w-full rounded-xl px-3 py-2.5 text-right text-[10px] text-[#ffb0a2] hover:bg-white/[.06]">خروج امن</button></form>
        </div>
    </aside>

    <section class="min-w-0 p-4 sm:p-7 lg:p-9">
        <header class="mb-7 flex items-center justify-between gap-3">
            <div><p class="text-[10px] font-bold text-brand">پنل مدیریت</p><h1 class="mt-1 text-2xl font-black">@yield('title')</h1></div>
            <span class="hidden rounded-xl border border-[#dce9e7] bg-white px-3 py-2 text-[10px] text-[#607976] sm:block">{{ fa_date(now()) }}</span>
        </header>

        @if(session('success'))
            <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-700">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-xs font-bold text-red-600">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </section>
</div>

@stack('scripts')
</body>
</html>
