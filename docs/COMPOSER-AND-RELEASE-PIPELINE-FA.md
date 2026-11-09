# اصلاح Composer و خط ساخت انتشار – بتا ۷

## اصلاحات
1. کلیدهای PSR-4 در `composer.json` به شکل استاندارد تک‌پسوندی اصلاح شدند:
   - `App\\` به مسیر `app/`
   - `Database\\Factories\\` به مسیر Factoryها
   - `Database\\Seeders\\` به مسیر Seederها
   - `Tests\\` به مسیر تست‌ها
2. نام کلاس Composer Script مربوط به Laravel اصلاح شد.
3. Import سه کنترلر داشبورد، کاربران و کاتالوگ به `routes/web.php` اضافه شد.
4. اسکریپت Build انتشار اکنون `composer validate`، نصب production dependencyها، کنترل وجود `vendor/autoload.php`، تولید APP_KEY و اعتبارسنجی ZIP را انجام می‌دهد.
5. گردش‌کار GitHub Actions برای اجرای آزمون‌ها و ساخت ZIP دارای vendor اضافه شد.

## نحوه تولید بسته قابل نصب
روی مخزن Git، یک tag مانند `v1.0.0-beta.7` ایجاد و push کنید یا Workflow را دستی اجرا کنید. در صورت موفقیت تست‌ها و Build، فایل ZIP در بخش Actions به‌عنوان Artifact قابل دریافت خواهد بود.

## محدودیت فعلی
در محیط فعلی Composer نصب نیست و دسترسی نصب وابستگی‌ها وجود ندارد؛ بنابراین این نوبت نمی‌توان vendor واقعی ساخت یا موفقیت Runtime را ادعا کرد. Workflow نیز تا اجرای واقعی در مخزن Git، صرفاً پیکربندی آماده اجراست.

## هشدار
این اصلاحات مشکلات آشکار manifest و import را برطرف می‌کنند، اما تأیید نهایی نیازمند اجرای Composer، تست‌ها، migration روی دیتابیس خالی، بررسی Routeها و نصب واقعی روی cPanel/DirectAdmin است.
