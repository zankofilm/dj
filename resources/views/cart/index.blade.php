<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">@vite(['resources/css/app.css','resources/js/app.js'])<title>سبد خرید</title></head>
<body>@include('partials.storefront-header')<main class="form-shell">
<h1>سبد خرید</h1>
@if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
@if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif
@forelse($cart->items as $item)
<section class="seller-row">
    <h2>{{ data_get($item->variant->product->translations, 'fa.title', $item->variant->product->slug) }}</h2>
    <p>شناسه کالا: {{ $item->variant->sku }} | تعداد: {{ $item->quantity }}</p>
    <form method="POST" action="{{ route('cart.items.remove', $item) }}">@csrf @method('DELETE')<button>حذف</button></form>
</section>
@empty
<p>سبد خرید شما خالی است.</p>
@endforelse
@if($cart->items->isNotEmpty())<a href="{{ route('checkout.show') }}">ادامه و ثبت سفارش</a>@endif
</main>@include('partials.storefront-footer')</body></html>
