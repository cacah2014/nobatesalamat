<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ویرایش کامل محتوای سایت: متن‌ها، آمار، اطلاعات تماس، فوتر و تصاویر.
 * امکان افزودن کلید جدید و حذف کلید هم وجود دارد.
 */
class SiteSettingController extends Controller
{
    public function index(): View
    {
        $settings = SiteSetting::orderBy('group')->orderBy('key')->get();

        return view('admin.settings', [
            'settings' => $settings->groupBy('group'),
            'groups' => ['general', 'homepage', 'footer', 'social', 'contact'],
        ]);
    }

    /** ویرایش زندهٔ محتوای سایت — متن‌ها و تصاویر */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable', 'string', 'max:5000'],
        ]);

        foreach ((array) ($data['settings'] ?? []) as $key => $value) {
            SiteSetting::whereKey($key)->update(['value' => (string) $value]);
        }

        // تصاویر: settings_images[کلید] = فایل
        foreach ((array) $request->file('settings_images', []) as $key => $file) {
            if ($uploaded = store_uploaded_image($file, 'settings')) {
                $setting = SiteSetting::find($key);

                if ($setting) {
                    delete_uploaded_image($setting->value);
                    $setting->update(['value' => $uploaded]);
                }
            }
        }

        // حذف تصویر
        foreach ((array) $request->input('remove_image', []) as $key => $flag) {
            if (! $flag) {
                continue;
            }

            $setting = SiteSetting::find($key);

            if ($setting) {
                delete_uploaded_image($setting->value);
                $setting->update(['value' => '']);
            }
        }

        return back()->with('success', 'تغییرات سایت منتشر شد.');
    }

    /** افزودن کلید تنظیم جدید */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/', 'unique:site_settings,key'],
            'label' => ['required', 'string', 'max:120'],
            'group' => ['required', 'string', 'max:32'],
            'type' => ['required', 'in:text,textarea,image'],
            'value' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ], [
            'key.required' => 'کلید تنظیم را وارد کنید (حروف کوچک انگلیسی و _).',
            'key.regex' => 'کلید فقط می‌تواند شامل حروف کوچک انگلیسی، عدد و _ باشد.',
            'key.unique' => 'این کلید قبلاً وجود دارد.',
            'label.required' => 'برچسب نمایشی را وارد کنید.',
        ]);

        $value = (string) ($data['value'] ?? '');

        if ($uploaded = store_uploaded_image($request->file('image'), 'settings')) {
            $value = $uploaded;
        }

        SiteSetting::create([
            'key' => $data['key'],
            'label' => $data['label'],
            'group' => $data['group'],
            'type' => $data['type'],
            'value' => $value,
        ]);

        return back()->with('success', 'کلید جدید ساخته شد؛ حالا مقدارش را ویرایش کنید.');
    }

    public function destroy(string $key): RedirectResponse
    {
        $setting = SiteSetting::find($key);

        if ($setting) {
            delete_uploaded_image($setting->value);
            $setting->delete();
        }

        return back()->with('success', 'کلید حذف شد.');
    }
}
