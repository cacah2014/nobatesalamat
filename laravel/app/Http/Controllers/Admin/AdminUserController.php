<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * مدیریت مدیران و سطح دسترسی — فقط مدیر ارشد.
 */
class AdminUserController extends Controller
{
    public function __construct()
    {
        // فقط مدیر ارشد به مدیریت مدیران دسترسی دارد
        abort_unless(request()->user()?->isSuperAdmin(), 403, 'فقط مدیر ارشد به این بخش دسترسی دارد.');
    }

    public function index(): View
    {
        return view('admin.admins', [
            'admins' => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER])->get(),
            'permissionLabels' => [
                'content' => 'ویرایش محتوای سایت',
                'doctors' => 'مدیریت پزشکان',
                'appointments' => 'مدیریت نوبت‌ها',
                'updates' => 'به‌روزرسانی پروژه',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = normalize_phone($data['phone']);

        if (! preg_match('/^09\d{9}$/', $phone)) {
            return back()->withErrors(['phone' => 'شماره موبایل معتبر نیست.']);
        }

        $user = User::firstOrNew(['phone' => $phone]);

        if ($user->exists && $user->isAdmin()) {
            return back()->withErrors(['phone' => 'این شماره از قبل مدیر است.']);
        }

        $user->fill([
            'full_name' => $data['full_name'],
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'permissions' => [],
        ])->save();

        return back()->with('success', 'مدیر جدید اضافه شد؛ حالا دسترسی‌های او را تعیین کنید.');
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin(), 422, 'دسترسی مدیر ارشد قابل تغییر نیست.');

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', User::PERMISSIONS)],
        ]);

        $user->update(['permissions' => array_values($data['permissions'] ?? [])]);

        return back()->with('success', "دسترسی‌های {$user->full_name} ذخیره شد.");
    }

    /** حذف نقش مدیر (حساب کاربری باقی می‌ماند) */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin() || $user->id === $request->user()->id, 422, 'امکان حذف این مدیر وجود ندارد.');
        abort_unless($user->role === User::ROLE_ADMIN, 404);

        $user->update(['role' => User::ROLE_USER, 'permissions' => []]);

        return back()->with('success', 'دسترسی مدیر حذف شد.');
    }
}
