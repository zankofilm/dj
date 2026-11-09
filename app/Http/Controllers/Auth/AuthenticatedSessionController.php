<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Sms\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'identity' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $identity = trim($data['identity']);
        if (str_contains($identity, '@')) {
            $credentials = ['email' => mb_strtolower($identity), 'password' => $data['password']];
            $errorField = 'identity';
        } else {
            try {
                $phone = PhoneNumber::normalize($identity);
            } catch (InvalidArgumentException $exception) {
                throw ValidationException::withMessages(['identity' => 'شماره موبایل را همراه کد کشور وارد کنید.']);
            }
            $credentials = ['phone' => $phone, 'password' => $data['password']];
            $errorField = 'identity';
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([$errorField => 'شماره موبایل/ایمیل یا گذرواژه صحیح نیست.']);
        }

        if ($request->user()->status !== 'active' || (isset($credentials['phone']) && !$request->user()->phone_verified_at)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([$errorField => 'دسترسی این حساب در حال حاضر فعال نیست.']);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('account.index'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('storefront.home');
    }
}
