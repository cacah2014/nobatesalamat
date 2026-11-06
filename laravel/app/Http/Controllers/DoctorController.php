<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Doctor::with('specialty')
            ->where('is_active', true)
            ->whereHas('specialty', fn ($q) => $q->where('is_active', true));

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhereHas('specialty', fn ($s) => $s->where('title', 'like', "%{$q}%"));
            });
        }

        if ($city = $request->query('city')) {
            $query->where('city', $city);
        }

        if ($specialty = $request->query('specialty')) {
            $query->whereHas('specialty', fn ($s) => $s->where('slug', $specialty));
        }

        if ($request->query('visit') === 'online') {
            $query->where('is_online', true);
        }

        return view('doctors.index', [
            'doctors' => $query->orderBy('rating', 'desc')->paginate(12)->withQueryString(),
            'specialties' => Specialty::where('is_active', true)->orderBy('sort_order')->get(),
            'filters' => [
                'q' => $q ?? '',
                'city' => $city ?? '',
                'specialty' => $specialty ?? '',
                'visit' => (string) $request->query('visit', ''),
            ],
        ]);
    }

    public function show(Doctor $doctor): View
    {
        abort_unless($doctor->is_active, 404);
        $doctor->load('specialty');

        return view('doctors.show', [
            'doctor' => $doctor,
            'similarDoctors' => Doctor::with('specialty')
                ->where('is_active', true)
                ->whereKeyNot($doctor->id)
                ->limit(3)
                ->get(),
        ]);
    }
}
