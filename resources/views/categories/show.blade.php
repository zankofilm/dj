<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head>
    @php($locale = app()->getLocale())
    @php($categoryTitle = data_get($category->translations, $locale.'.name', data_get($category->translations, 'fa.name', $category->slug)))
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $categoryTitle }} | بازارگاه</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) data_get($category->translations, $locale.'.description', '')), 160) }}">
    <link rel="canonical" href="{{ route('categories.show', $category->slug) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@include('partials.storefront-header')
<main class="market-main category-page">
    <nav class="breadcrumbs" aria-label="مسیر صفحه">
        <a href="{{ route('storefront.home') }}">خانه</a><span>/</span><span>{{ $categoryTitle }}</span>
    </nav>
    <section class="category-intro">
        <span class="eyebrow">CATEGORY</span>
        <h1>{{ $categoryTitle }}</h1>
        @if($description = data_get($category->translations, $locale.'.description', data_get($category->translations, 'fa.description')))
            <p>{{ $description }}</p>
        @endif
        <span class="section-note">{{ $products->total() }} محصول</span>
    </section>

    <section class="catalog-filter-panel">
        <form method="GET" action="{{ route('categories.show', $category->slug) }}" class="catalog-filters">
            <label class="search-field"><span>جست‌وجو در این دسته</span><input type="search" name="q" value="{{ request('q') }}" maxlength="120" placeholder="نام محصول یا برند"></label>
            <label><span>فروشنده</span><select name="seller"><option value="">همه فروشندگان</option>
                @foreach($sellers as $seller)<option value="{{ $seller->id }}" @selected((string) request('seller') === (string) $seller->id)>{{ $seller->display_name }}</option>@endforeach
            </select></label>
            <label><span>حداقل قیمت</span><input type="number" name="min_price" min="0" value="{{ request('min_price') }}"></label>
            <label><span>حداکثر قیمت</span><input type="number" name="max_price" min="0" value="{{ request('max_price') }}"></label>
            <label><span>مرتب‌سازی</span><select name="sort">
                <option value="newest" @selected(request('sort', 'newest') === 'newest')>جدیدترین</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>ارزان‌ترین</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>گران‌ترین</option>
            </select></label>
            <div class="filter-actions"><button type="submit" class="primary-link">اعمال فیلتر</button><a href="{{ route('categories.show', $category->slug) }}">پاک‌کردن</a></div>
        </form>
    </section>

    @if($products->isNotEmpty())
        <div class="product-grid">
            @foreach($products as $product)
                @php
                    $title = data_get($product->translations, $locale.'.title', data_get($product->translations, 'fa.title', 'محصول'));
                    $primaryMedia = $product->media->firstWhere('is_primary', true) ?? $product->media->first();
                @endphp
                <article class="product-card">
                    <a class="product-card-image" href="{{ route('products.show', $product->public_id) }}">
                        @if($primaryMedia)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($primaryMedia->storage_path) }}" alt="{{ data_get($primaryMedia->alt_translations, $locale, $title) }}" loading="lazy">
                        @else <span class="image-placeholder">بدون تصویر</span> @endif
                    </a>
                    <div class="product-card-body">
                        <span class="product-seller">{{ $product->seller->display_name }}</span>
                        <h3><a href="{{ route('products.show', $product->public_id) }}">{{ $title }}</a></h3>
                        <strong class="product-price">{{ number_format($product->base_price_minor) }} <small>{{ $product->currency }}</small></strong>
                        <a class="product-open" href="{{ route('products.show', $product->public_id) }}">مشاهده محصول</a>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="pagination-wrap">{{ $products->links() }}</div>
    @else
        <div class="empty-catalog"><h3>محصولی در این دسته پیدا نشد</h3><p>فیلترها را تغییر دهید یا بعداً دوباره مراجعه کنید.</p></div>
    @endif
</main>
@include('partials.storefront-footer')
</body>
</html>
