<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'appointment_date' => ['required', 'string', 'max:30'],
            'appointment_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'visit_type' => ['required', 'in:in_person,online'],
            'patient_name' => ['required', 'string', 'min:3', 'max:120'],
            'patient_phone' => ['required', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $doctor = Doctor::where('is_active', true)->findOrFail($data['doctor_id']);

        if ($data['visit_type'] === 'online' && ! $doctor->is_online) {
            return response()->json(['error' => 'این پزشک مشاوره آنلاین ندارد.'], 422);
        }

        $phone = normalize_phone($data['patient_phone']);

        if (! preg_match('/^09\d{9}$/', $phone)) {
            return response()->json(['error' => 'شماره موبایل بیمار معتبر نیست.'], 422);
        }

        $duplicate = Appointment::where('user_id', $request->user()->id)
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->exists();

        if ($duplicate) {
            return response()->json(['error' => 'این نوبت قبلاً برای شما ثبت شده است.'], 409);
        }

        $appointment = Appointment::create([
            'user_id' => $request->user()->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'visit_type' => $data['visit_type'],
            'status' => Appointment::STATUS_CONFIRMED,
            'price' => $doctor->visit_price,
            'tracking_code' => 'NS' . substr((string) now()->getTimestampMs(), -6) . random_int(100, 999),
            'patient_name' => $data['patient_name'],
            'patient_phone' => $phone,
            'note' => $data['note'] ?? '',
        ]);

        return response()->json(['ok' => true, 'trackingCode' => $appointment->tracking_code]);
    }

    /** لغو نوبت فقط توسط مالک آن */
    public function destroy(Request $request, Appointment $appointment): JsonResponse
    {
        abort_unless($appointment->user_id === $request->user()->id, 403);

        if ($appointment->status !== Appointment::STATUS_CANCELLED) {
            $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        }

        return response()->json(['ok' => true]);
    }
}
