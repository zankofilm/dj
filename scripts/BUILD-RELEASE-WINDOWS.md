# ساخت بسته نصب در ویندوز

برای ساخت بسته مستقل نصب، روی یک رایانه Build با PHP 8.2+، Composer 2 و ابزار zip کار کنید:

1. سورس را استخراج کنید.
2. در ریشه پروژه PowerShell را باز کنید.
3. `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader` را اجرا کنید.
4. `.env.example` را به `.env` کپی کنید.
5. `php artisan key:generate --force` را اجرا کنید.
6. پوشه‌های `storage/framework`، `storage/logs` و `bootstrap/cache` را ایجاد کنید.
7. پوشه `vendor` را همراه پروژه در بسته انتشار قرار دهید.
8. فایل ZIP خروجی را روی محیط آزمایشی نصب کنید و تمام آزمون‌های release gate را بگذرانید.

فایل ZIP سورس توسعه، بدون `vendor`، بسته نصب مستقل محسوب نمی‌شود. این سند عمداً تضمین نمی‌کند که بسته بدون آزمایش روی همه نسخه‌های cPanel یا DirectAdmin اجرا خواهد شد.
