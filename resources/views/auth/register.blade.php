<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(),['fa','ku'])?'rtl':'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>ثبت‌نام با شماره موبایل | بازارگاه</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
@include('partials.storefront-header')
<main class="auth-shell"><section class="auth-card">
<a class="auth-back" href="{{ route('storefront.home') }}">← بازگشت به فروشگاه</a>
<div class="auth-mark">ب</div><h1>ثبت‌نام با شماره موبایل</h1><p>شماره موبایل خود را وارد کنید تا کد فعال‌سازی برایتان پیامک شود.</p>
@if(session('otp_sent'))<div class="auth-success">{{ session('otp_sent') }}</div>@endif
@if($errors->any())<div class="auth-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('register.send-code') }}">@csrf
<label>شماره موبایل<input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="30" inputmode="tel" autocomplete="tel" placeholder="09123456789" dir="ltr"></label>
<button type="submit">ارسال کد فعال‌سازی</button>
</form>
@if(session('otp_sent') && session()->filled('registration.pending_phone'))
<div class="auth-divider"></div><h2>تأیید شماره موبایل</h2><p>کد ۶ رقمی پیامک‌شده را وارد کنید. کد تا ۵ دقیقه معتبر است.</p>
<form method="POST" action="{{ route('register.verify-code') }}">@csrf
<label>کد فعال‌سازی<input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" placeholder="۱۲۳۴۵۶" required autocomplete="one-time-code" dir="ltr"></label>
<button type="submit">تأیید و ادامه</button>
</form>
@endif
<div class="auth-switch">قبلاً حساب دارید؟ <a href="{{ route('login') }}">وارد شوید</a></div>
</section></main>
@include('partials.storefront-footer')
<style>
.auth-shell{min-height:58vh;display:grid;place-items:center;padding:35px 16px}.auth-card{width:min(440px,100%);background:#fff;border:1px solid #eee8f7;border-radius:20px;padding:30px;box-shadow:0 18px 55px rgba(54,29,87,.07)}.auth-back{font-size:12px;color:#766a86}.auth-mark{width:48px;height:48px;border-radius:15px;background:#6236ad;color:white;display:grid;place-items:center;font-size:24px;font-weight:bold;margin:22px 0 12px}.auth-card h1{color:#392458;font-size:24px;margin:0 0 8px}.auth-card h2{font-size:17px;color:#392458;margin:0 0 8px}.auth-card>p{font-size:13px;color:#887e94;line-height:1.8}.auth-card form{display:grid;gap:14px;margin-top:18px}.auth-card label{display:grid;gap:7px;font-size:12px;color:#625c70}.auth-card input{width:100%;box-sizing:border-box;padding:12px;border:1px solid #ded6e9;border-radius:9px;font:inherit}.auth-card button{border:0;border-radius:10px;padding:12px;background:#6236ad;color:white;font:inherit;cursor:pointer}.auth-hint{color:#887e94;font-size:11px}.auth-divider{height:1px;background:#eee8f7;margin:22px 0}.auth-switch{text-align:center;margin-top:18px;font-size:12px;color:#82778f}.auth-success{background:#eaf7ef;color:#18794e;padding:10px;border-radius:9px;font-size:12px}.auth-error{background:#fff0ee;color:#b42318;padding:11px;border-radius:9px;font-size:12px;line-height:1.8}.auth-success{background:#edf8f1;color:#176b3c;padding:11px;border-radius:9px;font-size:12px;line-height:1.8}
</style>
</body></html>
