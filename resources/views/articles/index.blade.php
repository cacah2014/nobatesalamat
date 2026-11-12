@extends('layouts.app')

@section('title', 'مجله سلامت | مقالات پزشکی نوبت سلامت')
@section('meta_description', 'مقالات و راهنمای سلامت: انتخاب پزشک، چکاپ سالانه، ویزیت آنلاین و نکات مراقبت از سلامت.')
@section('canonical', route('articles.index'))

@section('content')
<section class="border-b border-[#dceae7] bg-[#eaf7f3] py-9">
    <div class="mx-auto w-full max-w-[1180px] px-4">
        <h1 class="text-2xl font-black sm:text-3xl">مجله سلامت</h1>
        <p class="mt-2 text-sm text-[#68807e]">راهنماها و نکته‌های کاربردی برای انتخاب پزشک و مراقبت از سلامت.</p>
    </div>
</section>

<section class="mx-auto w-full max-w-[1180px] px-4 py-10">
    @if($articles->isEmpty())
        <p class="rounded-2xl border border-dashed border-[#d8e6e3] bg-white p-10 text-center text-sm text-[#7b918e]">هنوز مقاله‌ای منتشر نشده است.</p>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($articles as $article)
                <article class="group overflow-hidden rounded-[22px] border border-[#e0ebe9] bg-white transition hover:-translate-y-1 hover:shadow-xl">
                    <a href="{{ route('articles.show', $article) }}" class="block">
                        <img src="{{ media_url($article->image_url) }}" alt="{{ $article->title }}" loading="lazy" class="h-44 w-full bg-[#eaf2f0] object-cover">
                    </a>
                    <div class="p-5">
                        <div class="flex items-center gap-2 text-[10px] font-bold text-[#93a6a3]">
                            <span>{{ $article->created_at?->format('Y/m/d') }}</span>
                            <span>·</span>
                            <span>{{ fa_digits($article->read_time) }} دقیقه مطالعه</span>
                        </div>
                        <h2 class="mt-2 text-base font-black leading-7">
                            <a href="{{ route('articles.show', $article) }}" class="transition hover:text-brand">{{ $article->title }}</a>
                        </h2>
                        <p class="mt-2 line-clamp-3 text-xs leading-6 text-[#68807e]">{{ $article->excerpt }}</p>
                        <a href="{{ route('articles.show', $article) }}" class="mt-4 inline-block text-xs font-black text-brand">ادامهٔ مطلب ←</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10">{{ $articles->links() }}</div>
    @endif
</section>
@endsection
