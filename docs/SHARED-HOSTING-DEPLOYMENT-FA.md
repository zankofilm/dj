# راهنمای نصب روی هاست لینوکس اشتراکی

## وضعیت این بسته
این خروجی یک نسخه بتای نصب CLI برای بررسی فنی است؛ نصب‌گر تحت وب گرافیکی هنوز ارائه نشده است.
برای اجرای Laravel به PHP و وابستگی‌های Composer نیاز است. فایل `vendor/` عمداً در بسته سورس نیست؛
اگر هاست SSH/Terminal یا امکان اجرای Composer ندارد، باید بسته Release با vendor از محیط Build تهیه شود.
بدون vendor اجرای برنامه ممکن نیست.

## پیش‌نیازها
- PHP 8.2 یا بالاتر
- MySQL 8 یا MariaDB سازگار با نسخه Laravel انتخاب‌شده
- افزونه‌های pdo_mysql، mbstring، openssl، tokenizer، xml، ctype، json و fileinfo
- Composer 2 برای نصب وابستگی‌ها، یا Release artifact از پیش ساخته‌شده
- امکان تنظیم Document Root روی پوشه `public`
- دسترسی نوشتن محدود برای `storage/` و `bootstrap/cache/`

## مراحل پیشنهادی
1. یک دیتابیس و کاربر اختصاصی در کنترل‌پنل هاست بسازید.
2. سورس را خارج از مسیر عمومی وب قرار دهید؛ فقط محتوای `public/` باید Document Root باشد.
3. فایل `.env.shared-hosting.example` را به `.env` کپی و اطلاعات دیتابیس و دامنه را تنظیم کنید.
4. در محیط Build یا ترمینال هاست `composer install --no-dev --prefer-dist --optimize-autoloader` را اجرا کنید.
5. کلید برنامه را با `php artisan key:generate` بسازید.
6. `php artisan marketplace:preflight` را اجرا کنید.
7. `php artisan marketplace:install` را اجرا و حساب مدیر را بسازید.
8. دسترسی پوشه‌های `storage` و `bootstrap/cache` را فقط به اندازه لازم تنظیم کنید.
9. APP_DEBUG را خاموش و HTTPS را فعال نگه دارید.
10. پس از نصب، وجود `storage/app/installed.lock` را بررسی کنید.

## اگر SSH یا Composer در هاست وجود ندارد
از میزبان بخواهید Composer 2 و PHP CLI را فعال کند یا بسته Release شامل `vendor/` را از محیط Build تحویل بگیرید.
آپلود فایل سورس بدون vendor برای اجرای Laravel کافی نیست.
از قراردادن کل پروژه در `public_html` و قابل دسترس کردن `.env`، `vendor`، `storage` یا فایل‌های پشتیبان خودداری کنید.

## محدودیت‌های نسخه بتا
- Setup تحت وب و فارسی هنوز آماده نیست.
- زمان‌بندی Queue و Scheduler به Cron هاست نیاز دارد؛ Worker دائمی فرض نشده است.
- هیچ PSP یا شرکت حمل‌ونقل واقعی متصل نیست.
- آزمون نصب روی cPanel/DirectAdmin واقعی انجام نشده است.
