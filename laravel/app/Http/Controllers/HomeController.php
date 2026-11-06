<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Doctor;
use App\Models\SiteSetting;
use App\Models\Specialty;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'settings' => SiteSetting::all_cached(),
            'specialties' => Specialty::where('is_active', true)->orderBy('sort_order')->get(),
            'featuredDoctors' => Doctor::with('specialty')
                ->where('is_active', true)
                ->whereHas('specialty', fn ($q) => $q->where('is_active', true))
                ->latest()
                ->limit(4)
                ->get(),
            'articles' => Article::where('is_published', true)->latest()->limit(3)->get(),
        ]);
    }
}
