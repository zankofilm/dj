# اصلاح خط انتشار و بسته نصب هاست اشتراکی – بتا ۴۰

## تغییرات این مرحله

1. گردش‌کار GitHub Actions روی PHP 8.5 اجرا می‌شود.
2. در CI ابتدا `composer update` اجرا می‌شود تا `composer.lock` واقعی و وابستگی‌های قطعی ساخته شوند؛ سپس آزمون‌ها اجرا می‌شوند.
3. مرحله انتشار از همان lock file برای نصب production dependencies استفاده می‌کند.
4. فایل `.env.example` و `.env.shared-hosting.example` در مرحله staging حفظ می‌شوند.
5. فایل `.env` واقعی هرگز وارد ZIP انتشار نمی‌شود و APP_KEY در زمان نصب برای هر فروشگاه به‌صورت تصادفی ساخته می‌شود.
6. ZIP در ریشه پروژه بسته‌بندی می‌شود؛ پس از Extract مستقیم در `public_html/shop` مسیر `public_html/shop/setup.php` وجود خواهد داشت.
7. قبل از انتشار وجود `vendor/autoload.php`، فایل Laravel Framework، `.env.example`، `composer.lock` و `setup.php` کنترل می‌شود.
8. manifest هش SHA-256 برای محتویات دقیق بسته، شامل vendor، تولید و بررسی می‌شود.

## اجرای ساخت بدون ترمینال هاست

1. مخزن GitHub را Private نگه دارید.
2. تمام محتوای ZIP سورس را در ریشه مخزن بارگذاری کنید؛ فایل‌های مخفی `.env.example` و `.github/workflows/build-release.yml` را نیز نگه دارید.
3. در Actions گردش‌کار `Build and verify marketplace PHP 8.5 release` را با `Run workflow` اجرا کنید.
4. فقط در صورت سبز شدن همه مراحل، Artifact با نام `marketplace-foundation-1.0.0-beta.40-php85-shared-hosting` را دریافت کنید.
5. ZIP داخل Artifact، بسته نصب است؛ نه ZIP سورس build-source.
6. پیش از جایگزینی فایل‌های هاست از کل `public_html/shop` و دیتابیس نسخه پشتیبان بگیرید.
7. محتوای ZIP نصب را مستقیماً در `public_html/shop` استخراج کنید و سپس `/shop/setup.php` را باز کنید.

## مرز تأیید

این بسته، خط ساخت و کنترل‌ها را اصلاح می‌کند؛ اما تا زمانی که Workflow در مخزن GitHub اجرا و Artifact تولید نشود، vendor نهایی ساخته‌شده و نصب روی هاست تأییدشده محسوب نمی‌شود. اگر Workflow شکست خورد، گزارش همان مرحله باید بررسی شود و نباید از ZIP سورس به‌عنوان بسته نصب استفاده کرد.
