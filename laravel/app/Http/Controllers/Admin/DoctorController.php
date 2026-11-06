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
        Doctor::create($this->validateDoctor($request) + [
            'slug' => 'doctor-' . Str::random(8) . '-' . time(),
            'is_active' => true,
        ]);

        return back()->with('success', 'پزشک جدید اضافه شد.');
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $doctor->update($this->validateDoctor($request));

        return back()->with('success', 'اطلاعات پزشک به‌روز شد.');
    }

    /** غیرفعال‌سازی به‌جای حذف فیزیکی برای حفظ سوابق */
    public function destroy(Doctor $doctor): RedirectResponse
    {
        $doctor->update(['is_active' => false]);

        return back()->with('success', 'پزشک از سایت غیرفعال شد.');
    }

    private function validateDoctor(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'title' => ['required', 'string', 'min:3', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:1000'],
            'visit_price' => ['required', 'integer', 'min:0'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'medical_code' => ['nullable', 'string', 'max:30'],
            'image_url' => ['required', 'url', 'max:2000'],
            'about' => ['nullable', 'string', 'max:3000'],
            'next_available' => ['nullable', 'string', 'max:80'],
            'is_online' => ['boolean'],
        ]) + ['address' => '', 'medical_code' => '', 'about' => '', 'next_available' => 'امروز', 'is_online' => false];
    }
}
