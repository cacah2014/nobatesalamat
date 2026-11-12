@extends('layouts.app')

@section('title', $article->title . ' | مجله سلامت نوبت سلامت')
@section('meta_description', \Illuminate\Support\Str::limit($article->excerpt, 155))
@section('canonical', route('articles.show', $article))
@section('og_image', media_url($article->image_url))
@section('og_type', 'article')

@section('content')
<article class="mx-auto w-full max-w-[820px] px-4 py-10">
    <a href="{{ route('articles.index') }}" class="text-xs font-bold text-brand">← بازگشت به مجله سلامت</a>

    <h1 class="mt-4 text-2xl font-black leading-9 sm:text-3xl">{{ $article->title }}</h1>
    <div class="mt-3 flex items-center gap-2 text-[11px] font-bold text-[#93a6a3]">
        <span>{{ $article->created_at?->format('Y/m/d') }}</span>
        <span>·</span>
        <span>{{ fa_digits($article->read_time) }} دقیقه مطالعه</span>
    </div>

    <img src="{{ media_url($article->image_url) }}" alt="{{ $article->title }}" class="mt-6 h-64 w-full rounded-[24px] bg-[#eaf2f0] object-cover sm:h-80">

    <div class="prose-fa mt-8 space-y-5 text-sm leading-8 text-[#3e5f5d]">
        @foreach(preg_split('/\R{2,}/u', (string) ($article->body ?: $article->excerpt)) as $paragraph)
            @if(trim($paragraph) !== '')
                <p>{{ trim($paragraph) }}</p>
            @endif
        @endforeach
    </div>

    <div class="mt-10 rounded-[24px] bg-gradient-to-br from-[#0d4d49] to-[#123030] p-6 text-white">
        <b class="block text-base">پزشک مناسب را پیدا نکرده‌اید؟</b>
        <p class="mt-2 text-xs leading-6 text-[#b5cfcc]">از میان پزشکان معتبر جست‌وجو کنید و اولین نوبت آزاد را رزرو کنید.</p>
        <a href="{{ route('doctors.index') }}" class="btn-primary mt-4 text-xs">مشاهدهٔ پزشکان</a>
    </div>
</article>

@if($related->isNotEmpty())
    <section class="mx-auto w-full max-w-[1180px] px-4 pb-14">
        <h2 class="mb-5 text-lg font-black">مطالب مرتبط</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($related as $item)
                <a href="{{ route('articles.show', $item) }}" class="group overflow-hidden rounded-[22px] border border-[#e0ebe9] bg-white transition hover:-translate-y-1 hover:shadow-xl">
                    <img src="{{ media_url($item->image_url) }}" alt="{{ $item->title }}" loading="lazy" class="h-36 w-full bg-[#eaf2f0] object-cover">
                    <div class="p-4">
                        <b class="block text-sm leading-6 transition group-hover:text-brand">{{ $item->title }}</b>
                        <p class="mt-1 line-clamp-2 text-[11px] leading-5 text-[#68807e]">{{ $item->excerpt }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
@endsection
