# تکمیل Preflight نصب روی هاست اشتراکی – بتا ۱۹

## پیش‌نیازهای اجباری
- PHP 8.2 یا بالاتر
- PDO و PDO MySQL
- cURL
- Mbstring
- OpenSSL
- Fileinfo، Ctype، Tokenizer، XML و DOM
- Filter، Hash، Session، PCRE و JSON
- دسترسی نوشتن در storage و bootstrap/cache
- vendor/autoload.php و فایل‌های Laravel

## توصیه‌های غیرمسدودکننده
- حافظه PHP حداقل 128MB
- زمان اجرای PHP حداقل 60 ثانیه
- سقف آپلود حداقل 20MB
- Intl برای پردازش locale و مرتب‌سازی چندزبانه
- OPcache برای کارایی بهتر

## دیتابیس
پس از دریافت اطلاعات اتصال، Setup نسخه سرور MySQL/MariaDB را با `SELECT VERSION()` تشخیص می‌دهد و در state نصب ثبت می‌کند. این مرحله فقط تشخیص نسخه است و جایگزین آزمون Migration و بررسی سازگاری نسخه روی میزبان واقعی نیست.

## تمامیت بسته
فایل `RELEASE-MANIFEST.sha256` شامل هش SHA-256 فایل‌های بسته است. اسکریپت `scripts/verify-release-manifest.py` برای بررسی عدم تغییر فایل‌ها در محیط دارای Python ارائه شده است.

## محدودیت
این نسخه همچنان فاقد وابستگی‌های واقعی Composer و `composer.lock` است؛ manifest به‌تنهایی بسته را نصب‌پذیر نمی‌کند.
