@extends('layouts.admin')
@section('title','محصولات و دسته‌های منتخب')
@section('content')
@php
$selectedCategoryIds=$categories->where('is_homepage_featured',true)->pluck('id')->all();
$selectedProductIds=$selectedProductIds ?? [];
@endphp
<div class="page-head"><div><h1>محصولات و دسته‌بندی‌های منتخب</h1><div class="muted">مواردی را که باید در بخش منتخب هوم‌پیج دیده شوند انتخاب کنید. ترتیب انتخاب‌شده‌ها در صفحه اصلی مطابق ترتیب فهرست زیر خواهد بود.</div></div><span class="pill">حداکثر ۱۲ مورد از هر گروه</span></div>
@if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
@if($errors->any())<div class="flash" style="background:#fff0ee;color:#b42318">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('admin.homepage-merchandising.update') }}">
@csrf @method('PUT')
@php($visibleProductIds=$products->getCollection()->pluck('id')->all())
@foreach(array_diff($selectedProductIds,$visibleProductIds) as $selectedId)<input type="hidden" name="products[]" value="{{ $selectedId }}">@endforeach
<section class="panel">
    <div class="merch-head"><div><h2>دسته‌بندی‌های منتخب</h2><p class="muted">حداکثر ۱۲ دسته‌بندی فعال</p></div><span class="pill">{{ count($selectedCategoryIds) }} انتخاب فعلی</span></div>
    <div class="merch-grid">
        @forelse($categories as $category)
            @php($categoryName=data_get($category->translations,'fa.name',$category->slug))
            <label class="merch-option">
                <input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id,$selectedCategoryIds))>
                <span><strong>{{ $categoryName }}</strong><small>{{ $category->slug }}</small></span>
                @if($category->is_homepage_featured)<em>منتخب</em>@endif
            </label>
        @empty
            <p>دسته‌بندی فعالی وجود ندارد.</p>
        @endforelse
    </div>
</section>
<section class="panel">
    <div class="merch-head"><div><h2>محصولات منتخب</h2><p class="muted">حداکثر ۱۲ محصول منتشرشده از فروشندگان تأییدشده</p></div><span class="pill">{{ count($selectedProductIds) }} انتخاب فعلی</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>انتخاب</th><th>محصول</th><th>فروشنده</th><th>قیمت</th><th>وضعیت</th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td><input type="checkbox" name="products[]" value="{{ $product->id }}" @checked(in_array($product->id,$selectedProductIds)) aria-label="انتخاب محصول"></td>
                    <td><strong>{{ data_get($product->translations,'fa.title',$product->slug) }}</strong><div class="muted">{{ $product->slug }}</div></td>
                    <td>{{ $product->seller?->display_name }}</td>
                    <td>{{ number_format($product->base_price_minor) }} {{ $product->currency }}</td>
                    <td><span class="pill good">قابل نمایش</span></td>
                </tr>
            @empty
                <tr><td colspan="5">محصول منتشرشده‌ای برای انتخاب وجود ندارد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:14px">{{ $products->links() }}</div>
</section>
<div class="actions"><button type="submit" class="btn">ذخیره انتخاب‌های هوم‌پیج</button><a class="btn light" href="{{ route('storefront.home') }}" target="_blank" rel="noopener">مشاهده هوم‌پیج</a></div>
</form>
<style>
.merch-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.merch-head h2{margin:0 0 5px}.merch-head p{margin:0}.merch-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin-top:16px}.merch-option{display:flex;align-items:center;gap:10px;padding:11px;border:1px solid #eee9f3;border-radius:10px;min-width:0}.merch-option span{display:grid;gap:3px;min-width:0}.merch-option strong{font-size:12px;overflow:hidden;text-overflow:ellipsis}.merch-option small{font-size:10px;color:#8b8497}.merch-option em{margin-inline-start:auto;font-style:normal;font-size:10px;color:#16804b}@media(max-width:760px){.merch-grid{grid-template-columns:1fr}.merch-head{display:block}.merch-head>.pill{display:inline-block;margin-top:10px}}
</style>
@endsection
