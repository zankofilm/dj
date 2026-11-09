# Marketplace Foundation 0.2.0

This cumulative source package advances the shared-hosting-first Laravel foundation with:
- Laravel application configuration for MySQL/MariaDB, database cache, queue and sessions
- Initial user, role, permission and pivot tables
- Initial administrator CLI installer command (`php artisan marketplace:install`)
- Locale selection middleware for `fa`, `ku`, and `en`
- Runtime tables for database-backed queue/cache/session
- Seeded initial platform permissions and system administrator role

## Important status
This is still a foundation milestone, not a finished marketplace. It is not yet safe to process live payments,
seller settlements, or untrusted executable extensions. Package signature verification, actual runtime isolation,
full admin authentication screens, seller onboarding, financial posting services, and visual builder are future milestones.

## Requirements
- PHP 8.2+
- Composer 2
- MySQL 8.0+ or compatible MariaDB
- PHP extensions: BCMath, Ctype, cURL, DOM, Fileinfo, Intl, Mbstring, OpenSSL, PDO, Tokenizer, XML
- Web root must point to `public/`
- SSH/Composer preferred; otherwise build dependencies in CI and upload the resulting release artifact

## Install
1. `composer install`
2. Copy `.env.example` to `.env`; configure database and APP_URL.
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. Build frontend assets: `npm install && npm run build`
6. Run `php artisan marketplace:install` to perform the guarded baseline install and create the first administrator.
7. Point the web server document root to `public/`.

The CLI installer refuses to run when `storage/app/installed.lock` exists. It does not expose a public unauthenticated installation endpoint.

## Shared hosting
Build Composer and frontend dependencies locally or in CI if the hosting plan does not provide SSH/Composer/Node.
Keep application source, `.env`, `vendor/`, `storage/`, and backups outside the public web root.
Do not upload executable extension packages into a web-executable directory.

## Architecture
See `docs/ARCHITECTURE-BASELINE.md`, `docs/SECURITY-BASELINE.md`, and `docs/INSTALLER.md`.


## Version 0.3.0 additions
- Seller onboarding data model and application workflow foundation
- Seller review actions protected by a permission check
- Audit records for submission and review
- Persian, Sorani Kurdish (`ku`), and English seller UI translations
- Seller application/status and admin review views

See `docs/SELLER-ONBOARDING.md`. Authentication screens, password reset, secure document upload,
and full integration/security testing are still pending. Do not use this milestone for live seller
verification or financial operations.


## Version 0.4.0 additions
- Hierarchical categories and multilingual catalog fields
- Seller-owned product submission workflow
- Product types and condition fields
- Product variants, SKU/barcode and localized media metadata
- Inventory balances and append-only movement schema
- Product creation audit record
- Persian-first seller catalog screens

See `docs/CATALOG-INVENTORY.md`. Variant management, media upload, stock mutation/reservation, moderation,
and concurrency tests are not yet implemented. Do not use this milestone for production inventory.


## Version 0.5.0 additions
- Authenticated persistent cart
- Multi-seller parent order and seller suborders
- Product and commercial snapshots on order items
- Transactional stock reservation with row locks and movement records
- Persian-first cart, checkout, and order summary screens

Payment processing, tax/shipping/discount calculations, reservation expiry, checkout idempotency,
automated concurrency tests, and production validation remain pending. Do not accept live payments.
See `docs/CART-ORDER-CHECKOUT.md`.


## نسخه ۰.۶.۰
زیرساخت انتزاعی پرداخت، Payment Intent، تلاش‌ها، Idempotency، Webhook و تأیید سروربه‌سرور اضافه شد.
هیچ درگاه واقعی فعال نیست. بازپرداخت، Chargeback، تطبیق PSP، دفتر کل، Outbox و تست Sandbox باقی مانده‌اند.
برای جزئیات به `docs/PAYMENT-ARCHITECTURE.md` مراجعه کنید.


## نسخه ۰.۷.۰
دفتر کل دوطرفه با حساب‌ها، اسناد، ردیف‌های بدهکار/بستانکار، کلید یکتای رویداد و اصلاح جبرانی اضافه شد.
اتصال خودکار رویدادهای پرداخت، کمیسیون، مالیات، بازپرداخت، تسویه، گزارش مانده و آزمون حسابداری هنوز باقی است.
جزئیات در `docs/LEDGER-ARCHITECTURE.md`.


## نسخه ۰.۸.۰
زیرساخت تعهد قابل پرداخت فروشنده، مقصد تسویه رمزنگاری‌شده، درخواست تسویه تجمیعی و تأیید مستقل اضافه شد.
انتقال وجه واقعی، اتصال دفتر کل، سیاست کمیسیون نسخه‌دار، تطبیق بانکی و تست‌های تولید باقی مانده‌اند.
جزئیات در `docs/TREASURY-SETTLEMENT-ARCHITECTURE.md`.


## نسخه ۰.۹.۰
پایه چندمرسوله‌ای، قرارداد شرکت حمل، رهگیری و پرونده مرجوعی اضافه شد.
هیچ Adapter واقعی حمل یا بازپرداخت PSP فعال نیست. دریافت فیزیکی مرجوعی، restock، نرخ‌گذاری حمل،
اتصال دفتر کل، تست Sandbox و RBAC نهایی باقی مانده‌اند.
جزئیات در `docs/SHIPPING-RETURNS-ARCHITECTURE.md`.


## نسخه ۱.۰.۰-beta.1
این بتا بر آماده‌سازی نصب روی هاست اشتراکی تمرکز دارد:
- ثبت Service Providerهای پرداخت و حمل‌ونقل
- اصلاح Seeder حساب‌های دفتر کل
- دستور بررسی پیش‌نیاز `php artisan marketplace:preflight`
- الگوی `.env.shared-hosting.example`
- راهنمای فارسی استقرار و فهرست دروازه انتشار

این نسخه نصب‌گر تحت وب ندارد، vendor در ZIP سورس قرار نگرفته و برای عملیات واقعی آماده نیست.


## نسخه ۱.۰.۰-beta.2
نصب‌گر تحت وب فارسی در مسیر `/setup` اضافه شد. این نسخه آزمایشی است؛ پیش از استفاده واقعی باید نصب از دیتابیس خالی، خطاهای نیمه‌کاره، CSRF/Rate Limit، مجوز فایل‌ها و نصب روی cPanel/DirectAdmin آزمون شود.


## نسخه ۱.۰.۰-beta.3
پنل مدیریت اولیه شامل داشبورد، فهرست کاربران، مدیریت وضعیت حساب، فهرست محصولات و مدیریت وضعیت کاتالوگ اضافه شد. ماژول سفارش، مالی، CMS، قالب و افزونه هنوز تکمیل نشده‌اند. جزئیات در `docs/ADMIN-PANEL-BETA3.md`.


## نسخه ۱.۰.۰-beta.4
آماده‌سازی فرایند ساخت بسته نصب مستقل:
- اسکریپت `scripts/build-release.sh` برای Build روی محیط دارای PHP و Composer
- راهنمای Build ویندوز
- غیرفعال شدن دکمه نصب در صورت ناقص بودن پیش‌نیازهای Setup
- مستند تفکیک Source ZIP و Installable ZIP

توجه: محیط ساخت این خروجی Composer نداشت؛ بنابراین این ZIP هنوز Source package است و vendor ندارد.


## نسخه ۱.۰.۰-beta.5
فایل مستقل `public/setup.php` اضافه شد. مسیر نصب با Document Root روی `public` برابر `/setup.php` است. صفحه بدون Laravel Router بالا می‌آید، اما نصب کامل همچنان به `vendor` تولیدشده توسط Composer نیاز دارد. این بسته به دلیل نبود vendor هنوز نصب مستقل کامل نیست.


## نسخه ۱.۰.۰-beta.6
برای هاست اشتراکی، `setup.php` و `index.php` و `.htaccess` در ریشه بسته قرار گرفتند. در حالت استخراج در `public_html` مسیر نصب `/setup.php` است. بسته همچنان vendor ندارد و نصب کامل مسدود است.


## نسخه ۱.۰.۰-beta.7
- اصلاح PSR-4 Composer autoload و Composer script
- تکمیل import کنترلرهای پنل مدیریت در Routeها
- افزودن GitHub Actions برای تست و تولید artifact دارای vendor
- سخت‌گیری بیشتر در اسکریپت Build و اعتبارسنجی ZIP

این ZIP همچنان سورس توسعه است؛ Composer در محیط فعلی نصب نیست و vendor تولید نشده است.


## نسخه ۱.۰.۰-beta.8
نصب‌کننده مستقل سخت‌تر شد: قفل اجرای هم‌زمان، محدودیت تلاش IP، پیش‌نیازهای PHP کامل‌تر و امکان ادامه نصب نیمه‌تمام با تطبیق fingerprint دیتابیس. این بسته همچنان بدون vendor است و نصب کامل آن در هاست تأیید نشده است.


## نسخه ۱.۰.۰-beta.9
سخت‌سازی ورودی‌های دیتابیس، نوشتن امن مقادیر `.env`، ثبت مرحله نصب و بازیابی حساب مدیر با همان ایمیل در نصب نیمه‌تمام اضافه شد. همچنان vendor و آزمون Runtime واقعی موجود نیست.


## نسخه ۱.۰.۰-beta.10
در ادامه نصب نیمه‌تمام، APP_KEY معتبر حفظ می‌شود و مشخصات سایت و ایمیل مدیر باید ثابت بمانند. لینک موفقیت نصب اصلاح شد و Smoke Test مستقل نمایش صفحه Setup اضافه شد.


## نسخه ۱.۰.۰-beta.11
دو تعارض مسدودکننده Migration برطرف شد: ایجاد تکراری جدول sellers و ledger_accounts. ابزار بررسی برخورد نام جدول‌ها و گزارش ممیزی دیتابیس اضافه شد. اجرای واقعی روی MySQL هنوز لازم است.


## نسخه ۱.۰.۰-beta.12
روابط Eloquent با ساختار واقعی جداول تطبیق داده شد؛ به‌ویژه رابطه چندبه‌چند تسویه و پرداخت فروشنده. ممیزی ایستای مدل‌ها و Migrationها اضافه شد.


## نسخه ۱.۰.۰-beta.13
مسیر نمایش وضعیت درخواست فروشندگی اضافه شد، ثبت درخواست فروشندگی محدود و در برابر ثبت هم‌زمان تکراری مقاوم‌تر شد، حفاظت از آخرین مدیر کل فعال افزوده شد و نوشتن مقادیر پیچیده در .env امن‌تر شد.


## نسخه ۱.۰.۰-beta.14
تقویت idempotency و تشخیص بازپخش دستکاری‌شده Webhook پرداخت، Claim پردازش رویداد و جلوگیری از عقب‌گرد وضعیت مرسوله در callback دیررس.


## نسخه ۱.۰.۰-beta.15
فرمان بازیابی Webhookهای پرداخت نیمه‌تمام اضافه شد. رویدادهای processing قدیمی پس از بازبینی مجدد قفل سطری به needs_review منتقل می‌شوند؛ از اجرای مجدد خودکار پرداخت بدون بازاعتبارسنجی امضای Provider جلوگیری شده است.


## نسخه ۱.۰.۰-beta.16
تمرکز بر استقرار بدون Terminal در هاست اشتراکی: بررسی‌های Setup کامل‌تر شد و راهنمای فارسی File Manager، چیدمان امن public/private و دروازه انتشار ZIP دارای vendor اضافه شد. این بسته همچنان vendor ندارد و نصب کامل نیست.


## نسخه ۱.۰.۰-beta.17
قواعد امنیتی پوشه‌های خصوصی، پشتیبانی از Document Root روی public، حفاظت مسیر storage و راهنمای سخت‌سازی هاست اشتراکی اضافه شد. Setup اکنون تنظیمات منابع PHP را به‌صورت توصیه‌ای نمایش می‌دهد. بسته همچنان vendor ندارد و نصب کامل نیست.


## نسخه ۱.۰.۰-beta.18
ورودی `public/setup.php` اضافه شد تا مسیر نصب `/setup.php` در حالت Document Root روی پوشه public نیز به نصب‌کننده اصلی در ریشه بسته برسد.


## نسخه ۱.۰.۰-beta.19
Preflight هاست با بررسی cURL تکمیل شد؛ Intl و OPcache به‌عنوان توصیه نمایش داده می‌شوند؛ نسخه MySQL/MariaDB پس از اتصال تشخیص داده می‌شود؛ manifest هش SHA-256 برای کنترل تمامیت فایل‌ها اضافه شد.


## نسخه ۱.۰.۰-beta.20
مرز اعطای نقش super-admin در بازیابی نصب سخت‌گیرانه شد؛ ایمیل مدیر normalize می‌شود و وضعیت نصب با نوشتن اتمیک و کنترل خطا ذخیره می‌شود.


## نسخه ۱.۰.۰-beta.21
کنترل مجوز سمت سرور برای پنل مدیریت اضافه شد: دسترسی کلی مدیر، داشبورد، مدیریت کاربران، کاتالوگ و بررسی فروشندگان با RBAC کنترل می‌شوند.


## نسخه ۱.۰.۰-beta.22
آپلود امن تصاویر محصول اضافه شد: حداکثر ۵ تصویر ۵ مگابایتی در فرمت‌های JPG/PNG/WebP، ذخیره‌سازی با نام تصادفی، ثبت ProductMedia، پاک‌سازی فایل در شکست تراکنش و محدودیت نرخ درخواست.


## نسخه ۱.۰.۰-beta.23
مدیریت گالری تصاویر فروشنده اضافه شد: نمایش گالری، انتخاب تصویر اصلی، حذف امن تصویر، جایگزینی خودکار تصویر اصلی حذف‌شده و ثبت AuditLog.


## نسخه ۱.۰.۰-beta.24
ویرایش متن جایگزین تصاویر به سه زبان fa/ku/en و مرتب‌سازی Drag & Drop گالری با کنترل‌های بالا/پایین، اعتبارسنجی دقیق شناسه‌ها و ثبت AuditLog اضافه شد.


## نسخه ۱.۰.۰-beta.25
صفحه فروشگاه واقعی و صفحه جزئیات محصول اضافه شد: فهرست محصولات منتشرشده فروشندگان تأییدشده، کارت محصول، گالری تصاویر، متن‌های چندزبانه، محصولات مرتبط و متادیتای پایه SEO.


## نسخه ۱.۰.۰-beta.26
آرشیو مستقل دسته‌بندی، جست‌وجوی اولیه محصول، فیلتر فروشنده و بازه قیمت، مرتب‌سازی جدیدترین/ارزان‌ترین/گران‌ترین و حفظ فیلترها در صفحه‌بندی اضافه شد.


## نسخه ۱.۰.۰-beta.27
هوم‌پیج با اسلایدر سه‌اسلایدی و چهار نوار جدیدترین، محبوب‌ترین مبتنی بر سفارش پرداخت‌شده، ارزان‌ترین و تخفیف‌های مبتنی بر compare-at price تکمیل شد. نوار جدیدترین حرکت خودکار دوطرفه و کنترل دستی دارد.


## نسخه ۱.۰.۰-beta.28
مدیریت بنرهای هوم‌پیج از پنل مدیریت اضافه شد: CRUD، تصویر، متن‌های fa/ku/en، لینک مقصد، ترتیب، زمان‌بندی، مجوز مستقل و AuditLog. اسلایدر عمومی اکنون از بنرهای فعال و زمان‌بندی‌شده پایگاه داده استفاده می‌کند.


## نسخه ۱.۰.۰-beta.29
مدیریت چهار نوار اصلی هوم‌پیج از پنل اضافه شد: فعال/غیرفعال‌سازی، عنوان‌های fa/ku/en، ترتیب، تعداد محصولات و سرعت حرکت نوار جدیدترین. مسیر پنل `/admin/homepage-shelves` است و مجوز `homepage.manage` دارد.


## نسخه ۱.۰.۰-beta.30
مدیریت نام برند، لوگو، نوار اطلاع‌رسانی، اطلاعات تماس، شبکه‌های اجتماعی و Footer با پشتیبانی fa/ku/en اضافه شد. مسیر پنل `/admin/appearance` و مجوز آن `appearance.manage` است. Migration شماره 000019 را اجرا کنید.


## نسخه ۱.۰.۰-beta.31
تاریخچه نسخه‌های طراحی هوم‌پیج اضافه شد. مسیر `/admin/homepage-revisions` امکان Snapshot گرفتن از بنرها، نوارهای محصول و تنظیمات هویت سایت، پیش‌نمایش مدیریتی، انتشار با تأیید و بازگردانی با انتشار مجدد نسخه قبلی را فراهم می‌کند. Migration شماره 000020 باید اجرا شود.


## نسخه ۱.۰.۰-beta.32
مدیریت دسته‌بندی‌ها و محصولات منتخب هوم‌پیج به مسیر `/admin/homepage-merchandising` اضافه شد. Header و Footer مشترک به صفحات هوم‌پیج، دسته‌بندی، محصول، سبد خرید و تسویه‌حساب متصل شدند. Snapshot و Rollback طراحی، انتخاب‌های ویژه را نیز پوشش می‌دهد. Migration شماره 000021 باید اجرا شود.


## نسخه ۱.۰.۰-beta.33
مدیریت منوهای Header و Footer با عنوان‌های سه‌زبانه و محدودیت URL امن اضافه شد. مدیریت صفحات ثابت در `/admin/pages` و نمایش عمومی در `/pages/{slug}` فعال شد. Migration شماره 000022 جدول static_pages را ایجاد می‌کند. محتوای صفحات در این نسخه متن ساده و امن است و WYSIWYG/صفحه‌ساز بلوکی هنوز در برنامه بعدی قرار دارد.


## نسخه ۱.۰.۰-beta.34
ویرایشگر بلوکی امن برای صفحات ثابت اضافه شد: حداکثر ۸ بلوک از نوع عنوان بخش، پاراگراف و کادر اطلاع‌رسانی با متن سه‌زبانه. خروجی فقط متن Escape‌شده است و HTML/JavaScript اجرا نمی‌شود. Migration شماره 000023 ستون JSON محتوای بلوکی را اضافه می‌کند و محتوای صفحات قدیمی بدون تغییر باقی می‌ماند.


## نسخه ۱.۰.۰-beta.34
ورود، ثبت‌نام و خروج مشتری با کنترل وضعیت حساب و محدودیت تلاش اضافه شد. داشبورد `/account` تاریخچه صفحه‌بندی‌شده سفارش‌ها و خلاصه وضعیت را نمایش می‌دهد. جزئیات سفارش در `/account/orders/{publicId}` فقط برای مالک سفارش قابل مشاهده است. این نسخه Migration جدید ندارد. تأیید ایمیل، بازیابی گذرواژه و ویرایش پروفایل هنوز در برنامه بعدی هستند.


## نسخه ۱.۰.۰-beta.35 – ثبت‌نام با موبایل و کد پیامکی
ثبت‌نام مشتری با شماره موبایل و OTP شش‌رقمی اضافه شد. ابتدا شماره دریافت می‌شود، سپس کد ۵ دقیقه‌ای ارسال و تأیید می‌شود و بعد از تأیید، نام و گذرواژه گرفته شده و حساب ساخته می‌شود. شماره‌ها به قالب E.164 نرمال می‌شوند؛ ارقام فارسی/عربی و شماره‌های محلی ایران نیز پشتیبانی می‌شوند. ارسال مجدد ۶۰ ثانیه فاصله دارد، حداکثر ۵ ارسال در ساعت برای هر شماره و حداکثر ۵ تلاش برای هر کد مجاز است. مسیر ورود، شماره موبایل یا ایمیل را می‌پذیرد. Migration شماره 000023 (phone و phone_verified_at و sms_verification_codes) باید اجرا شود. اتصال پیامک واقعی نیازمند تنظیم SMS_DRIVER و endpoint/token سازگار با قرارداد مستندشده در docs/SMS-OTP-BETA35.md است.


## نسخه ۱.۰.۰-beta.36 – ثبت‌نام پیامکی
ثبت‌نام مشتری با شماره موبایل و OTP شش‌رقمی اضافه شده است. پس از تأیید شماره، صفحه تکمیل نام و گذرواژه نمایش داده می‌شود. ورود با موبایل و گذرواژه فعال است و ورود ایمیلی برای حساب‌های قدیمی حفظ شده است. تنظیمات پیامک از `SMS_DRIVER`, `SMS_HTTP_ENDPOINT`, `SMS_HTTP_TOKEN`, `SMS_SENDER` و `SMS_TIMEOUT` خوانده می‌شوند. Migration شماره 000023 باید اجرا شود. برای ارسال واقعی باید API پنل پیامکی در هاست تنظیم و Adapter با قرارداد همان سرویس تطبیق داده شود.


## نسخه ۱.۰.۰-beta.37 – سخت‌سازی ثبت‌نام پیامکی
پیش‌فرض درگاه پیامک به HTTP امن تغییر کرد تا بدون پیکربندی صریح، کد OTP در لاگ توسعه قرار نگیرد. پاسخ و مسیر بازگشت درخواست کد برای شماره‌های موجود و جدید یکسان شد تا امکان حدس زدن وجود حساب کاهش یابد. Rollback مهاجرت موبایل در صورت وجود حساب‌های بدون ایمیل متوقف می‌شود تا اطلاعات حساب‌ها ناسازگار نشود.


## نسخه ۱.۰.۰-beta.38 – ثبت‌نام با موبایل و پیامک واقعی
ثبت‌نام مشتری با شماره موبایل و OTP شش‌رقمی تکمیل شده است. حساب فقط پس از تأیید کد ساخته می‌شود. درایور مستقیم Kavenegar برای شماره‌های ایران اضافه شده و درایور HTTPS JSON/Bearer برای ارائه‌دهندگان دیگر حفظ شده است. کد ۵ دقیقه اعتبار دارد، ارسال مجدد ۶۰ ثانیه فاصله دارد، حداکثر ۵ تلاش برای هر کد و ۵ ارسال در ساعت مجاز است. درایور log فقط برای توسعه است و در production مسدود است. برای ارسال واقعی، کلید API پیامک باید در فایل خصوصی `.env` هاست قرار گیرد؛ ارسال زنده در این محیط انجام نشده است. راهنمای کامل در `docs/MOBILE-OTP-REGISTRATION-BETA38.md` قرار دارد.


## نسخه ۱.۰.۰-beta.38 – ثبت‌نام موبایلی با SMS OTP
ثبت‌نام مشتری با شماره موبایل و کد یک‌بارمصرف شش‌رقمی انجام می‌شود. حساب فقط پس از تأیید OTP ساخته و فعال می‌شود. درایور مستقیم Kavenegar برای شماره‌های ایران اضافه شده و درایور HTTPS JSON/Bearer برای سایر ارائه‌دهندگان سازگار حفظ شده است. کد ۵ دقیقه اعتبار دارد، فاصله ارسال مجدد ۶۰ ثانیه است، حداکثر ۵ تلاش برای هر کد و ۵ ارسال در ساعت مجاز است. درایور log فقط در محیط توسعه فعال است. ورود حساب‌های قدیمی با ایمیل همچنان پشتیبانی می‌شود. برای فعال‌سازی ارسال واقعی، کلید API پیامک باید در `.env` خصوصی هاست تنظیم شود. ارسال زنده و نصب روی هاست واقعی در این محیط آزمایش نشده‌اند. راهنمای پیکربندی: `docs/MOBILE-OTP-REGISTRATION-BETA38.md`.
