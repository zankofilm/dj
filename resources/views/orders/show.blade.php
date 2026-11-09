<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(),['fa','ku'])?'rtl':'ltr' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>جزئیات سفارش | بازارگاه</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
@include('partials.storefront-header')
<main class="order-detail-shell">
    <nav class="breadcrumbs"><a href="{{ route('storefront.home') }}">خانه</a><span>/</span><a href="{{ route('account.index') }}">حساب کاربری</a><span>/</span><span>جزئیات سفارش</span></nav>
    <div class="order-detail-heading"><div><span class="order-eyebrow">ORDER DETAILS</span><h1>جزئیات سفارش</h1><p>شماره سفارش: <bdi>{{ $order->public_id }}</bdi></p></div><a href="{{ route('account.index') }}">بازگشت به سفارش‌ها</a></div>
    @php($statusLabels=['pending_payment'=>'در انتظار پرداخت','paid'=>'پرداخت‌شده','processing'=>'در حال پردازش','shipped'=>'ارسال‌شده','delivered'=>'تحویل‌شده','completed'=>'تکمیل‌شده','cancelled'=>'لغوشده','refunded'=>'بازپرداخت‌شده'])
    <section class="order-summary">
        <div><span>وضعیت سفارش</span><strong>{{ $statusLabels[$order->status]??$order->status }}</strong></div>
        <div><span>مبلغ کل</span><strong>{{ number_format($order->total_minor) }} {{ $order->currency }}</strong></div>
        <div><span>تاریخ ثبت</span><strong>{{ $order->placed_at?->format('Y/m/d H:i')??$order->created_at?->format('Y/m/d H:i') }}</strong></div>
    </section>
    @if($order->status==='pending_payment')
        @if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif
        <section class="order-payment">
            <h2>پرداخت سفارش</h2>
            <form method="POST" action="{{ route('payments.start',$order->public_id) }}">@csrf
                <label>شناسه درگاه پیکربندی‌شده<input name="provider" required maxlength="80"></label>
                <label>کلید یکتای درخواست<input name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}" required></label>
                <button type="submit">ادامه به پرداخت</button>
            </form>
            <p class="notice">درگاه واقعی تا زمان نصب Adapter رسمی و تنظیم امن کلیدها فعال نیست.</p>
        </section>
    @endif
    <section class="order-lines">
        <h2>اقلام سفارش</h2>
        @forelse($order->items as $item)
            <article class="order-line"><div><strong>{{ $item->product_title_snapshot }}</strong><small>تعداد: {{ number_format($item->quantity) }}</small></div><strong>{{ number_format($item->line_total_minor) }} {{ $order->currency }}</strong></article>
        @empty
            <p>اطلاعات اقلام این سفارش در دسترس نیست.</p>
        @endforelse
    </section>
    @foreach($order->sellerOrders as $sellerOrder)
        <section class="seller-row"><h3>{{ $sellerOrder->seller->display_name }}</h3><p>مبلغ فروشنده: {{ number_format($sellerOrder->total_minor) }} {{ $sellerOrder->currency }}</p><p>وضعیت: {{ $statusLabels[$sellerOrder->status]??$sellerOrder->status }}</p></section>
    @endforeach
</main>
@include('partials.storefront-footer')
<style>
.order-detail-shell{width:min(900px,92vw);margin:0 auto;padding:25px 0 70px;min-height:55vh}.order-detail-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;margin:10px 0 22px}.order-detail-heading h1{font-size:28px;color:#382154;margin:7px 0}.order-detail-heading p{font-size:12px;color:#82778f}.order-detail-heading>a{border:1px solid #e8e0f2;border-radius:9px;padding:10px 13px;font-size:12px}.order-eyebrow{font-size:10px;letter-spacing:1px;color:#7950bd;font-weight:bold}.order-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.order-summary>div,.order-payment,.order-lines{background:#fff;border:1px solid #eee8f7;border-radius:14px;padding:17px;margin-bottom:15px}.order-summary>div{display:grid;gap:9px}.order-summary span{font-size:11px;color:#877d91}.order-summary strong{font-size:14px;color:#4b239c}.order-payment h2,.order-lines h2{font-size:17px;color:#392458;margin:0 0 15px}.order-payment label{display:grid;gap:7px;font-size:12px;margin:12px 0}.order-payment input{padding:10px;border:1px solid #ddd7e8;border-radius:8px}.order-payment button{padding:11px 16px;background:#6236ad;color:white;border:0;border-radius:9px}.order-line{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:13px 0;border-bottom:1px solid #f0edf4}.order-line:last-child{border:0}.order-line div{display:grid;gap:6px}.order-line small{font-size:11px;color:#887e94}.order-line>strong{font-size:13px}.seller-row{background:#faf8fd;border:1px solid #eee8f7;border-radius:12px;padding:15px;margin:12px 0}.seller-row h3{margin-top:0;color:#392458}@media(max-width:650px){.order-detail-heading{align-items:flex-start;flex-direction:column}.order-summary{grid-template-columns:1fr}.order-line{align-items:flex-start;flex-direction:column}}
</style>
</body></html>
