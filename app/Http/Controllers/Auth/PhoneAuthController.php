<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\SmsException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PhoneAuthController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(Auth::user()->isAdmin() ? route('admin.dashboard') : route('panel'));
        }

        return view('auth.login');
    }

    /** مرحلهٔ ۱: درخواست کد تأیید */
    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:20']]);
        $phone = normalize_phone($data['phone']);

        if (! preg_match('/^09\d{9}$/', $phone)) {
            return response()->json(['error' => 'شماره موبایل را به‌صورت ۰۹xxxxxxxxx وارد کنید.'], 422);
        }

        try {
            $result = $this->otp->send($phone);
        } catch (SmsException $e) {
            // پیام دقیق پنل پیامک (توکن نامعتبر، نبود اعتبار، خط غیرفعال …)
            return response()->json(['error' => $e->getMessage(), 'code' => $e->errorCode()], 500);
        } catch (\Throwable) {
            return response()->json(['error' => 'ارسال کد با خطا روبه‌رو شد. دوباره تلاش کنید.'], 500);
        }

        if (! $result['ok']) {
            return response()->json(['error' => $result['message']], 429);
        }

        return response()->json(array_filter([
            'ok' => true,
            'message' => $result['message'],
            'devCode' => $result['dev_code'],
        ]));
    }

    /** مرحلهٔ ۲: تأیید کد و ورود/ساخت حساب */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'min:4', 'max:8'],
        ]);

        $phone = normalize_phone($data['phone']);

        if (! $this->otp->verify($phone, $data['code'])) {
            return response()->json(['error' => 'کد واردشده اشتباه یا منقضی شده است.'], 422);
        }

        $user = User::firstOrCreate(['phone' => $phone], ['permissions' => []]);

        // اگر حساب غیرفعال شده باشد
        if ($user->status !== 'active') {
            return response()->json(['error' => 'حساب شما غیرفعال است.'], 403);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
            'redirectTo' => $user->isAdmin() ? route('admin.dashboard') : route('panel'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
