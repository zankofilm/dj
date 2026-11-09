@extends('layouts.admin')
@section('title','داشبورد مدیریت بازارگاه')
@section('content')
<div class="page-head"><div><h1>داشبورد مدیریتی</h1><div class="muted">نمای کلی وضعیت فروشگاه و فعالیت‌های اخیر</div></div><span class="pill good">سامانه مدیریت</span></div>
<div class="cards">
<div class="stat"><div class="stat-label">کل کاربران</div><div class="stat-value">{{ number_format($stats['users']) }}</div><div class="stat-note">حساب‌های ثبت‌شده</div></div>
<div class="stat"><div class="stat-label">فروشندگان</div><div class="stat-value">{{ number_format($stats['sellers']) }}</div><div class="stat-note">{{ number_format($stats['pending_sellers']) }} پرونده نیازمند بررسی</div></div>
<div class="stat"><div class="stat-label">محصولات</div><div class="stat-value">{{ number_format($stats['products']) }}</div><div class="stat-note">{{ number_format($stats['pending_products']) }} محصول در صف بررسی</div></div>
<div class="stat"><div class="stat-label">کل سفارش‌ها</div><div class="stat-value">{{ number_format($stats['orders']) }}</div><div class="stat-note">تمام وضعیت‌ها</div></div>
</div>
<div class="columns">
<section>
<div class="panel"><h2>آخرین سفارش‌ها</h2><div class="table-wrap"><table><thead><tr><th>شناسه</th><th>وضعیت</th><th>مبلغ</th><th>تاریخ</th></tr></thead><tbody>
@forelse($recentOrders as $order)<tr><td>{{ substr($order->public_id,0,12) }}</td><td><span class="pill">{{ $order->status }}</span></td><td>{{ number_format($order->total_minor) }} {{ $order->currency }}</td><td>{{ $order->created_at?->format('Y/m/d') }}</td></tr>@empty<tr><td colspan="4" class="muted">هنوز سفارشی ثبت نشده است.</td></tr>@endforelse
</tbody></table></div></div>
<div class="panel"><h2>آخرین محصولات</h2>
@forelse($recentProducts as $product)<div class="list-row"><span>{{ $product->slug }} <small class="muted">· {{ $product->seller?->display_name }}</small></span><span class="pill">{{ $product->status }}</span></div>@empty<div class="muted">محصولی ثبت نشده است.</div>@endforelse
</div>
</section>
<section>
<div class="panel"><h2>پرونده‌های اخیر فروشندگان</h2>
@forelse($recentSellers as $seller)<div class="list-row"><span>{{ $seller->display_name }}</span><span class="pill {{ in_array($seller->status,['approved','active'])?'good':'warn' }}">{{ $seller->status }}</span></div>@empty<div class="muted">فروشنده‌ای ثبت نشده است.</div>@endforelse
@if(request()->user()->hasPermission('sellers.review'))<a class="btn light" href="{{ route('admin.sellers.index') }}">مدیریت فروشندگان</a>@endif
</div>
<div class="panel"><h2>رویدادهای مدیریتی اخیر</h2>
@forelse($recentAudit as $event)<div class="list-row"><span>{{ $event->action }}<small class="muted" style="display:block">{{ $event->actor?->name ?? 'سامانه' }}</small></span><small class="muted">{{ $event->created_at?->format('Y/m/d H:i') }}</small></div>@empty<div class="muted">رویداد ثبت‌شده‌ای وجود ندارد.</div>@endforelse
</div>
</section>
</div>
@endsection
