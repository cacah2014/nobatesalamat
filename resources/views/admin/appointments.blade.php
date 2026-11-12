@extends('admin.layout')
@section('title', 'مدیریت نوبت‌ها')

@section('content')
<div class="admin-card overflow-x-auto">
    <table class="w-full min-w-[640px] text-right text-[11px]">
        <thead>
            <tr class="border-b border-[#e7efed] text-[9px] text-[#7b918e]">
                <th class="pb-3 font-bold">بیمار</th><th class="pb-3 font-bold">پزشک</th><th class="pb-3 font-bold">زمان</th><th class="pb-3 font-bold">مبلغ</th><th class="pb-3 font-bold">وضعیت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $appointment)
                <tr class="border-b border-[#f0f4f3]">
                    <td class="py-3"><b class="block text-[#315b58]">{{ $appointment->patient_name }}</b><span dir="ltr" class="text-[#829592]">{{ $appointment->user?->phone ?? '—' }}</span></td>
                    <td class="py-3">{{ $appointment->doctor?->full_name ?? '—' }}</td>
                    <td class="py-3 text-[#607976]">{{ $appointment->appointment_date }}<br><span dir="ltr">{{ fa_digits($appointment->appointment_time) }}</span></td>
                    <td class="py-3">{{ fa_money($appointment->price) }}</td>
                    <td class="py-3">
                        <form action="{{ route('admin.appointments.update', $appointment) }}" method="POST">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-[#dde9e7] bg-white px-2 py-1.5 text-[9px] font-bold outline-none">
                                @foreach(['confirmed' => 'تأییدشده', 'completed' => 'انجام‌شده', 'cancelled' => 'لغوشده'] as $value => $label)
                                    <option value="{{ $value }}" {{ $appointment->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-xs text-[#829592]">هنوز نوبتی ثبت نشده است.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4" dir="ltr">{{ $appointments->links() }}</div>
</div>
@endsection
