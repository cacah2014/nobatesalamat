@extends('admin.layout')
@section('title', 'مقالات')

@section('content')
<details class="admin-card mb-5 border-[#cfe4e0]" open>
    <summary class="cursor-pointer text-sm font-black">+ نوشتن مقالهٔ جدید</summary>
    <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-3 sm:grid-cols-2">
        @csrf
        <input name="title" class="input-field sm:col-span-2" placeholder="عنوان مقاله" required>
        <textarea name="excerpt" class="input-field min-h-20 sm:col-span-2" placeholder="خلاصهٔ کوتاه (در لیست و کارت‌ها نمایش داده می‌شود)" required></textarea>
        <textarea name="body" class="input-field min-h-40 sm:col-span-2" placeholder="متن کامل مقاله — هر پاراگراف را با یک خط خالی جدا کنید"></textarea>
        <div class="space-y-2 rounded-xl border border-[#e0ebe9] p-3">
            <span class="block text-[11px] font-bold text-[#315b58]">تصویر مقاله</span>
            <input type="file" name="image" accept="image/*" class="input-field !py-1.5 text-[10px]">
            <input name="image_url" dir="ltr" class="input-field" placeholder="یا آدرس URL تصویر">
            <small class="block text-[9px] text-[#93a6a3]">حجم حداکثر ۵ مگابایت · jpg / png / webp</small>
        </div>
        <div class="space-y-3">
            <input name="read_time" type="number" class="input-field" placeholder="زمان مطالعه (دقیقه)" value="4">
            <label class="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 py-2.5 text-xs"><input type="checkbox" name="is_published" value="1" checked>منتشر شود</label>
        </div>
        <div class="sm:col-span-2"><button class="btn-primary">ثبت مقاله</button></div>
    </form>
</details>

<div class="space-y-3">
    @forelse($articles as $article)
        <details class="admin-card !p-0">
            <summary class="flex cursor-pointer list-none items-center gap-3 p-3.5">
                <img src="{{ media_url($article->image_url) }}" alt="" class="size-11 rounded-xl bg-[#eaf2f0] object-cover">
                <div class="min-w-0 flex-1">
                    <b class="block truncate text-xs">{{ $article->title }}</b>
                    <small class="text-[9px] text-[#7b918e]">{{ fa_digits($article->read_time) }} دقیقه · {{ $article->created_at?->format('Y/m/d') }}</small>
                </div>
                <span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $article->is_published ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">{{ $article->is_published ? 'منتشرشده' : 'پیش‌نویس' }}</span>
            </summary>
            <form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data" class="grid gap-3 border-t border-[#edf2f1] p-4 sm:grid-cols-2">
                @csrf @method('PATCH')
                <input name="title" class="input-field sm:col-span-2" value="{{ $article->title }}" required>
                <textarea name="excerpt" class="input-field min-h-20 sm:col-span-2" required>{{ $article->excerpt }}</textarea>
                <textarea name="body" class="input-field min-h-40 sm:col-span-2">{{ $article->body }}</textarea>
                <div class="space-y-2 rounded-xl border border-[#e0ebe9] p-3">
                    <span class="block text-[11px] font-bold text-[#315b58]">تصویر مقاله</span>
                    <div class="flex items-center gap-2">
                        <img src="{{ media_url($article->image_url) }}" alt="" class="size-12 rounded-lg bg-[#eaf2f0] object-cover">
                        <input type="file" name="image" accept="image/*" class="input-field !py-1.5 text-[10px]">
                    </div>
                    <input name="image_url" dir="ltr" class="input-field" value="{{ $article->image_url }}" placeholder="آدرس تصویر">
                    @if($article->image_url !== '')
                        <label class="flex items-center gap-2 text-[10px] text-red-500"><input type="checkbox" name="remove_image" value="1">حذف تصویر فعلی</label>
                    @endif
                </div>
                <div class="space-y-3">
                    <input name="read_time" type="number" class="input-field" value="{{ $article->read_time }}">
                    <label class="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 py-2.5 text-xs"><input type="checkbox" name="is_published" value="1" {{ $article->is_published ? 'checked' : '' }}>منتشر شود</label>
                </div>
                <div class="flex gap-2 sm:col-span-2"><button class="btn-primary">ذخیره</button></div>
            </form>
            <div class="flex gap-2 px-4 pb-4">
                <a href="{{ route('articles.show', $article) }}" target="_blank" class="rounded-lg border border-[#dde9e7] bg-white px-3 py-2 text-xs font-bold text-brand">مشاهده در سایت</a>
                <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" onsubmit="return confirm('این مقاله حذف شود؟')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg border border-[#dde9e7] bg-white px-3 py-2 text-xs font-bold text-red-500">حذف</button>
                </form>
            </div>
        </details>
    @empty
        <p class="admin-card text-center text-xs text-[#7b918e]">هنوز مقاله‌ای نوشته نشده است.</p>
    @endforelse
</div>
@endsection
