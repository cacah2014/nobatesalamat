<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(): View
    {
        return view('admin.appointments', [
            'appointments' => Appointment::with(['doctor', 'user'])->latest()->paginate(20),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:confirmed,completed,cancelled']]);
        $appointment->update(['status' => $data['status']]);

        return back()->with('success', 'وضعیت نوبت تغییر کرد.');
    }
}
