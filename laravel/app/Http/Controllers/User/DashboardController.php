<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $appointments = $user->appointments()->with('doctor.specialty')->latest()->get();

        return view('user.dashboard', [
            'user' => $user,
            'appointments' => $appointments,
            'activeCount' => $appointments->where('status', '!=', 'cancelled')->count(),
            'cancelledCount' => $appointments->where('status', 'cancelled')->count(),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate(['full_name' => ['required', 'string', 'min:3', 'max:120']]);
        $request->user()->update(['full_name' => $data['full_name']]);

        return response()->json(['ok' => true]);
    }
}
