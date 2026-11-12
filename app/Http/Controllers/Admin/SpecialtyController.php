<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * مدیریت حوزه‌های کاری (تخصص‌ها) — افزودن، ویرایش، حذف.
 */
class SpecialtyController extends Controller
{
    public function index(): View
    {
        return view('admin.specialties', [
            'specialties' => Specialty::withCount('doctors')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title']);

        Specialty::create($data);

        return back()->with('success', 'حوزهٔ کاری جدید اضافه شد.');
    }

    public function update(Request $request, Specialty $specialty): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['title'] !== $specialty->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $specialty->id);
        }

        $specialty->update($data);

        return back()->with('success', 'حوزهٔ کاری به‌روز شد.');
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        if ($specialty->doctors()->exists()) {
            return back()->with('success', 'این حوزه پزشک دارد؛ اول پزشکان آن را به حوزهٔ دیگری منتقل کنید.');
        }

        $specialty->delete();

        return back()->with('success', 'حوزهٔ کاری حذف شد.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:100'],
            'icon' => ['nullable', 'string', 'max:16'],
            'color' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'نام حوزهٔ کاری را وارد کنید.',
        ]);

        return [
            'title' => $data['title'],
            'icon' => $data['icon'] ?? '🩺',
            'color' => $data['color'] ?? 'teal',
            'description' => $data['description'] ?? '',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = persian_slug($title, 'specialty');
        $slug = $base;
        $i = 2;

        while (Specialty::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
