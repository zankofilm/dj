@extends('layouts.admin')
@section('title','هویت و سربرگ سایت')
@section('content')
@php
$brand=$settings['brand']??[];$announcement=$settings['announcement']??[];$tagline=$settings['footer_tagline']??[];$copyright=$settings['footer_copyright']??[];$contact=$settings['contact']??[];$navigation=$settings['navigation']??[];$headerLinks=$navigation['header']??[];$footerLinks=$navigation['footer']??[];$logo=$settings['logo']['path']??null;
@endphp
<div class="page-head"><div><h1>هویت، سربرگ و پابرگ سایت</h1><div class="muted">نام برند، پیام بالای سایت، لوگو، اطلاعات تماس و متن پابرگ را بدون تغییر کد مدیریت کنید.</div></div><span class="pill">fa · ku · en</span></div>
@if($errors->any())<div class="flash" style="background:#fff0ee;color:#b42318">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('admin.appearance.update') }}" enctype="multipart/form-data">
@csrf @method('PUT')
<section class="panel">
<h2>نام و لوگوی برند</h2>
<div class="appearance-grid">
<label>نام فارسی *<input required maxlength="80" name="brand_fa" value="{{ old('brand_fa',$brand['fa']??'بازارگاه') }}"></label>
<label>ناوی کوردی<input maxlength="80" name="brand_ku" value="{{ old('brand_ku',$brand['ku']??'بازاڕگا') }}"></label>
<label>English brand name<input maxlength="80" name="brand_en" value="{{ old('brand_en',$brand['en']??'Marketplace') }}"></label>
<label>تصویر لوگو (حداکثر ۲ مگابایت)<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
</div>
@if($logo)<div class="logo-preview"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="لوگوی فعلی"><span class="muted">لوگوی فعلی</span></div>@endif
</section>
<section class="panel">
<h2>نوار اطلاع‌رسانی بالای سایت</h2>
<div class="appearance-grid">
<label>پیام فارسی<input maxlength="180" name="announcement_fa" value="{{ old('announcement_fa',$announcement['fa']??'') }}"></label>
<label>پیام کوردی<input maxlength="180" name="announcement_ku" value="{{ old('announcement_ku',$announcement['ku']??'') }}"></label>
<label>پیام انگلیسی<input maxlength="180" name="announcement_en" value="{{ old('announcement_en',$announcement['en']??'') }}"></label>
</div>
</section>
<section class="panel">
<h2>اطلاعات تماس و شبکه‌های اجتماعی</h2>
<div class="appearance-grid">
<label>ایمیل تماس<input type="email" name="contact_email" value="{{ old('contact_email',$contact['email']??'') }}"></label>
<label>شماره تماس<input maxlength="40" name="contact_phone" value="{{ old('contact_phone',$contact['phone']??'') }}"></label>
<label>لینک اینستاگرام<input type="url" name="instagram_url" placeholder="https://instagram.com/..." value="{{ old('instagram_url',$contact['instagram']??'') }}"></label>
<label>لینک تلگرام<input type="url" name="telegram_url" placeholder="https://t.me/..." value="{{ old('telegram_url',$contact['telegram']??'') }}"></label>
</div>
</section>
<section class="panel">
<h2>منوی سربرگ</h2>
<p class="muted">حداکثر ۵ لینک. آدرس داخلی باید با / شروع شود؛ لینک خارجی فقط با https:// پذیرفته می‌شود.</p>
<div class="appearance-grid">
@for($i=0;$i<5;$i++)
@php($item=$headerLinks[$i]??[])
<fieldset class="menu-link-field"><legend>لینک {{ $i+1 }}</legend>
<label>عنوان فارسی<input maxlength="80" name="header_links[{{ $i }}][label_fa]" value="{{ old('header_links.'.$i.'.label_fa',data_get($item,'labels.fa')) }}"></label>
<label>عنوان کوردی<input maxlength="80" name="header_links[{{ $i }}][label_ku]" value="{{ old('header_links.'.$i.'.label_ku',data_get($item,'labels.ku')) }}"></label>
<label>عنوان انگلیسی<input maxlength="80" name="header_links[{{ $i }}][label_en]" value="{{ old('header_links.'.$i.'.label_en',data_get($item,'labels.en')) }}"></label>
<label>آدرس لینک<input maxlength="2048" name="header_links[{{ $i }}][url]" value="{{ old('header_links.'.$i.'.url',data_get($item,'url')) }}" placeholder="/pages/about یا https://example.com"></label>
</fieldset>
@endfor
</div>
</section>
<section class="panel">
<h2>لینک‌های پابرگ</h2>
<p class="muted">حداکثر ۸ لینک در پابرگ سایت.</p>
<div class="appearance-grid">
@for($i=0;$i<8;$i++)
@php($item=$footerLinks[$i]??[])
<fieldset class="menu-link-field"><legend>لینک {{ $i+1 }}</legend>
<label>عنوان فارسی<input maxlength="80" name="footer_links[{{ $i }}][label_fa]" value="{{ old('footer_links.'.$i.'.label_fa',data_get($item,'labels.fa')) }}"></label>
<label>عنوان کوردی<input maxlength="80" name="footer_links[{{ $i }}][label_ku]" value="{{ old('footer_links.'.$i.'.label_ku',data_get($item,'labels.ku')) }}"></label>
<label>عنوان انگلیسی<input maxlength="80" name="footer_links[{{ $i }}][label_en]" value="{{ old('footer_links.'.$i.'.label_en',data_get($item,'labels.en')) }}"></label>
<label>آدرس لینک<input maxlength="2048" name="footer_links[{{ $i }}][url]" value="{{ old('footer_links.'.$i.'.url',data_get($item,'url')) }}" placeholder="/pages/contact یا https://example.com"></label>
</fieldset>
@endfor
</div>
</section>
<section class="panel">
<h2>پابرگ سایت</h2>
<div class="appearance-grid">
<label>توضیح فارسی<input maxlength="250" name="footer_tagline_fa" value="{{ old('footer_tagline_fa',$tagline['fa']??'') }}"></label>
<label>توضیح کوردی<input maxlength="250" name="footer_tagline_ku" value="{{ old('footer_tagline_ku',$tagline['ku']??'') }}"></label>
<label>توضیح انگلیسی<input maxlength="250" name="footer_tagline_en" value="{{ old('footer_tagline_en',$tagline['en']??'') }}"></label>
<label>کپی‌رایت فارسی<input maxlength="160" name="footer_copyright_fa" value="{{ old('footer_copyright_fa',$copyright['fa']??'') }}"></label>
<label>کپی‌رایت کوردی<input maxlength="160" name="footer_copyright_ku" value="{{ old('footer_copyright_ku',$copyright['ku']??'') }}"></label>
<label>کپی‌رایت انگلیسی<input maxlength="160" name="footer_copyright_en" value="{{ old('footer_copyright_en',$copyright['en']??'') }}"></label>
</div>
</section>
<div class="actions"><button class="btn" type="submit">ذخیره هویت سایت</button><a class="btn light" href="{{ route('storefront.home') }}" target="_blank" rel="noopener">مشاهده سایت</a></div>
</form>
<style>
.appearance-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:16px}.menu-link-field{border:1px solid #eee9f3;border-radius:10px;padding:12px;display:grid;gap:9px;min-width:0}.menu-link-field legend{font-size:12px;font-weight:bold;color:#6236ad}.appearance-grid label{display:grid;gap:7px;font-size:12px;color:#625c70}.appearance-grid input{width:100%;min-width:0}.logo-preview{display:flex;align-items:center;gap:12px;margin-top:16px}.logo-preview img{max-width:110px;max-height:70px;object-fit:contain;border:1px solid #eee;border-radius:10px;padding:8px}@media(max-width:760px){.appearance-grid{grid-template-columns:1fr}}
</style>
@endsection
