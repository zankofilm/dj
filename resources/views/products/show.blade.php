<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head>
    @php
        $locale = app()->getLocale();
        $title = data_get($product->translations, $locale.'.title', data_get($product->translations, 'fa.title', 'محصول'));
        $description = data_get($product->translations, $locale.'.description', data_get($product->translations, 'fa.description', ''));
        $primaryMedia = $product->media->firstWhere('is_primary', true) ?? $product->media->first();
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | بازارگاه</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($description), 160) }}">
    <link rel="canonical" href="{{ route('products.show', $product->public_id) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@include('partials.storefront-header')

<main class="product-detail-shell">
    <nav class="breadcrumbs" aria-label="مسیر صفحه">
        <a href="{{ route('storefront.home') }}">خانه</a><span>/</span>
        @if($product->category)
            <span>{{ data_get($product->category->translations, $locale.'.name', data_get($product->category->translations, 'fa.name', $product->category->slug)) }}</span><span>/</span>
        @endif
        <span>{{ $title }}</span>
    </nav>

    <section class="product-detail">
        <div class="product-gallery">
            <div class="product-main-image">
                @if($primaryMedia)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($primaryMedia->storage_path) }}"
                         alt="{{ data_get($primaryMedia->alt_translations, $locale, $title) }}" id="main-product-image">
                @else
                    <div class="image-placeholder">بدون تصویر</div>
                @endif
            </div>
            @if($product->media->count() > 1)
                <div class="product-thumbnails" aria-label="تصاویر بیشتر محصول">
                    @foreach($product->media as $media)
                        <button type="button" data-gallery-image="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($media->storage_path) }}"
                                data-gallery-alt="{{ data_get($media->alt_translations, $locale, $title) }}"
                                aria-label="نمایش تصویر {{ $loop->iteration }}">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($media->storage_path) }}"
                                 alt="" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="product-detail-info">
            <span class="product-seller">فروشنده: {{ $product->seller->display_name }}</span>
            <h1>{{ $title }}</h1>
            @if($product->brand_name)<p class="product-brand">{{ $product->brand_name }}</p>@endif
            <div class="detail-price">{{ number_format($product->base_price_minor) }} <small>{{ $product->currency }}</small></div>
            <div class="product-status"><span></span> عرضه توسط فروشنده تأییدشده</div>
            @if($product->variants->isNotEmpty())
                <div class="variant-list">
                    <h2>گزینه‌های محصول</h2>
                    @foreach($product->variants as $variant)
                        <div class="variant-row">
                            <span>{{ $variant->title ?: $variant->sku }}</span>
                            <strong>{{ number_format($variant->price_minor ?? $product->base_price_minor) }} {{ $variant->currency }}</strong>
                        </div>
                    @endforeach
                </div>
            @endif
            <a class="primary-link" href="{{ route('cart.index') }}">رفتن به سبد خرید</a>
            <p class="seller-note">اطلاعات پرداخت و موجودی در مرحله تکمیل گردش‌کار خرید به این صفحه متصل خواهد شد.</p>
        </div>
    </section>

    @if($description)
        <section class="product-description">
            <h2>توضیحات محصول</h2>
            <div>{!! nl2br(e($description)) !!}</div>
        </section>
    @endif

    @if($relatedProducts->isNotEmpty())
        <section class="market-section related-section">
            <div class="section-heading"><div><span class="eyebrow">YOU MAY ALSO LIKE</span><h2>محصولات مرتبط</h2></div></div>
            <div class="product-grid">
                @foreach($relatedProducts as $related)
                    @php
                        $relatedTitle = data_get($related->translations, $locale.'.title', data_get($related->translations, 'fa.title', 'محصول'));
                        $relatedImage = $related->media->firstWhere('is_primary', true) ?? $related->media->first();
                    @endphp
                    <article class="product-card">
                        <a class="product-card-image" href="{{ route('products.show', $related->public_id) }}">
                            @if($relatedImage)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($relatedImage->storage_path) }}" alt="{{ data_get($relatedImage->alt_translations, $locale, $relatedTitle) }}" loading="lazy">
                            @else <span class="image-placeholder">بدون تصویر</span> @endif
                        </a>
                        <div class="product-card-body">
                            <span class="product-seller">{{ $related->seller->display_name }}</span>
                            <h3><a href="{{ route('products.show', $related->public_id) }}">{{ $relatedTitle }}</a></h3>
                            <strong class="product-price">{{ number_format($related->base_price_minor) }} <small>{{ $related->currency }}</small></strong>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</main>
@include('partials.storefront-footer')
</body>
</html>
