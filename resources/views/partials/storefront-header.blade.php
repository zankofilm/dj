@php
$siteLocale=app()->getLocale();
$brandNames=$siteAppearance['brand']??['fa'=>'بازارگاه','ku'=>'بازاڕگا','en'=>'Marketplace'];
$brandName=data_get($brandNames,$siteLocale,data_get($brandNames,'fa','بازارگاه'));
$announcement=data_get($siteAppearance,'announcement.'.$siteLocale,data_get($siteAppearance,'announcement.fa',''));
$siteLogo=data_get($siteAppearance,'logo.path');
$headerLinks=data_get($siteAppearance,'navigation.header',[]);
@endphp
@if($announcement)<div class="market-announcement">{{ $announcement }}</div>@endif
<header class="market-header">
    <a class="brand" href="{{ route('storefront.home') }}">
        @if($siteLogo)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($siteLogo) }}" alt="{{ $brandName }}" class="market-brand-logo">@endif
        <span>{{ $brandName }}</span>
    </a>
    <nav class="market-primary-nav" aria-label="منوی اصلی">
        @foreach($headerLinks as $link)
            <a href="{{ $link['url'] }}">{{ data_get($link,'labels.'.$siteLocale,data_get($link,'labels.fa','')) }}</a>
        @endforeach
    </nav>
    <nav class="market-language-nav" aria-label="انتخاب زبان">
        <a href="{{ request()->fullUrlWithQuery(['lang'=>'fa']) }}">فارسی</a>
        <a href="{{ request()->fullUrlWithQuery(['lang'=>'ku']) }}">کوردی</a>
        <a href="{{ request()->fullUrlWithQuery(['lang'=>'en']) }}">English</a>
    </nav>
    @auth
        <a class="header-account" href="{{ route('account.index') }}">{{ $siteLocale==='en'?'Account':($siteLocale==='ku'?'هەژمار':'حساب کاربری') }}</a>
        <form class="header-logout" method="POST" action="{{ route('logout') }}">@csrf<button type="submit">{{ $siteLocale==='en'?'Sign out':($siteLocale==='ku'?'چوونەدەرەوە':'خروج') }}</button></form>
    @else
        <a class="header-account" href="{{ route('login') }}">{{ $siteLocale==='en'?'Sign in':($siteLocale==='ku'?'چوونەژوورەوە':'ورود') }}</a>
    @endauth
    <a class="header-cart" href="{{ route('cart.index') }}">{{ $siteLocale==='en'?'Cart':($siteLocale==='ku'?'سەبەتە':'سبد خرید') }}</a>
</header>
