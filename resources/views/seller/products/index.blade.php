<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>محصولات من</title></head>
<body>
<main class="form-shell">
    <h1>محصولات فروشگاه {{ $seller->display_name }}</h1>
    @if(session('status')) <div class="notice">{{ session('status') }}</div> @endif
    <p><a href="{{ route('seller.products.create') }}">افزودن محصول جدید</a></p>
    @forelse($products as $product)
        <section class="seller-row">
            <h2>{{ data_get($product->translations, app()->getLocale().'.title', data_get($product->translations, 'fa.title')) }}</h2>
            <p>شناسه: {{ $product->public_id }} | وضعیت: {{ $product->status }}</p>
            <p>{{ number_format($product->base_price_minor) }} {{ $product->currency }}</p>
            @if($product->media->isNotEmpty())
                <p>برای مرتب‌سازی، تصاویر را بکشید و جابه‌جا کنید؛ سپس ذخیره ترتیب را بزنید.</p>
                    <ol class="media-gallery" data-sortable-gallery>
                        @foreach($product->media as $media)
                            <li draggable="true" data-media-id="{{ $media->id }}">
                                <figure>
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($media->storage_path) }}"
                                         alt="{{ data_get($media->alt_translations, app()->getLocale(), 'تصویر محصول') }}"
                                         loading="lazy" width="120" height="120">
                                    <span class="drag-handle" aria-hidden="true">⠿ جابه‌جایی</span>
                                    <div class="media-order-controls" aria-label="کنترل ترتیب تصویر">
                                        <button type="button" data-move-up>بالا</button>
                                        <button type="button" data-move-down>پایین</button>
                                    </div>
                                    @if($media->is_primary)<strong>تصویر اصلی</strong>@else
                                        <form method="POST" action="{{ route('seller.products.media.primary', [$product, $media]) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit">انتخاب تصویر اصلی</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('seller.products.media.alt', [$product, $media]) }}">
                                        @csrf @method('PATCH')
                                        <label>متن جایگزین فارسی</label>
                                        <input name="alt_translations[fa]" maxlength="250" value="{{ data_get($media->alt_translations, 'fa', '') }}">
                                        <label>متن جایگزین کوردی</label>
                                        <input name="alt_translations[ku]" maxlength="250" value="{{ data_get($media->alt_translations, 'ku', '') }}">
                                        <label>Alt text English</label>
                                        <input name="alt_translations[en]" maxlength="250" value="{{ data_get($media->alt_translations, 'en', '') }}">
                                        <button type="submit">ذخیره متن‌ها</button>
                                    </form>
                                    <form method="POST" action="{{ route('seller.products.media.destroy', [$product, $media]) }}"
                                          onsubmit="return confirm('تصویر حذف شود؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit">حذف تصویر</button>
                                    </form>
                                </figure>
                            </li>
                        @endforeach
                    </ol>
                <form method="POST" action="{{ route('seller.products.media.reorder', $product) }}" class="media-sort-form" data-sort-form>
                    @csrf @method('PATCH')
                    <div data-order-inputs>
                        @foreach($product->media as $media)
                            <input type="hidden" name="media_ids[]" value="{{ $media->id }}">
                        @endforeach
                    </div>
                    <button type="submit">ذخیره ترتیب تصاویر</button>
                </form>
            @else
                <p>برای این محصول تصویری ثبت نشده است.</p>
            @endif
        </section>
    @empty
        <p>هنوز محصولی ثبت نشده است.</p>
    @endforelse
    {{ $products->links() }}
</main>
</body>
</html>
