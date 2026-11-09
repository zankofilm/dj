@extends('layouts.admin')
@section('title', $banner->exists ? 'ویرایش بنر' : 'افزودن بنر')
@section('content')
@php
    $fa = data_get($banner->translations,'fa',[]);
    $ku = data_get($banner->translations,'ku',[]);
    $en = data_get($banner->translations,'en',[]);
@endphp
<div class="page-head"><div><h1>{{ $banner->exists ? 'ویرایش بنر' : 'افزودن بنر جدید' }}</h1><div class="muted">متن‌ها برای فارسی، کوردی و انگلیسی جداگانه تنظیم می‌شوند.</div></div><a class="btn light" href="{{ route('admin.banners.index') }}">بازگشت</a></div>
@if($errors->any())<div class="flash" style="background:#fff0ee;color:#b42318">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="panel">
<form method="POST" enctype="multipart/form-data" action="{{ $banner->exists ? route('admin.banners.update',$banner) : route('admin.banners.store') }}">
    @csrf
    @if($banner->exists) @method('PUT') @endif
    <h2>متن فارسی</h2>
    <div class="banner-form-grid">
        <label>عنوان فارسی *<input name="title_fa" required maxlength="160" value="{{ old('title_fa',data_get($fa,'title')) }}"></label>
        <label>زیرعنوان فارسی<input name="subtitle_fa" maxlength="500" value="{{ old('subtitle_fa',data_get($fa,'subtitle')) }}"></label>
        <label>متن دکمه فارسی<input name="button_fa" maxlength="60" value="{{ old('button_fa',data_get($fa,'button','مشاهده')) }}"></label>
    </div>
    <h2>کوردی</h2>
    <div class="banner-form-grid">
        <label>ناونیشان<input name="title_ku" maxlength="160" value="{{ old('title_ku',data_get($ku,'title')) }}"></label>
        <label>دەقی ژێرناونیشان<input name="subtitle_ku" maxlength="500" value="{{ old('subtitle_ku',data_get($ku,'subtitle')) }}"></label>
        <label>دەقی دوگمە<input name="button_ku" maxlength="60" value="{{ old('button_ku',data_get($ku,'button')) }}"></label>
    </div>
    <h2>English</h2>
    <div class="banner-form-grid">
        <label>English title<input name="title_en" maxlength="160" value="{{ old('title_en',data_get($en,'title')) }}"></label>
        <label>English subtitle<input name="subtitle_en" maxlength="500" value="{{ old('subtitle_en',data_get($en,'subtitle')) }}"></label>
        <label>Button label<input name="button_en" maxlength="60" value="{{ old('button_en',data_get($en,'button')) }}"></label>
    </div>
    <h2>تصویر و مقصد</h2>
    @if($banner->image_path)
        <p><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) }}" alt="" style="max-width:260px;max-height:130px;border-radius:10px"></p>
    @endif
    <div class="banner-form-grid">
        <label>تصویر بنر {{ $banner->exists ? '(برای جایگزینی انتخاب کنید)' : '*' }}<input type="file" name="image" accept="image/jpeg,image/png,image/webp" {{ $banner->exists ? '' : 'required' }}></label>
        <label>لینک مقصد (URL کامل)<input type="url" name="target_url" maxlength="2048" value="{{ old('target_url',$banner->target_url) }}" placeholder="https://example.com/category"></label>
        <label>ترتیب نمایش<input type="number" name="sort_order" min="0" max="100000" required value="{{ old('sort_order',$banner->sort_order ?? 0) }}"></label>
    </div>
    <h2>زمان‌بندی</h2>
    <div class="banner-form-grid">
        <label>شروع نمایش<input type="datetime-local" name="starts_at" value="{{ old('starts_at',$banner->starts_at?->format('Y-m-d\TH:i')) }}"></label>
        <label>پایان نمایش<input type="datetime-local" name="ends_at" value="{{ old('ends_at',$banner->ends_at?->format('Y-m-d\TH:i')) }}"></label>
    </div>
    <label class="banner-active"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$banner->is_active))> بنر فعال باشد</label>
    <div class="actions" style="margin-top:20px"><button class="btn" type="submit">ذخیره بنر</button><a class="btn light" href="{{ route('admin.banners.index') }}">انصراف</a></div>
</form>
</div>
<style>
.banner-form-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:12px 0 24px}
.banner-form-grid label{display:grid;gap:7px;font-size:12px;color:#625c70}
.banner-form-grid input{width:100%;min-width:0}
.banner-active{display:flex;align-items:center;gap:8px;font-size:13px}
@media(max-width:760px){.banner-form-grid{grid-template-columns:1fr}}
</style>
@endsection
