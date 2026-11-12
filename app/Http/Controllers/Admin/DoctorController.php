<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        return view('admin.doctors', [
            'doctors' => Doctor::with('specialty')->latest()->get(),
            'specialties' => Specialty::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDoctor($request);
        $data['image_url'] = $this->resolveImage($request, '');
        $data['slug'] = 'doctor-' . Str::random(8) . '-' . time();
        $data['is_active'] = true;

        Doctor::create($data);

        return back()->with('success', 'پزشک جدید اضافه شد.');
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $data = $this->validateDoctor($request);
        $data['image_url'] = $this->resolveImage($request, (string) $doctor->image_url);

        $doctor->update($data);

        return back()->with('success', 'اطلاعات پزشک به‌روز شد.');
    }

    /** حذف کامل پزشک (اگر نوبتی برایش ثبت نشده باشد) */
    public function forceDestroy(Doctor $doctor): RedirectResponse
    {
        if ($doctor->appointments()->exists()) {
            return back()->with('success', 'این پزشک سابقهٔ نوبت دارد و حذف نمی‌شود؛ فقط غیرفعال شد.');
        }

        delete_uploaded_image($doctor->image_url);
        $doctor->delete();

        return back()->with('success', 'پزشک برای همیشه حذف شد.');
    }

    /**
     * تعیین تصویر پزشک: فایل آپلودی > درخواست حذف > آدرس دستی > تصویر فعلی.
     */
    private function resolveImage(Request $request, string $current): string
    {
        if ($uploaded = store_uploaded_image($request->file('image'), 'doctors')) {
            delete_uploaded_image($current);

            return $uploaded;
        }

        if ($request->boolean('remove_image')) {
            delete_uploaded_image($current);

            return '';
        }

        if ($request->filled('image_url')) {
            return (string) $request->input('image_url');
        }

        return $current;
    }

    /** غیرفعال‌سازی به‌جای حذف فیزیکی برای حفظ سوابق */
    public function destroy(Doctor $doctor): RedirectResponse
    {
        $doctor->update(['is_active' => false]);

        return back()->with('success', 'پزشک از سایت غیرفعال شد.');
    }

    private function validateDoctor(Request $request): array
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'title' => ['required', 'string', 'min:3', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:1000'],
            'visit_price' => ['required', 'integer', 'min:0'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'medical_code' => ['nullable', 'string', 'max:30'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:2000'],
            'remove_image' => ['nullable', 'boolean'],
            'about' => ['nullable', 'string', 'max:3000'],
            'next_available' => ['nullable', 'string', 'max:80'],
            'is_online' => ['boolean'],
        ]);

        // فیلدهای مخصوص آپلود نباید در مدل ذخیره شوند
        unset($data['image'], $data['remove_image']);

        return $data + [
            'image_url' => (string) ($data['image_url'] ?? ''),
            'address' => (string) ($data['address'] ?? ''),
            'medical_code' => (string) ($data['medical_code'] ?? ''),
            'about' => (string) ($data['about'] ?? ''),
            'next_available' => (string) ($data['next_available'] ?? 'امروز'),
            'is_online' => $request->boolean('is_online'),
        ];
    }
}
