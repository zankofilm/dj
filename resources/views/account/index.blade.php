<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(),['fa','ku'])?'rtl':'ltr' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>حساب کاربری | بازارگاه</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
@include('partials.storefront-header')
<main class="account-shell">
    <div class="account-heading"><div><span class="account-eyebrow">MY MARKETPLACE</span><h1>حساب کاربری</h1><p>سلام {{ auth()->user()->name }}؛ از این بخش می‌توانید سفارش‌های خود را پیگیری کنید.</p></div><a class="account-back" href="{{ route('storefront.home') }}">بازگشت به فروشگاه</a></div>
    <section class="account-stats" aria-label="خلاصه سفارش‌ها">
        <article><span>کل سفارش‌ها</span><strong>{{ number_format($stats['total']) }}</strong></article>
        <article><span>در انتظار پرداخت</span><strong>{{ number_format($stats['pending']) }}</strong></article>
        <article><span>تکمیل‌شده</span><strong>{{ number_format($stats['completed']) }}</strong></article>
    </section>
    <section class="account-orders">
        <div class="account-section-head"><div><h2>سفارش‌های من</h2><p>آخرین سفارش‌های ثبت‌شده شما</p></div></div>
        @if($orders->isEmpty())
            <div class="account-empty"><span>◇</span><h3>هنوز سفارشی ثبت نکرده‌اید</h3><p>محصول موردنظرتان را از فروشگاه انتخاب کنید و اولین سفارش خود را ثبت کنید.</p><a href="{{ route('storefront.home') }}">مشاهده محصولات</a></div>
        @else
            <div class="account-order-list">
                @foreach($orders as $order)
                    @php
                        $statusLabels=['pending_payment'=>'در انتظار پرداخت','paid'=>'پرداخت‌شده','processing'=>'در حال پردازش','shipped'=>'ارسال‌شده','delivered'=>'تحویل‌شده','completed'=>'تکمیل‌شده','cancelled'=>'لغوشده','refunded'=>'بازپرداخت‌شده'];
                        $statusLabel=$statusLabels[$order->status]??$order->status;
                    @endphp
                    <article class="account-order-card">
                        <div class="account-order-top"><div><span class="account-order-label">شماره سفارش</span><strong dir="ltr">{{ substr($order->public_id,0,8) }}…</strong></div><span class="account-status {{ in_array($order->status,['completed','delivered','paid'])?'is-success':($order->status==='cancelled'?'is-danger':'') }}">{{ $statusLabel }}</span></div>
                        <div class="account-order-bottom"><span>{{ $order->placed_at?->format('Y/m/d H:i')??$order->created_at?->format('Y/m/d H:i') }}</span><span>{{ number_format($order->total_minor) }} {{ $order->currency }}</span><a href="{{ route('account.orders.show',$order->public_id) }}">جزئیات سفارش ←</a></div>
                    </article>
                @endforeach
            </div>
            <div class="account-pagination">{{ $orders->links() }}</div>
        @endif
    </section>
    <section class="account-profile"><div><h2>اطلاعات حساب</h2><p>نام و ایمیل ثبت‌شده برای این حساب</p></div><dl><div><dt>نام</dt><dd>{{ auth()->user()->name }}</dd></div><div><dt>شماره موبایل</dt><dd dir="ltr">{{ auth()->user()->phone ?? '—' }}</dd></div><div><dt>ایمیل</dt><dd>{{ auth()->user()->email ?? '—' }}</dd></div></dl></section>
</main>
@include('partials.storefront-footer')
<style>
.account-shell{width:min(1050px,92vw);margin:0 auto;padding:38px 0 70px;min-height:55vh}.account-heading{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:25px}.account-eyebrow{font-size:11px;letter-spacing:1.5px;color:#7950bd;font-weight:800}.account-heading h1{font-size:30px;color:#382154;margin:8px 0}.account-heading p,.account-section-head p,.account-profile p{color:#887e94;margin:0;font-size:13px;line-height:1.9}.account-back{border:1px solid #e8e0f2;border-radius:10px;padding:10px 14px;font-size:12px}.account-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:22px 0 32px}.account-stats article,.account-orders,.account-profile{background:#fff;border:1px solid #eee8f7;border-radius:16px;padding:19px}.account-stats article{display:grid;gap:10px}.account-stats span,.account-order-label{font-size:12px;color:#837891}.account-stats strong{font-size:25px;color:#4b239c}.account-section-head h2,.account-profile h2{font-size:18px;margin:0 0 5px;color:#35224d}.account-order-list{display:grid;gap:10px;margin-top:18px}.account-order-card{border:1px solid #eee9f4;border-radius:12px;padding:15px}.account-order-top,.account-order-bottom{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.account-order-top>div{display:grid;gap:5px}.account-order-top strong{font-size:13px}.account-status{font-size:11px;background:#fff4dd;color:#916000;border-radius:20px;padding:6px 10px}.account-status.is-success{background:#e8f6ee;color:#16804b}.account-status.is-danger{background:#fff0ee;color:#b42318}.account-order-bottom{border-top:1px solid #f1edf5;margin-top:13px;padding-top:12px;font-size:12px;color:#7f758b}.account-order-bottom a{font-weight:700}.account-pagination{margin-top:18px}.account-empty{text-align:center;padding:38px 15px}.account-empty>span{font-size:35px;color:#7042ba}.account-empty h3{color:#3d285a}.account-empty p{color:#887e94;font-size:13px}.account-empty a{display:inline-block;background:#6236ad;color:#fff;padding:10px 16px;border-radius:9px;margin-top:10px}.account-profile{margin-top:18px;display:grid;grid-template-columns:1fr 1.5fr;gap:18px}.account-profile dl{margin:0;display:grid;grid-template-columns:1fr 1fr;gap:12px}.account-profile dl div{background:#faf8fd;border-radius:10px;padding:12px}.account-profile dt{font-size:11px;color:#8a8095}.account-profile dd{margin:6px 0 0;font-size:13px;overflow-wrap:anywhere}@media(max-width:700px){.account-heading{align-items:flex-start;flex-direction:column}.account-stats{gap:8px}.account-stats article{padding:12px}.account-stats strong{font-size:20px}.account-profile{grid-template-columns:1fr}.account-profile dl{grid-template-columns:1fr}.account-order-bottom{align-items:flex-start;flex-direction:column}}
</style>
</body></html>
