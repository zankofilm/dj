<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>افزودن محصول</title></head>
<body>
<main class="form-shell">
    <h1>افزودن محصول جدید</h1>
    @if ($errors->any()) <div class="notice error">{{ $errors->first() }}</div> @endif
    <form method="POST" enctype="multipart/form-data" action="{{ route('seller.products.store') }}">
        @csrf
        <input type="hidden" name="seller_id" value="{{ $seller->id }}">
        <label>دسته‌بندی</label>
        <select name="category_id"><option value="">بدون دسته‌بندی</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ data_get($category->translations, 'fa.title', $category->slug) }}</option>
            @endforeach
        </select>
        <label>عنوان فارسی محصول</label><input name="translations[fa][title]" required>
        <label>توضیحات فارسی</label><textarea name="translations[fa][description]"></textarea>
        <label>عنوان کوردی</label><input name="translations[ku][title]">
        <label>توضیحات کوردی</label><textarea name="translations[ku][description]"></textarea>
        <label>عنوان انگلیسی</label><input name="translations[en][title]">
        <label>توضیحات انگلیسی</label><textarea name="translations[en][description]"></textarea>
        <label>شناسه URL (لاتین)</label><input name="slug" required>
        <label>نوع محصول</label>
        <select name="product_type"><option value="physical">فیزیکی</option><option value="digital">دیجیتال</option><option value="service">خدمات</option></select>
        <label>وضعیت کالا</label>
        <select name="condition"><option value="new">نو</option><option value="used">دست‌دوم</option><option value="refurbished">بازسازی‌شده</option></select>
        <label>برند</label><input name="brand_name">
        <label>ارز (کد سه‌حرفی)</label><input name="currency" value="EUR" maxlength="3" required>
        <label>قیمت به کوچک‌ترین واحد ارز (مثلاً سنت)</label><input type="number" min="0" name="base_price_minor" required>
        <label>تصاویر محصول (حداکثر ۵ تصویر)</label>
        <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
        <small>فرمت‌های مجاز: JPG، PNG و WebP؛ حداکثر ۵ مگابایت برای هر تصویر. فایل SVG و فایل اجرایی پذیرفته نمی‌شود.</small>
        <button type="submit">ثبت برای بررسی</button>
    </form>
</main>
</body>
</html>
