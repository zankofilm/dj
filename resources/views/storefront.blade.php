<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() === 'en' ? 'Bazaar' : 'بازارگاه' }}</title>
    <meta name="description" content="{{ app()->getLocale() === 'en' ? 'Discover products from verified sellers.' : 'محصولات فروشندگان تأییدشده را در بازارگاه کشف کنید.' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app">
    @include('partials.storefront-header')

    <main class="market-main">
        <section class="hero-slider" data-hero-slider aria-label="بنرهای ویژه بازارگاه">
            <div class="hero-slides">
                @if($homepageBanners->isNotEmpty())
                    @foreach($homepageBanners as $banner)
                        @php
                            $bannerText = data_get($banner->translations, $locale, data_get($banner->translations, 'fa', []));
                            $bannerTitle = data_get($bannerText, 'title', 'بازارگاه');
                            $bannerSubtitle = data_get($bannerText, 'subtitle', '');
                            $bannerButton = data_get($bannerText, 'button', 'مشاهده');
                            $bannerImage = $banner->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) : null;
                        @endphp
                        <article class="hero-slide {{ $loop->first ? 'is-active' : '' }}">
                            @if($bannerImage)
                                <img class="hero-managed-image" src="{{ $bannerImage }}" alt="{{ $bannerTitle }}" fetchpriority="{{ $loop->first ? 'high' : 'auto' }}">
                            @endif
                            <div class="hero-copy">
                                <span class="eyebrow">MARKETPLACE</span>
                                @if($loop->first)<h1>{{ $bannerTitle }}</h1>@else<h2>{{ $bannerTitle }}</h2>@endif
                                @if($bannerSubtitle)<p>{{ $bannerSubtitle }}</p>@endif
                                @if($banner->target_url)
                                    <a class="primary-link" href="{{ $banner->target_url }}">{{ $bannerButton }}</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                @else
                    <article class="hero-slide is-active">
                        <div class="hero-copy"><span class="eyebrow">MARKETPLACE</span><h1>خریدی ساده، از فروشندگان معتبر</h1><p>محصولات فروشندگان تأییدشده را در یک بازارگاه چندفروشنده‌ای کشف کنید.</p><a class="primary-link" href="#latest-products">مشاهده جدیدترین‌ها</a></div>
                        <div class="hero-art hero-art-one" aria-hidden="true"><span>بازارگاه</span></div>
                    </article>
                    <article class="hero-slide">
                        <div class="hero-copy"><span class="eyebrow">DISCOVER MORE</span><h2>محصول مناسب خودت را پیدا کن</h2><p>در دسته‌بندی‌ها جست‌وجو کن و محصولات را بر اساس قیمت مرتب کن.</p><a class="primary-link" href="#catalog-search">جست‌وجوی محصولات</a></div>
                        <div class="hero-art hero-art-two" aria-hidden="true"><span>کشف کن</span></div>
                    </article>
                    <article class="hero-slide">
                        <div class="hero-copy"><span class="eyebrow">SPECIAL OFFERS</span><h2>فرصت‌های خرید را از دست نده</h2><p>محصولات دارای قیمت قبل و بعد از تخفیف را در بخش پیشنهادها ببین.</p><a class="primary-link" href="#discount-products">مشاهده تخفیف‌ها</a></div>
                        <div class="hero-art hero-art-three" aria-hidden="true"><span>پیشنهاد ویژه</span></div>
                    </article>
                @endif
            </div>
            <div class="hero-controls">
                <button type="button" data-hero-prev aria-label="بنر قبلی">‹</button>
                <div class="hero-dots" aria-label="انتخاب بنر">
                    @php($heroCount = $homepageBanners->isNotEmpty() ? $homepageBanners->count() : 3)
                    @for($dot = 0; $dot < $heroCount; $dot++)
                        <button type="button" class="{{ $dot === 0 ? 'is-active' : '' }}" data-hero-dot="{{ $dot }}" aria-label="بنر {{ $dot + 1 }}"></button>
                    @endfor
                </div>
                <button type="button" data-hero-next aria-label="بنر بعدی">›</button>
            </div>
        </section>

        @if($categories->isNotEmpty())
            <section class="market-section">
                <div class="section-heading">
                    <div><span class="eyebrow">DISCOVER</span><h2>{{ $locale === 'en' ? 'Featured categories' : ($locale === 'ku' ? 'بەشە هەڵبژێردراوەکان' : 'دسته‌بندی‌های منتخب') }}</h2></div>
                </div>
                <div class="category-chips">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}">
                            {{ data_get($category->translations, $locale.'.name', data_get($category->translations, 'fa.name', $category->slug)) }}
                            <small>{{ $category->products_count }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($featuredProducts->isNotEmpty())
            <section class="market-section featured-products-section">
                <div class="section-heading">
                    <div><span class="eyebrow">EDITOR'S PICKS</span><h2>{{ $locale === 'en' ? 'Featured products' : ($locale === 'ku' ? 'بەرهەمە هەڵبژێردراوەکان' : 'محصولات منتخب') }}</h2></div>
                    <span class="section-note">{{ $locale === 'en' ? 'Curated by marketplace team' : ($locale === 'ku' ? 'هەڵبژێردراو لەلایەن تیمی بازاڕگا' : 'انتخاب‌شده توسط مدیریت بازارگاه') }}</span>
                </div>
                <div class="product-grid">
                    @foreach($featuredProducts as $cardProduct)
                        @include('partials.storefront-product-card', ['cardProduct' => $cardProduct, 'locale' => $locale])
                    @endforeach
                </div>
            </section>
        @endif

        <section class="catalog-filter-panel" id="catalog-search" aria-label="جست‌وجو و فیلتر محصولات">
            <form method="GET" action="{{ route('storefront.home') }}" class="catalog-filters">
                <label class="search-field">
                    <span>جست‌وجوی محصول</span>
                    <input type="search" name="q" value="{{ request('q') }}" maxlength="120" placeholder="نام محصول، برند یا عبارت جست‌وجو">
                </label>
                <label>
                    <span>فروشنده</span>
                    <select name="seller">
                        <option value="">همه فروشندگان</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" @selected((string) request('seller') === (string) $seller->id)>{{ $seller->display_name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>حداقل قیمت (واحد پایه)</span>
                    <input type="number" name="min_price" min="0" value="{{ request('min_price') }}">
                </label>
                <label>
                    <span>حداکثر قیمت (واحد پایه)</span>
                    <input type="number" name="max_price" min="0" value="{{ request('max_price') }}">
                </label>
                <label>
                    <span>مرتب‌سازی</span>
                    <select name="sort">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>جدیدترین</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>ارزان‌ترین</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>گران‌ترین</option>
                    </select>
                </label>
                <div class="filter-actions">
                    <button type="submit" class="primary-link">اعمال فیلتر</button>
                    <a href="{{ route('storefront.home') }}">پاک‌کردن</a>
                </div>
            </form>
        </section>


        @php
            $shelfDefinitions = [
                'latest' => ['id' => 'latest-shelf', 'items' => $latestProducts, 'direction' => 'both'],
                'popular' => ['id' => 'popular-shelf', 'items' => $popularProducts, 'direction' => 'ltr'],
                'cheapest' => ['id' => 'cheapest-shelf', 'items' => $cheapestProducts, 'direction' => 'ltr'],
                'discount' => ['id' => 'discount-products', 'items' => $discountedProducts, 'direction' => 'ltr'],
            ];
            $shelves = collect($shelfDefinitions)->map(function ($definition, $key) use ($shelfSettings, $locale) {
                $setting = $shelfSettings->get($key);
                if (!$setting || !$setting->is_active) return null;
                return [
                    ...$definition,
                    'title' => $setting->titleFor($locale),
                    'eyebrow' => $setting->eyebrowFor($locale),
                    'speed' => $setting->motion_speed,
                    'sort_order' => $setting->sort_order,
                ];
            })->filter()->sortBy('sort_order');
        @endphp
        @foreach($shelves as $shelf)
            <section class="market-section product-shelf" id="{{ $shelf['id'] }}">
                <div class="section-heading">
                    <div><span class="eyebrow">{{ $shelf['eyebrow'] }}</span><h2>{{ $shelf['title'] }}</h2></div>
                    <div class="shelf-controls">
                        <button type="button" data-shelf-prev aria-label="محصولات قبلی">‹</button>
                        <button type="button" data-shelf-next aria-label="محصولات بعدی">›</button>
                    </div>
                </div>
                @if($shelf['items']->isNotEmpty())
                    <div class="shelf-viewport" data-product-shelf data-motion="{{ $shelf['direction'] }}" data-speed="{{ $shelf['speed'] }}">
                        <div class="shelf-track">
                            @foreach($shelf['items'] as $cardProduct)
                                @include('partials.storefront-product-card', ['cardProduct' => $cardProduct, 'locale' => $locale])
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="shelf-empty">
                        @if($shelf['id'] === 'popular-shelf')
                            هنوز سفارش پرداخت‌شده‌ای برای تعیین محبوب‌ترین‌ها ثبت نشده است.
                        @elseif($shelf['id'] === 'discount-products')
                            در حال حاضر محصولی با قیمت تخفیفی معتبر وجود ندارد.
                        @else
                            محصولی برای نمایش وجود ندارد.
                        @endif
                    </div>
                @endif
            </section>
        @endforeach

        <section class="market-section" id="latest-products">
            <div class="section-heading">
                <div><span class="eyebrow">JUST ARRIVED</span><h2>محصولات بازارگاه</h2></div>
                <span class="section-note">{{ $products->total() }} محصول</span>
            </div>

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
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($primaryMedia->storage_path) }}"
                                         alt="{{ data_get($primaryMedia->alt_translations, $locale, $title) }}"
                                         loading="lazy" width="480" height="480">
                                @else
                                    <span class="image-placeholder">بدون تصویر</span>
                                @endif
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
                <div class="empty-catalog">
                    <span aria-hidden="true">✦</span>
                    <h3>هنوز محصولی برای نمایش منتشر نشده است</h3>
                    <p>پس از تأیید و انتشار محصولات فروشندگان، آن‌ها در این بخش نمایش داده می‌شوند.</p>
                </div>
            @endif
        </section>
    </main>

    @include('partials.storefront-footer')
</div>
</body>
</html>
