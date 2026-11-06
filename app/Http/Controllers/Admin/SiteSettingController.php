<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings', [
            'settings' => SiteSetting::orderBy('group')->orderBy('key')->get()->groupBy('group'),
        ]);
    }

    /** ویرایش زندهٔ محتوای سایت — فقط کلیدهای موجود به‌روزرسانی می‌شوند */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['settings' => ['required', 'array'], 'settings.*' => ['string', 'max:3000']]);

        foreach ($data['settings'] as $key => $value) {
            SiteSetting::whereKey($key)->update(['value' => $value]);
        }

        return back()->with('success', 'تغییرات سایت منتشر شد.');
    }
}
