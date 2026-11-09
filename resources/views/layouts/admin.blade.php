<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa','ku']) ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', 'مدیریت بازارگاه')</title>
<style>
:root{--purple:#6236ad;--purple-dark:#45217f;--purple-soft:#f2edfb;--ink:#292437;--muted:#777184;--line:#ece8f2;--bg:#f7f6fa;--green:#16804b;--red:#b42318}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Tahoma,Arial,sans-serif}
.shell{display:grid;grid-template-columns:250px minmax(0,1fr);min-height:100vh}.sidebar{background:#fff;border-inline-end:1px solid var(--line);padding:22px 16px;position:sticky;top:0;height:100vh}
.brand{display:flex;align-items:center;gap:10px;padding:4px 8px 24px;font-size:18px;font-weight:bold;color:var(--purple-dark)}
.brand-mark{width:36px;height:36px;border-radius:12px;background:linear-gradient(135deg,#8054ce,#4b268e);display:grid;place-items:center;color:#fff}
.nav-label{font-size:11px;color:#a19aaa;padding:16px 10px 8px}.nav a{display:flex;gap:10px;align-items:center;text-decoration:none;color:#625c70;padding:11px 12px;border-radius:10px;margin:3px 0;font-size:13px}.nav a:hover,.nav a.active{background:var(--purple-soft);color:var(--purple-dark);font-weight:bold}
.main{min-width:0}.topbar{height:70px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 28px}
.content{padding:28px;max-width:1500px;margin:auto}.page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:22px}.page-head h1{font-size:22px;margin:0 0 7px}.muted{color:var(--muted);font-size:12px;line-height:1.8}
.cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}.stat,.panel{background:#fff;border:1px solid var(--line);border-radius:15px;padding:18px}.stat-label{font-size:12px;color:var(--muted)}.stat-value{font-size:25px;font-weight:bold;margin-top:12px}.stat-note{font-size:11px;color:var(--muted);margin-top:7px}
.columns{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(300px,1fr);gap:16px}.panel{margin-bottom:16px}.panel h2{font-size:15px;margin:0 0 15px}
.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;white-space:nowrap}th,td{text-align:start;padding:12px 10px;border-bottom:1px solid #f0edf4;font-size:12px}th{color:var(--muted);font-weight:normal;background:#fcfbfd}
.pill{display:inline-block;padding:5px 9px;border-radius:30px;background:#f0edf5;color:#5d5669;font-size:11px}.pill.good{background:#e8f6ee;color:var(--green)}.pill.warn{background:#fff5df;color:#946200}.pill.bad{background:#fff0ee;color:var(--red)}
.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:9px;padding:9px 13px;background:var(--purple);color:#fff;text-decoration:none;font:inherit;font-size:12px;cursor:pointer}.btn.light{background:var(--purple-soft);color:var(--purple-dark)}.btn.danger{background:#fff0ee;color:var(--red)}
input,select{border:1px solid #ddd7e8;border-radius:9px;padding:10px;background:#fff;font:inherit;font-size:12px;max-width:100%}
.flash{padding:12px 14px;border-radius:10px;background:#e8f6ee;color:var(--green);margin-bottom:16px;font-size:13px}
.actions{display:flex;gap:7px;align-items:center;flex-wrap:wrap}.list-row{display:flex;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #f1eef5;font-size:12px}.list-row:last-child{border:0}
.mobile-title{display:none}
@media(max-width:1050px){.cards{grid-template-columns:repeat(2,minmax(0,1fr))}.columns{grid-template-columns:1fr}}
@media(max-width:720px){.shell{grid-template-columns:1fr}.sidebar{position:static;height:auto;padding:12px}.brand{padding:4px 8px 10px}.nav{display:flex;overflow:auto;gap:5px}.nav-label{display:none}.nav a{white-space:nowrap}.topbar{height:55px;padding:0 16px}.content{padding:18px 12px}.cards{gap:8px}.stat{padding:13px}.stat-value{font-size:21px}.page-head{display:block}.page-head .actions{margin-top:12px}.desktop-title{display:none}.mobile-title{display:inline}}
</style>
</head><body>
<div class="shell">
<aside class="sidebar">
<a class="brand" href="{{ route('admin.dashboard') }}" style="text-decoration:none"><span class="brand-mark">ب</span><span>بازارگاه <small style="display:block;font-size:10px;color:#8b8497;font-weight:normal;margin-top:3px">مدیریت مرکزی</small></span></a>
<nav class="nav">
<div class="nav-label">نمای کلی</div>
<a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}">▦ داشبورد</a>
<div class="nav-label">مدیریت بازارگاه</div>
@if(request()->user()->hasPermission('users.manage'))<a class="{{ request()->routeIs('admin.users.*')?'active':'' }}" href="{{ route('admin.users.index') }}">♙ کاربران</a>@endif
@if(request()->user()->hasPermission('sellers.review'))<a class="{{ request()->routeIs('admin.sellers.*')?'active':'' }}" href="{{ route('admin.sellers.index') }}">▣ فروشندگان</a>@endif
@if(request()->user()->hasPermission('catalog.manage'))<a class="{{ request()->routeIs('admin.catalog.*')?'active':'' }}" href="{{ route('admin.catalog.index') }}">◇ محصولات و کاتالوگ</a>@endif
@if(request()->user()->hasPermission('banners.manage'))<a class="{{ request()->routeIs('admin.banners.*')?'active':'' }}" href="{{ route('admin.banners.index') }}">▧ بنرهای صفحه اصلی</a>@endif
@if(request()->user()->hasPermission('homepage.manage'))<a class="{{ request()->routeIs('admin.homepage-shelves.*')?'active':'' }}" href="{{ route('admin.homepage-shelves.index') }}">▤ چیدمان هوم‌پیج</a>@endif
@if(request()->user()->hasPermission('homepage.manage'))<a class="{{ request()->routeIs('admin.homepage-revisions.*')?'active':'' }}" href="{{ route('admin.homepage-revisions.index') }}">◷ نسخه‌ها و انتشار هوم‌پیج</a>@endif
@if(request()->user()->hasPermission('homepage.manage'))<a class="{{ request()->routeIs('admin.homepage-merchandising.*')?'active':'' }}" href="{{ route('admin.homepage-merchandising.index') }}">✦ محصولات و دسته‌های منتخب</a>@endif
@if(request()->user()->hasPermission('appearance.manage'))<a class="{{ request()->routeIs('admin.appearance.*')?'active':'' }}" href="{{ route('admin.appearance.edit') }}">◈ هویت و سربرگ سایت</a>@endif
@if(request()->user()->hasPermission('pages.publish'))<a class="{{ request()->routeIs('admin.pages.*')?'active':'' }}" href="{{ route('admin.pages.index') }}">▧ صفحات ثابت سایت</a>@endif
<a href="{{ route('storefront.home') }}">↗ مشاهده فروشگاه</a>
<div class="nav-label">نسخه و سامانه</div>
<a href="#" aria-disabled="true" style="opacity:.55;cursor:not-allowed">▤ سفارش‌ها (در دست توسعه)</a>
<a href="#" aria-disabled="true" style="opacity:.55;cursor:not-allowed">◈ قالب‌ها و افزونه‌ها (در دست توسعه)</a>
</nav>
</aside>
<div class="main">
<header class="topbar"><strong class="desktop-title">پنل مدیریت بازارگاه</strong><strong class="mobile-title">مدیریت</strong><span class="muted">{{ request()->user()->name }} · مدیر سامانه</span></header>
<main class="content">
@if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
@yield('content')
</main>
</div>
</div>
</body></html>
