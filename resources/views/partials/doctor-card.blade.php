<article class="card-hover overflow-hidden rounded-[22px] border border-[#e0ecea] bg-white">
    <div class="relative h-48 overflow-hidden bg-[#e9f3f1]">
        <img src="{{ media_url($doctor->image_url) }}" alt="تصویر {{ $doctor->full_name }}" class="h-full w-full object-cover object-[center_25%]" loading="lazy">
        <span class="absolute right-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-black text-[#7a5711] shadow-sm">★ {{ fa_digits($doctor->rating) }}</span>
        @if($doctor->is_online)
            <span class="absolute bottom-3 right-3 rounded-full bg-brand px-2.5 py-1 text-[10px] font-bold text-white">● مشاوره آنلاین</span>
        @endif
    </div>
    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h3 class="font-black">{{ $doctor->full_name }}</h3>
                <p class="mt-1 line-clamp-1 text-xs text-[#68807e]">{{ $doctor->title }}</p>
            </div>
            <span class="shrink-0 rounded-lg bg-[#e9f8f4] px-2 py-1 text-[10px] font-bold text-brand">{{ $doctor->specialty->title }}</span>
        </div>
        <div class="mt-4 flex items-center justify-between text-[11px] text-[#607a78]">
            <span>{{ $doctor->city }}</span>
            <span>{{ fa_digits(number_format($doctor->reviews_count)) }} نظر</span>
        </div>
        <div class="mt-4 rounded-xl bg-[#f7faf9] p-2.5">
            <small class="block text-[9px] text-[#78908e]">اولین نوبت آزاد</small>
            <b class="text-[11px]">{{ $doctor->next_available }}</b>
        </div>
        <div class="mt-4 flex items-end justify-between border-t border-[#edf2f1] pt-3.5">
            <div><small class="block text-[9px] text-[#819694]">هزینه ویزیت</small><b class="text-xs">{{ fa_money($doctor->visit_price) }}</b></div>
            <a href="{{ route('doctors.show', $doctor) }}" class="text-xs font-black text-brand">مشاهده و نوبت ←</a>
        </div>
    </div>
</article>
