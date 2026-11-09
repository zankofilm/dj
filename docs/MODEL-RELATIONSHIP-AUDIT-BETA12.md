# ممیزی قرارداد مدل‌ها و روابط – بتا ۱۲

## اصلاحات
- رابطه `SellerSettlement::payouts()` از HasMany به BelongsToMany تغییر کرد؛ مطابق جدول واسط `seller_payout_settlement` که مبلغ تخصیص‌یافته هر پرداخت را نگهداری می‌کند.
- رابطه‌های `User::orders()`، `User::carts()` و `User::ownedSellers()` اضافه شد.
- رابطه `Order::user()` و `Order::payments()` اضافه شد.
- رابطه‌های `Seller::products()`، `Seller::settlements()` و `Seller::payouts()` اضافه شد.
- رابطه `ReturnEvent::actor()` اضافه شد.
- اسکریپت ممیزی ایستا برای ارجاع کلاس‌های مدل و تکرار هدف‌های Schema::create اضافه شد.

## نکته طراحی درباره Webhook پرداخت
رویدادهای Webhook در ساختار فعلی با `provider_key` و `provider_event_id` ثبت می‌شوند و FK مستقیم به یک Payment ندارند. بنابراین رابطه Eloquent مستقیم Payment به WebhookEvent تعریف نشد؛ اتصال رویداد به پرداخت باید از طریق شناسه مرجع Provider و سرویس تطبیق انجام شود.

## محدودیت‌های آزمون
این مرحله بررسی ایستا است. بدون Composer و vendor امکان اجرای تست‌های Eloquent، اجرای Migration روی MySQL و آزمون واقعی تراکنش‌ها وجود ندارد.
