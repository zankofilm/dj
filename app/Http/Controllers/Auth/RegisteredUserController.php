<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SmsVerificationCode;
use App\Models\User;
use App\Services\Sms\PhoneNumber;
use App\Services\Sms\SmsOtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use InvalidArgumentException;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function sendCode(Request $request, SmsOtpService $otp)
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30']]);
        try {
            $phone = PhoneNumber::normalize($data['phone']);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['phone' => $exception->getMessage()]);
        }

        // Keep the same response and form for registered and unregistered numbers.
        $alreadyRegistered = User::where('phone', $phone)->exists();
        if (!$alreadyRegistered) {
            $otp->send($phone, 'registration');
        }
        $request->session()->put('registration.pending_phone', $phone);
        $request->session()->put('registration.blocked_existing', $alreadyRegistered);

        return redirect()->route('register')->with('otp_sent', 'اگر شماره برای ثبت‌نام واجد شرایط باشد، کد تأیید ارسال می‌شود.');
    }

    public function showVerification(Request $request): View
    {
        $phone = $request->session()->get('registration.pending_phone');
        abort_unless($phone, 403);
        return view('auth.verify-mobile', compact('phone'));
    }

    public function verifyCode(Request $request, SmsOtpService $otp)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $phone = $request->session()->get('registration.pending_phone');

        if (!$phone || $request->session()->boolean('registration.blocked_existing') || !$otp->verify($phone, $data['code'], 'registration')) {
            throw ValidationException::withMessages(['code' => 'کد تأیید نامعتبر است یا اعتبار آن پایان یافته است.']);
        }

        $request->session()->put('registration.verified_phone', $phone);
        $request->session()->forget('registration.pending_phone');

        return redirect()->route('register.complete');
    }

    public function complete(Request $request): View
    {
        abort_unless($request->session()->filled('registration.verified_phone'), 403);
        return view('auth.register-complete');
    }

    public function resendCode(Request $request, SmsOtpService $otp)
    {
        $phone = $request->session()->get('registration.pending_phone');
        abort_unless($phone, 403);
        if (!$request->session()->boolean('registration.blocked_existing')) {
            $otp->send($phone, 'registration');
        }
        return back()->with('otp_sent', 'اگر شماره برای ثبت‌نام واجد شرایط باشد، کد جدید ارسال می‌شود.');
    }

    public function store(Request $request)
    {
        $phone = $request->session()->get('registration.verified_phone');
        abort_unless($phone, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ], [
            'password.confirmed' => 'تکرار گذرواژه مطابقت ندارد.',
            'password.min' => 'گذرواژه باید حداقل ۱۰ کاراکتر باشد.',
        ]);

        if (User::where('phone', $phone)->exists()) {
            $request->session()->forget('registration');
            throw ValidationException::withMessages(['phone' => 'این شماره موبایل قبلاً ثبت شده است.']);
        }

        $user = DB::transaction(function () use ($phone, $data) {
            return User::create([
                'name' => $data['name'],
                'email' => null,
                'phone' => $phone,
                'phone_verified_at' => now(),
                'password' => Hash::make($data['password']),
                'status' => 'active',
            ]);
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->forget('registration');
        $request->session()->regenerate();

        return redirect()->route('account.index')->with('status', 'شماره موبایل تأیید شد و حساب کاربری شما ایجاد شد.');
    }
}
