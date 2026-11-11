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
use Illuminate\Support\Facades\Validator;
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
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
        ], [
            'phone.required' => 'شماره موبایل را وارد کنید.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first('phone'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = normalize_phone((string) $request->input('phone'));

        if (! preg_match('/^09\d{9}$/', $phone)) {
            return response()->json(['error' => 'شماره موبایل را به‌صورت ۰۹xxxxxxxxx وارد کنید.'], 422);
        }

        try {
            $result = $this->otp->send($phone);
        } catch (SmsException $e) {
            // پیام دقیق پنل پیامک (توکن نامعتبر، نبود اعتبار، خط غیرفعال …)
            return response()->json(['error' => $e->getMessage(), 'code' => $e->errorCode()], 500);
        } catch (\Throwable $e) {
            report($e);

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
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'min:4', 'max:8'],
        ], [
            'code.required' => 'کد تأیید را وارد کنید.',
            'code.min' => 'کد تأیید ۵ رقمی است.',
            'code.max' => 'کد تأیید ۵ رقمی است.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first('code') ?: $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = normalize_phone((string) $request->input('phone'));
        $result = $this->otp->verifyStatus($phone, (string) $request->input('code'));

        switch ($result['status']) {
            case OtpService::STATUS_OK:
                break;

            case OtpService::STATUS_MISSING:
                return response()->json([
                    'error' => 'کدی برای این شماره ثبت نشده است. دوباره «دریافت کد ورود» را بزنید.',
                ], 422);

            case OtpService::STATUS_EXPIRED:
                return response()->json([
                    'error' => 'کد تأیید منقضی شده است. کد جدید بگیرید.',
                ], 422);

            case OtpService::STATUS_TOO_MANY:
                return response()->json([
                    'error' => 'تعداد تلاش‌های ناموفق زیاد بود. کد جدید بگیرید.',
                ], 429);

            default:
                $left = (int) ($result['attempts_left'] ?? 0);

                return response()->json([
                    'error' => $left > 0
                        ? 'کد واردشده اشتباه است. ' . fa_digits((string) $left) . ' تلاش دیگر باقی مانده.'
                        : 'کد واردشده اشتباه است. کد جدید بگیرید.',
                    'attempts_left' => $left,
                ], 422);
        }

        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['status' => 'active', 'permissions' => []]
        );

        // اگر حساب غیرفعال شده باشد
        if (! $user->isActive()) {
            return response()->json(['error' => 'حساب شما غیرفعال است. برای پیگیری با پشتیبانی تماس بگیرید.'], 403);
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
