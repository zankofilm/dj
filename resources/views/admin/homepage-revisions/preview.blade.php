@extends('layouts.admin')
@section('title','پیش‌نمایش نسخه طراحی')
@section('content')
@php
$snapshot=$revision->snapshot ?? [];
$shelves=collect($snapshot['shelves'] ?? [])->sortBy('sort_order');
$appearance=collect($snapshot['appearance'] ?? [])->keyBy('setting_key');
$brand=data_get($appearance->get('brand'),'value.fa','بازارگاه');
$announcement=data_get($appearance->get('announcement'),'value.fa','');
$tagline=data_get($appearance->get('footer_tagline'),'value.fa','');
$banners=collect($snapshot['banners'] ?? [])->sortBy('sort_order');
$featuredCategoryIds=$snapshot['featured_categories'] ?? [];
$featuredProductIds=$snapshot['featured_products'] ?? [];
@endphp
<div class="page-head"><div><h1>پیش‌نمایش: {{ $revision->title }}</h1><div class="muted">نسخه #{{ $revision->id }} · {{ $revision->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}</div></div><a class="btn light" href="{{ route('admin.homepage-revisions.index') }}">بازگشت به تاریخچه</a></div>
@if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
<section class="revision-preview-shell">
    <div class="preview-announcement">{{ $announcement ?: 'نوار اطلاع‌رسانی غیرفعال است' }}</div>
    <header class="preview-header"><strong>{{ $brand }}</strong><span>فارسی　 کوردی　 English</span><span>سبد خرید</span></header>
    <div class="preview-hero">
        <span>MARKETPLACE · HERO SLIDER</span>
        <h2>{{ data_get($banners->first(),'translations.fa.title','اسلایدر اصلی بازارگاه') }}</h2>
        <p>{{ data_get($banners->first(),'translations.fa.subtitle','پیش‌نمایش محتوای بنرهای ذخیره‌شده') }}</p>
        <small>{{ $banners->count() }} بنر ذخیره‌شده</small>
    </div>
    <div class="preview-shelves">
        @foreach($shelves as $shelf)
            @if($shelf['is_active'] ?? false)
                <div class="preview-shelf">
                    <div><span>{{ data_get($shelf,'translations.fa.eyebrow','MARKETPLACE') }}</span><h3>{{ data_get($shelf,'translations.fa.title',$shelf['shelf_key'] ?? '') }}</h3></div>
                    <small>{{ $shelf['item_limit'] ?? 12 }} محصول · ترتیب {{ $shelf['sort_order'] ?? 0 }}</small>
                </div>
            @endif
        @endforeach
    </div>
    <footer class="preview-footer"><strong>{{ $brand }}</strong><span>{{ $tagline }}</span></footer>
</section>
<div class="revision-meta">
    <span>Snapshot: {{ data_get($snapshot,'captured_at','—') }}</span>
    <span>Schema version: {{ data_get($snapshot,'schema_version','—') }}</span>
    <span>بنرها: {{ $banners->count() }} · نوارها: {{ $shelves->count() }}</span>
    <span>دسته‌های منتخب: {{ count($featuredCategoryIds) }} · محصولات منتخب: {{ count($featuredProductIds) }}</span>
</div>
@if($revision->status !== 'published')
<section class="panel publish-panel">
    <h2>انتشار این نسخه</h2>
    <p>با انتشار، تنظیمات بنرها، نوارهای محصول و هویت سایت با Snapshot این نسخه جایگزین می‌شوند. برای بازگشت، یک نسخه منتشرشده قدیمی‌تر را دوباره منتشر کنید.</p>
    <form method="POST" action="{{ route('admin.homepage-revisions.publish',$revision) }}" onsubmit="return confirm('از انتشار این نسخه و جایگزینی تنظیمات فعلی مطمئن هستید؟')">
        @csrf
        <label class="confirm-publish"><input type="checkbox" name="confirm_publish" value="1" required> تنظیمات را بررسی کرده‌ام و انتشار را تأیید می‌کنم.</label>
        <button class="btn" type="submit">تأیید و انتشار نسخه</button>
    </form>
</section>
@else
<section class="panel"><span class="pill good">این نسخه منتشر شده است</span><p>برای بازگرداندن همین نسخه در آینده، از تاریخچه آن را انتخاب و دوباره منتشر کنید.</p></section>
@endif
<style>
.revision-preview-shell{background:#fff;border:1px solid #e9e3f2;border-radius:18px;overflow:hidden;color:#292333}
.preview-announcement{padding:10px;text-align:center;background:#f2edfb;color:#5c3695;font-size:12px}
.preview-header{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #eee;font-size:13px}
.preview-hero{margin:20px;padding:38px;border-radius:16px;background:linear-gradient(120deg,#f1eafd,#fff 72%);min-height:170px;color:#56308f}
.preview-hero h2{font-size:26px;margin:14px 0 8px}.preview-hero p{color:#625c70}.preview-hero small,.preview-hero>span{font-size:11px}
.preview-shelves{padding:0 20px 20px;display:grid;gap:12px}
.preview-shelf{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px;border:1px solid #eee;border-radius:12px}
.preview-shelf span{font-size:10px;color:#8873aa}.preview-shelf h3{margin:5px 0 0;font-size:16px}.preview-shelf small{font-size:11px;color:#766f80}
.preview-footer{display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:22px;background:#faf9fc;border-top:1px solid #eee}
.revision-meta{display:flex;gap:16px;flex-wrap:wrap;margin:12px 0;color:#82798d;font-size:11px}
.publish-panel{margin-top:14px}.publish-panel p{font-size:12px;line-height:2;color:#746d7e}.confirm-publish{display:flex;gap:8px;align-items:center;font-size:12px;margin:16px 0}
@media(max-width:640px){.preview-header{flex-wrap:wrap;gap:10px}.preview-hero{margin:12px;padding:24px}.preview-shelf{align-items:flex-start;flex-direction:column}}
</style>
@endsection
