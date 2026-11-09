@extends('layouts.admin')
@section('title','چیدمان صفحه اصلی')
@section('content')
@php
$labels = [
    'latest' => ['fa'=>'جدیدترین محصولات','ku'=>'نوێترین بەرهەمەکان','en'=>'Latest products','help'=>'حرکت خودکار دوطرفه و کنترل دستی'],
    'popular' => ['fa'=>'محبوب‌ترین‌ها','ku'=>'زۆرترین داواکراوەکان','en'=>'Most popular','help'=>'رتبه‌بندی بر اساس تعداد اقلام سفارش‌های پرداخت‌شده'],
    'cheapest' => ['fa'=>'ارزان‌ترین‌ها','ku'=>'هەرزانترینەکان','en'=>'Lowest prices','help'=>'مرتب‌سازی بر اساس کمترین قیمت پایه'],
    'discount' => ['fa'=>'تخفیف‌ها','ku'=>'داشکاندنەکان','en'=>'Special offers','help'=>'فقط محصولات دارای قیمت تخفیفی معتبر'],
];
@endphp
<div class="page-head">
    <div><h1>چیدمان صفحه اصلی</h1><div class="muted">عنوان، ترتیب، تعداد محصولات و وضعیت چهار نوار اصلی هوم‌پیج را مدیریت کنید.</div></div>
    <span class="pill">۴ نوار محصول</span>
</div>
@if($errors->any())<div class="flash" style="background:#fff0ee;color:#b42318">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('admin.homepage-shelves.update') }}">
    @csrf @method('PUT')
    @foreach($labels as $key => $label)
        @php($setting = $settings->get($key))
        @if($setting)
        <section class="panel shelf-config">
            <div class="shelf-config-head">
                <div><h2>{{ $label['fa'] }}</h2><p class="muted">{{ $label['help'] }}</p></div>
                <label class="toggle"><input type="checkbox" name="shelves[{{ $key }}][is_active]" value="1" @checked(old("shelves.$key.is_active",$setting->is_active))> نمایش این نوار</label>
            </div>
            <div class="shelf-config-grid">
                <label>عنوان فارسی *<input required maxlength="100" name="shelves[{{ $key }}][title_fa]" value="{{ old("shelves.$key.title_fa",data_get($setting->translations,'fa.title')) }}"></label>
                <label>عنوان کوردی<input maxlength="100" name="shelves[{{ $key }}][title_ku]" value="{{ old("shelves.$key.title_ku",data_get($setting->translations,'ku.title')) }}"></label>
                <label>عنوان انگلیسی<input maxlength="100" name="shelves[{{ $key }}][title_en]" value="{{ old("shelves.$key.title_en",data_get($setting->translations,'en.title')) }}"></label>
                <label>ترتیب نمایش<input type="number" min="1" max="99" required name="shelves[{{ $key }}][sort_order]" value="{{ old("shelves.$key.sort_order",$setting->sort_order) }}"></label>
                <label>تعداد محصولات<input type="number" min="4" max="24" required name="shelves[{{ $key }}][item_limit]" value="{{ old("shelves.$key.item_limit",$setting->item_limit) }}"><small>بین ۴ تا ۲۴ محصول</small></label>
                <label>سرعت حرکت نوار<input type="number" min="10" max="100" required name="shelves[{{ $key }}][motion_speed]" value="{{ old("shelves.$key.motion_speed",$setting->motion_speed) }}"><small>۱۰ آهسته تا ۱۰۰ سریع؛ فقط نوار جدیدترین متحرک است.</small></label>
            </div>
        </section>
        @endif
    @endforeach
    <div class="actions"><button class="btn" type="submit">ذخیره تنظیمات هوم‌پیج</button><a class="btn light" href="{{ route('storefront.home') }}" target="_blank" rel="noopener">مشاهده فروشگاه</a></div>
</form>
<style>
.shelf-config{margin-bottom:14px}.shelf-config-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px}.shelf-config-head h2{margin:0 0 5px}.shelf-config-head p{margin:0}.toggle{display:flex;align-items:center;gap:7px;white-space:nowrap;font-size:12px}.shelf-config-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:18px}.shelf-config-grid label{display:grid;gap:7px;font-size:12px;color:#625c70}.shelf-config-grid input{width:100%;min-width:0}.shelf-config-grid small{color:#8b8497;font-size:10px;line-height:1.6}@media(max-width:760px){.shelf-config-grid{grid-template-columns:1fr}.shelf-config-head{display:block}.toggle{margin-top:12px}}
</style>
@endsection
