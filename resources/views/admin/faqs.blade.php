@extends('admin.layout')
@section('title', 'سؤالات متداول')

@section('content')
<details class="admin-card mb-5 border-[#cfe4e0]" open>
    <summary class="cursor-pointer text-sm font-black">+ افزودن سؤال جدید</summary>
    <form action="{{ route('admin.faqs.store') }}" method="POST" class="mt-5 grid gap-3">
        @csrf
        <input name="question" class="input-field" placeholder="متن سؤال" required>
        <textarea name="answer" class="input-field min-h-24" placeholder="متن پاسخ" required></textarea>
        <div class="grid gap-3 sm:grid-cols-2">
            <input name="sort_order" type="number" class="input-field" placeholder="ترتیب نمایش (خالی = آخر)">
            <label class="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 text-xs"><input type="checkbox" name="is_active" value="1" checked>نمایش در سایت</label>
        </div>
        <div><button class="btn-primary">ثبت سؤال</button></div>
    </form>
</details>

<div class="space-y-3">
    @forelse($faqs as $faq)
        <details class="admin-card !p-0">
            <summary class="flex cursor-pointer list-none items-center gap-3 p-3.5">
                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-[#e9f8f4] text-[10px] font-black text-brand">{{ fa_digits($faq->sort_order) }}</span>
                <b class="min-w-0 flex-1 truncate text-xs">{{ $faq->question }}</b>
                <span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $faq->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">{{ $faq->is_active ? 'فعال' : 'مخفی' }}</span>
            </summary>
            <form action="{{ route('admin.faqs.update', $faq) }}" method="POST" class="grid gap-3 border-t border-[#edf2f1] p-4">
                @csrf @method('PATCH')
                <input name="question" class="input-field" value="{{ $faq->question }}" required>
                <textarea name="answer" class="input-field min-h-24" required>{{ $faq->answer }}</textarea>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="sort_order" type="number" class="input-field" value="{{ $faq->sort_order }}">
                    <label class="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 text-xs"><input type="checkbox" name="is_active" value="1" {{ $faq->is_active ? 'checked' : '' }}>نمایش در سایت</label>
                </div>
                <div class="flex gap-2"><button class="btn-primary">ذخیره</button></div>
            </form>
            <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="px-4 pb-4" onsubmit="return confirm('این سؤال حذف شود؟')">
                @csrf @method('DELETE')
                <button class="rounded-lg border border-[#dde9e7] bg-white px-3 py-2 text-xs font-bold text-red-500">حذف</button>
            </form>
        </details>
    @empty
        <p class="admin-card text-center text-xs text-[#7b918e]">هنوز سؤالی ثبت نشده است. با فرم بالا اولین سؤال را بسازید.</p>
    @endforelse
</div>
@endsection
