@extends('layouts.admin')
@section('title',$page->exists?'ویرایش صفحه':'ایجاد صفحه')
@section('content')
@php
$translations=$page->translations??[];$seo=$page->seo??[];$blocks=$page->content_blocks??[];
$fa=$translations['fa']??[];$ku=$translations['ku']??[];$en=$translations['en']??[];
@endphp
<div class="page-head"><div><h1>{{ $page->exists?'ویرایش صفحه':'ایجاد صفحه ثابت' }}</h1><div class="muted">محتوا برای fa، ku و en ذخیره می‌شود. خروجی متن امن و بدون اجرای HTML است.</div></div><a class="btn light" href="{{ route('admin.pages.index') }}">بازگشت به صفحات</a></div>
@if($errors->any())<div class="flash" style="background:#fff0ee;color:#b42318">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ $page->exists?route('admin.pages.update',$page):route('admin.pages.store') }}">
@csrf @if($page->exists)@method('PUT')@endif
<section class="panel">
<h2>تنظیمات صفحه</h2>
<div class="page-form-grid">
<label>شناسه URL (انگلیسی) *<input name="slug" required maxlength="120" pattern="[A-Za-z0-9_-]+" value="{{ old('slug',$page->slug) }}" placeholder="about-us"></label>
<label>وضعیت انتشار<select name="status"><option value="draft" @selected(old('status',$page->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$page->status)==='published')>منتشرشده</option></select></label>
<label>عنوان SEO<input name="seo_title" maxlength="180" value="{{ old('seo_title',$seo['title']??'') }}"></label>
<label>توضیحات SEO<textarea name="seo_description" maxlength="320" rows="2">{{ old('seo_description',$seo['description']??'') }}</textarea></label>
</div>
</section>
<section class="panel">
<h2>محتوای فارسی</h2>
<label class="page-field">عنوان فارسی *<input name="title_fa" required maxlength="180" value="{{ old('title_fa',$fa['title']??'') }}"></label>
<label class="page-field">متن فارسی *<textarea name="body_fa" required rows="12">{{ old('body_fa',$fa['body']??'') }}</textarea></label>
</section>
<section class="panel">
<h2>محتوای کوردی</h2>
<label class="page-field">عنوان کوردی<input name="title_ku" maxlength="180" value="{{ old('title_ku',$ku['title']??'') }}"></label>
<label class="page-field">متن کوردی<textarea name="body_ku" rows="10">{{ old('body_ku',$ku['body']??'') }}</textarea></label>
</section>
<section class="panel">
<h2>محتوای انگلیسی</h2>
<label class="page-field">عنوان انگلیسی<input name="title_en" maxlength="180" value="{{ old('title_en',$en['title']??'') }}"></label>
<label class="page-field">متن انگلیسی<textarea name="body_en" rows="10">{{ old('body_en',$en['body']??'') }}</textarea></label>
</section>
<section class="panel">
<h2>بخش‌های محتوایی صفحه</h2>
<p class="muted">تا ۸ بلوک محتوایی اضافه کنید. ترتیب بلوک‌ها از بالا به پایین است. نوع بلوک فقط ظاهر متن را تعیین می‌کند؛ محتوای HTML یا کد اجرا نمی‌شود.</p>
<div class="page-blocks-grid">
@for($i=0;$i<8;$i++)
@php($block=$blocks[$i]??[])
<fieldset class="page-block-editor">
<legend>بلوک {{ $i+1 }}</legend>
<label>نوع بلوک<select name="blocks[{{ $i }}][type]">
<option value="">بدون بلوک</option>
<option value="heading" @selected(old('blocks.'.$i.'.type',data_get($block,'type'))==='heading')>عنوان بخش</option>
<option value="paragraph" @selected(old('blocks.'.$i.'.type',data_get($block,'type'))==='paragraph')>پاراگراف</option>
<option value="notice" @selected(old('blocks.'.$i.'.type',data_get($block,'type'))==='notice')>کادر اطلاع‌رسانی</option>
</select></label>
<label>متن فارسی<textarea name="blocks[{{ $i }}][text_fa]" maxlength="5000" rows="3">{{ old('blocks.'.$i.'.text_fa',data_get($block,'text.fa')) }}</textarea></label>
<label>متن کوردی<textarea name="blocks[{{ $i }}][text_ku]" maxlength="5000" rows="3">{{ old('blocks.'.$i.'.text_ku',data_get($block,'text.ku')) }}</textarea></label>
<label>متن انگلیسی<textarea name="blocks[{{ $i }}][text_en]" maxlength="5000" rows="3">{{ old('blocks.'.$i.'.text_en',data_get($block,'text.en')) }}</textarea></label>
</fieldset>
@endfor
</div>
</section>
<div class="actions"><button type="submit" class="btn">ذخیره صفحه</button><a class="btn light" href="{{ route('admin.pages.index') }}">انصراف</a></div>
</form>
<style>.page-blocks-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:14px}.page-block-editor{border:1px solid #eee9f3;border-radius:12px;padding:13px;display:grid;gap:10px;min-width:0}.page-block-editor legend{font-size:12px;color:#6236ad;font-weight:bold}.page-block-editor label{display:grid;gap:6px;font-size:12px;color:#625c70}.page-block-editor select,.page-block-editor textarea{width:100%;box-sizing:border-box;border:1px solid #ddd7e8;border-radius:9px;padding:9px;font:inherit;font-size:12px}.page-block-editor textarea{resize:vertical;line-height:1.8}.page-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.page-form-grid label,.page-field{display:grid;gap:7px;font-size:12px;color:#625c70}.page-field{margin-top:14px}.page-form-grid input,.page-form-grid textarea,.page-field input,.page-field textarea{width:100%;font:inherit;font-size:13px;border:1px solid #ddd7e8;border-radius:9px;padding:11px;box-sizing:border-box}.page-field textarea{line-height:1.9;resize:vertical}@media(max-width:760px){.page-form-grid,.page-blocks-grid{grid-template-columns:1fr}}</style>
@endsection
