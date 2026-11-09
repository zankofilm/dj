<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(),['fa','ku'])?'rtl':'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>تأیید شماره موبایل | بازارگاه</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
@include('partials.storefront-header')
<main class="auth-shell"><section class="auth-card">
<a class="auth-back" href="{{ route('register') }}">← بازگشت به ثبت‌نام</a>
<div class="auth-mark">✓</div><h1>تأیید شماره موبایل</h1><p>کد ۶ رقمی ارسال‌شده به شماره زیر را وارد کنید:</p>
<p class="verify-mobile" dir="ltr">{{ $phone }}</p>
@if(session('otp_sent'))<div class="auth-success">{{ session('otp_sent') }}</div>@endif
@if($errors->any())<div class="auth-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('register.verify-code') }}">@csrf
<label>کد فعال‌سازی<input type="text" name="code" required minlength="6" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" placeholder="------" dir="ltr" autofocus></label>
<button type="submit">تأیید و فعال‌سازی حساب</button>
</form>
<form method="POST" action="{{ route('register.resend-code') }}" class="resend-form">@csrf<button type="submit" class="resend-button">ارسال مجدد کد</button></form>
<p class="verify-hint">کد تا ۵ دقیقه معتبر است. برای دریافت مجدد کد باید ۶۰ ثانیه صبر کنید.</p>
</section></main>
@include('partials.storefront-footer')
<style>
.auth-shell{min-height:58vh;display:grid;place-items:center;padding:35px 16px}.auth-card{width:min(440px,100%);background:white;border:1px solid #eee8f7;border-radius:20px;padding:30px;box-shadow:0 18px 55px rgba(54,29,87,.07)}.auth-back{font-size:12px;color:#766a86}.auth-mark{width:48px;height:48px;border-radius:15px;background:#6236ad;color:white;display:grid;place-items:center;font-size:24px;font-weight:bold;margin:22px 0 12px}.auth-card h1{color:#392458;font-size:24px;margin:0 0 8px}.auth-card>p{font-size:13px;color:#887e94;line-height:1.8}.verify-mobile{text-align:center;font-weight:800;color:#4b239c;font-size:17px}.auth-card form{display:grid;gap:14px;margin-top:20px}.auth-card label{display:grid;gap:7px;font-size:12px;color:#625c70}.auth-card input{width:100%;box-sizing:border-box;padding:13px;border:1px solid #ded6e9;border-radius:9px;font:inherit;text-align:center;letter-spacing:7px;font-size:20px}.auth-card button{border:0;border-radius:10px;padding:12px;background:#6236ad;color:white;font:inherit;cursor:pointer}.auth-card .resend-form{margin-top:9px}.auth-card .resend-button{background:#f4effb;color:#6236ad;width:100%}.auth-error{background:#fff0ee;color:#b42318;padding:11px;border-radius:9px;font-size:12px;line-height:1.8}.auth-success{background:#eaf7ef;color:#18794e;padding:10px;border-radius:9px;font-size:12px}.verify-hint{font-size:11px!important;text-align:center;margin-top:16px}
</style>
</body></html>
