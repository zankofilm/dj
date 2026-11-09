<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(),['fa','ku'])?'rtl':'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>تکمیل ثبت‌نام | بازارگاه</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
@include('partials.storefront-header')
<main class="auth-shell"><section class="auth-card">
<a class="auth-back" href="{{ route('register.verify') }}">← بازگشت</a>
<div class="auth-mark">✓</div><h1>شماره موبایل تأیید شد</h1>
<p>حالا نام و گذرواژه خود را وارد کنید تا حساب بازارگاه ساخته شود.</p>
@if($errors->any())<div class="auth-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('register.store') }}">@csrf
<label>نام و نام خانوادگی<input name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name"></label>
<label>گذرواژه<input type="password" name="password" required minlength="10" autocomplete="new-password"><small>حداقل ۱۰ کاراکتر، شامل حروف و عدد</small></label>
<label>تکرار گذرواژه<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
<button type="submit">ایجاد حساب کاربری</button>
</form>
</section></main>
@include('partials.storefront-footer')
<style>
.auth-shell{min-height:58vh;display:grid;place-items:center;padding:35px 16px}.auth-card{width:min(440px,100%);background:#fff;border:1px solid #eee8f7;border-radius:20px;padding:30px;box-shadow:0 18px 55px rgba(54,29,87,.07)}.auth-back{font-size:12px;color:#766a86}.auth-mark{width:48px;height:48px;border-radius:15px;background:#16804b;color:white;display:grid;place-items:center;font-size:24px;font-weight:bold;margin:22px 0 12px}.auth-card h1{color:#392458;font-size:24px;margin:0 0 8px}.auth-card>p{font-size:13px;color:#887e94;line-height:1.8}.auth-card form{display:grid;gap:14px;margin-top:20px}.auth-card label{display:grid;gap:7px;font-size:12px;color:#625c70}.auth-card input{width:100%;box-sizing:border-box;padding:12px;border:1px solid #ded6e9;border-radius:9px;font:inherit}.auth-card label small{font-size:11px;color:#887e94}.auth-card button{border:0;border-radius:10px;padding:12px;background:#6236ad;color:white;font:inherit;cursor:pointer}.auth-error{background:#fff0ee;color:#b42318;padding:11px;border-radius:9px;font-size:12px;line-height:1.8}
</style>
</body></html>
