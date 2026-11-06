<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\UpdateLog;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'doctors' => Doctor::where('is_active', true)->count(),
                'appointments' => Appointment::count(),
                'revenue' => Appointment::where('status', '!=', 'cancelled')->sum('price'),
            ],
            'recentAppointments' => Appointment::with(['doctor', 'user'])->latest()->limit(5)->get(),
            'lastUpdate' => UpdateLog::latest('started_at')->first(),
        ]);
    }
}
