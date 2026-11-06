@extends('layouts.app')

@section('title', $doctor->full_name . ' | رزرو نوبت آنلاین | نوبت سلامت')
@section('meta_description', $doctor->title . ' در ' . $doctor->city . '؛ امتیاز ' . fa_digits($doctor->rating) . ' از ' . fa_digits($doctor->reviews_count) . ' نظر بیماران؛ رزرو نوبت در نوبت سلامت.')
@section('canonical', route('doctors.show', $doctor))
@section('og_image', $doctor->image_url)
@section('og_type', 'profile')

@section('content')
<section class="mx-auto grid w-full max-w-[1180px] items-start gap-7 px-4 py-8 lg:grid-cols-[1fr_370px]">
    <div class="space-y-5">
        <div class="rounded-[26px] border border-[#e0ebe9] bg-white p-5 sm:p-7">
            <div class="flex flex-col gap-6 sm:flex-row">
                <div class="relative mx-auto h-48 w-40 shrink-0 overflow-hidden rounded-[22px] bg-[#dcefeb] sm:mx-0">
                    <img src="{{ $doctor->image_url }}" alt="{{ $doctor->full_name }}" class="h-full w-full object-cover object-top">
                </div>
                <div class="flex-1">
                    <span class="rounded-full bg-[#e9f8f4] px-2.5 py-1 text-[10px] font-black text-brand">{{ $doctor->specialty->title }}</span>
                    <h1 class="mt-3 text-2xl font-black">{{ $doctor->full_name }}</h1>
                    <p class="mt-2 text-sm text-[#607976]">{{ $doctor->title }}</p>
                    <div class="mt-4 flex flex-wrap gap-4 text-xs text-[#607976]">
                        <span>★ <b>{{ fa_digits($doctor->rating) }}</b> از {{ fa_digits($doctor->reviews_count) }} نظر</span>
                        <span>📍 {{ $doctor->city }}</span>
                        <span>{{ fa_digits($doctor->experience_years) }} سال تجربه</span>
                    </div>
                    <span class="mt-4 inline-block rounded-xl bg-[#f7faf9] px-3 py-2 text-[10px] font-bold">شماره نظام پزشکی: {{ $doctor->medical_code }}</span>
                </div>
            </div>
        </div>
        <div class="rounded-[24px] border border-[#e0ebe9] bg-white p-6">
            <h2 class="font-black">درباره پزشک</h2>
            <p class="mt-4 text-sm leading-8 text-[#607976]">{{ $doctor->about }}</p>
        </div>
        <div class="rounded-[24px] border border-[#e0ebe9] bg-white p-6">
            <h2 class="font-black">آدرس مطب</h2>
            <p class="mt-3 rounded-2xl bg-[#f5f9f8] p-4 text-xs leading-6 text-[#526f6c]">{{ $doctor->address }}</p>
        </div>

        @if($similarDoctors->count())
            <div>
                <h2 class="mb-5 text-xl font-black">پزشکان پیشنهادی دیگر</h2>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($similarDoctors as $doctor)
                        @include('partials.doctor-card')
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside class="sticky top-24 rounded-[24px] border border-[#dceae7] bg-white p-5 shadow-xl" id="booking">
        <h2 class="mb-4 font-black">انتخاب زمان نوبت</h2>
        @auth
            <form id="booking-form" class="space-y-3" data-doctor="{{ $doctor->id }}">
                @csrf
                <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
                @php($dates = collect(range(0, 4))->map(fn ($i) => now()->addDays($i)))
                <div class="grid grid-cols-5 gap-1.5">
                    @foreach($dates as $date)
                        @php($value = fa_digits($date->format('Y/m/d')))
                        <label class="cursor-pointer rounded-xl border border-[#e0ebe9] py-2 text-center text-[10px] font-bold has-[:checked]:border-brand has-[:checked]:bg-brand has-[:checked]:text-white">
                            <input type="radio" name="appointment_date" value="{{ $value }}" class="hidden" {{ $loop->first ? 'checked' : '' }}>
                            {{ $loop->first ? 'امروز' : fa_digits($date->format('d')) }}
                        </label>
                    @endforeach
                </div>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(['16:00', '17:15', '18:30', '19:45'] as $time)
                        <label class="cursor-pointer rounded-xl border border-[#e0ebe9] py-2 text-center text-xs font-bold has-[:checked]:border-coral has-[:checked]:bg-[#fff0ec] has-[:checked]:text-[#d85843]">
                            <input type="radio" name="appointment_time" value="{{ $time }}" class="hidden" {{ $time === '18:30' ? 'checked' : '' }}>
                            {{ fa_digits($time) }}
                        </label>
                    @endforeach
                </div>
                @if($doctor->is_online)
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer rounded-xl border border-[#e0ebe9] p-2.5 text-center text-xs font-bold has-[:checked]:border-brand has-[:checked]:bg-[#e9f8f4] has-[:checked]:text-brand"><input type="radio" name="visit_type" value="in_person" class="hidden" checked>ویزیت حضوری</label>
                        <label class="cursor-pointer rounded-xl border border-[#e0ebe9] p-2.5 text-center text-xs font-bold has-[:checked]:border-brand has-[:checked]:bg-[#e9f8f4] has-[:checked]:text-brand"><input type="radio" name="visit_type" value="online" class="hidden">مشاوره آنلاین</label>
                    </div>
                @else
                    <input type="hidden" name="visit_type" value="in_person">
                @endif
                <input name="patient_name" class="input-field text-xs" placeholder="نام و نام خانوادگی بیمار" required>
                <input name="patient_phone" dir="ltr" class="input-field text-xs" value="{{ auth()->user()->phone }}" placeholder="09121234567" required>
                <textarea name="note" class="input-field min-h-16 resize-none text-xs" placeholder="توضیح برای پزشک (اختیاری)"></textarea>
                <div class="flex items-center justify-between border-t border-[#edf2f1] pt-4">
                    <div><small class="block text-[9px] text-[#7d918e]">مبلغ قابل پرداخت</small><b class="text-sm">{{ fa_money($doctor->visit_price) }}</b></div>
                    <button class="btn-primary !px-5 !py-2.5 text-xs" type="submit">ثبت نهایی نوبت</button>
                </div>
                <p id="booking-message" class="hidden rounded-xl px-3 py-2 text-xs font-bold"></p>
            </form>
        @else
            <p class="rounded-xl bg-[#fff8e9] p-3 text-xs leading-6 text-[#765b23]">برای ثبت نوبت، ابتدا با شماره موبایل وارد شوید.</p>
            <a href="{{ route('login') }}" class="btn-primary mt-4 w-full !py-2.5 text-xs">ورود و ادامه</a>
        @endauth
    </aside>
</section>

{{-- JSON-LD Physician + Breadcrumb --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org', '@type' => 'Physician',
    'name' => $doctor->full_name, 'image' => $doctor->image_url, 'url' => route('doctors.show', $doctor),
    'medicalSpecialty' => $doctor->specialty->title, 'description' => $doctor->about,
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => $doctor->city, 'streetAddress' => $doctor->address, 'addressCountry' => 'IR'],
    'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => $doctor->rating, 'reviewCount' => $doctor->reviews_count, 'bestRating' => 5, 'worstRating' => 1],
], JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@auth
@push('scripts')
<script>
document.getElementById('booking-form')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const message = document.getElementById('booking-message');
    message.className = 'hidden';
    const body = Object.fromEntries(new FormData(this).entries());
    const response = await fetch('{{ route('appointments.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(body),
    });
    const data = await response.json();
    message.className = 'rounded-xl px-3 py-2 text-xs font-bold ' + (response.ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600');
    if (response.ok) {
        message.textContent = 'نوبت شما ثبت شد. کد رهگیری: ' + data.trackingCode;
    } else if (response.status === 422 && data.errors) {
        message.textContent = Object.values(data.errors)[0][0];
    } else {
        message.textContent = data.error || 'ثبت نوبت انجام نشد.';
    }
});
</script>
@endpush
@endauth
