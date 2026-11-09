@php
    $cardTitle = data_get($cardProduct->translations, $locale.'.title', data_get($cardProduct->translations, 'fa.title', 'محصول'));
    $cardMedia = $cardProduct->media->firstWhere('is_primary', true) ?? $cardProduct->media->first();
    $cardVariant = $cardProduct->variants->first();
    $cardPrice = $cardVariant?->price_minor ?? $cardProduct->base_price_minor;
    $cardCurrency = $cardVariant?->currency ?? $cardProduct->currency;
    $cardCompareAt = $cardVariant?->compare_at_price_minor;
    $cardHasDiscount = $cardCompareAt !== null && (int) $cardCompareAt > (int) $cardPrice;
    $cardDiscountPercent = $cardHasDiscount ? (int) floor((($cardCompareAt - $cardPrice) / $cardCompareAt) * 100) : 0;
@endphp
<article class="product-card">
    <a class="product-card-image" href="{{ route('products.show', $cardProduct->public_id) }}">
        @if($cardHasDiscount)<span class="product-discount-badge">{{ $cardDiscountPercent }}٪ تخفیف</span>@endif
        @if($cardMedia)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($cardMedia->storage_path) }}"
                 alt="{{ data_get($cardMedia->alt_translations, $locale, $cardTitle) }}"
                 loading="lazy" width="480" height="480">
        @else
            <span class="image-placeholder">بدون تصویر</span>
        @endif
    </a>
    <div class="product-card-body">
        <span class="product-seller">{{ $cardProduct->seller->display_name }}</span>
        <h3><a href="{{ route('products.show', $cardProduct->public_id) }}">{{ $cardTitle }}</a></h3>
        <strong class="product-price">
            {{ number_format($cardPrice) }} <small>{{ $cardCurrency }}</small>
            @if($cardHasDiscount)<del class="product-compare-price">{{ number_format($cardCompareAt) }}</del>@endif
        </strong>
        <a class="product-open" href="{{ route('products.show', $cardProduct->public_id) }}">مشاهده محصول</a>
    </div>
</article>
