# سخت‌سازی هاست اشتراکی و Document Root – بتا ۱۷

## حالت‌های استقرار
1. حالت پیشنهادی: Document Root دامنه روی پوشه `public/` تنظیم شود؛ فقط ورودی وب و فایل‌های عمومی در معرض وب قرار می‌گیرند.
2. حالت جایگزین: کل بسته داخل `public_html` قرار گیرد و قواعد ریشه درخواست‌ها را به `public/` هدایت کنند. این حالت نیازمند Apache/LiteSpeed سازگار و AllowOverride فعال است.

## دفاع چندلایه
برای پوشه‌های app، bootstrap، config، database، resources، routes، tests، vendor، scripts، docs، storage/framework، storage/logs، storage/app و bootstrap/cache فایل `.htaccess` با `Require all denied` اضافه شده است.
پوشه `storage/app/public` برای فایل‌های عمومی منتشرشده مجاز شده است. `public/.htaccess` نیز درخواست‌های برنامه را به front controller می‌فرستد و فایل‌های حساس را مسدود می‌کند.

## محدودیت وب‌سرور
`.htaccess` روی Apache و پیکربندی‌های سازگار LiteSpeed اثر دارد. در Nginx خالص نادیده گرفته می‌شود؛ در آن حالت Document Root باید روی `public/` باشد و میزبان باید دسترسی مسیرهای خصوصی را در تنظیمات سرور مسدود کند.

## تنظیمات منابع
Setup حافظه PHP، زمان اجرای درخواست و سقف آپلود را به‌صورت توصیه‌ای نشان می‌دهد؛ این موارد به‌تنهایی مانع نصب نیستند.

## آزمون‌های لازم روی هاست مقصد
- درخواست مستقیم به `/app/` و `/storage/logs/` باید 403/404 بدهد.
- `.env` و `composer.json` نباید قابل دریافت باشند.
- فایل‌های `storage/app/public` فقط در صورت انتشار عمومی قابل دسترسی باشند.
- پس از نصب، `/setup.php` باید قفل شود.
- حالت Document Root روی `public/` و حالت جایگزین روی Apache/LiteSpeed واقعی آزمایش شوند.
