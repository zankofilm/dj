<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa','ku'])?'rtl':'ltr' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
@php
$locale=app()->getLocale();$translation=$page->translations[$locale]??$page->translations['fa']??[];
$title=$translation['title']??'بازارگاه';$body=$translation['body']??'';$contentBlocks=$page->content_blocks??[];
@endphp
<title>{{ $page->seo['title']??$title }} | بازارگاه</title>
<meta name="description" content="{{ $page->seo['description']??\Illuminate\Support\Str::limit(strip_tags($body),160) }}">
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
@include('partials.storefront-header')
<main class="static-page-shell">
<nav class="breadcrumbs" aria-label="مسیر صفحه"><a href="{{ route('storefront.home') }}">خانه</a><span>/</span><span>{{ $title }}</span></nav>
<article class="static-page-content">
    <h1>{{ $title }}</h1>
    @if(trim($body)!=='')<div class="static-page-body">{{ $body }}</div>@endif
    @foreach($contentBlocks as $block)
        @php($blockText=data_get($block,'text.'.$locale,data_get($block,'text.fa','')))
        @if($blockText!=='')
            @if(($block['type']??'')==='heading')
                <h2 class="static-page-block-heading">{{ $blockText }}</h2>
            @elseif(($block['type']??'')==='notice')
                <aside class="static-page-block-notice">{{ $blockText }}</aside>
            @elseif(($block['type']??'')==='paragraph')
                <p class="static-page-block-paragraph">{{ $blockText }}</p>
            @endif
        @endif
    @endforeach
</article>
</main>
@include('partials.storefront-footer')
<style>
.static-page-shell{max-width:1050px;margin:0 auto;padding:28px 20px 64px;min-height:45vh}.static-page-content{background:#fff;border:1px solid #eee9f3;border-radius:18px;padding:clamp(22px,5vw,52px);margin-top:20px}.static-page-content h1{font-size:clamp(24px,4vw,36px);color:#45217f;margin:0 0 24px}.static-page-body{white-space:pre-wrap;line-height:2.2;color:#514b5d;font-size:15px;overflow-wrap:anywhere}
.static-page-block-heading{margin:30px 0 12px;color:#45217f;font-size:22px;line-height:1.7}
.static-page-block-paragraph{white-space:pre-wrap;line-height:2.15;color:#514b5d;font-size:15px;overflow-wrap:anywhere;margin:12px 0}
.static-page-block-notice{white-space:pre-wrap;line-height:2;background:#f5f0fc;border-inline-start:4px solid #6236ad;border-radius:10px;padding:16px 18px;margin:20px 0;color:#49336a;overflow-wrap:anywhere}
</style>
</body></html>
