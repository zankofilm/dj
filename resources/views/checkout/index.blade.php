<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">@vite(['resources/css/app.css','resources/js/app.js'])<title>تکمیل سفارش</title></head>
<body>@include('partials.storefront-header')<main class="form-shell">
<h1>تکمیل سفارش</h1>
@if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif
<p>تعداد ردیف‌های سبد: {{ $cart->items->count() }}</p>
<form method="POST" action="{{ route('checkout.place') }}">
@csrf
<label>نام تحویل‌گیرنده</label><input name="recipient_name" required maxlength="150">
<label>شماره تماس</label><input name="phone" required maxlength="40">
<label>نشانی کامل</label><textarea name="address" required maxlength="2000"></textarea>
<label>کد پستی</label><input name="postal_code" maxlength="40">
<button type="submit">ثبت سفارش و رزرو موجودی</button>
</form>
<p class="notice">در این نسخه پرداخت آنلاین هنوز فعال نیست؛ سفارش پس از ثبت در وضعیت «در انتظار پرداخت» می‌ماند.</p>
</main>@include('partials.storefront-footer')</body></html>
