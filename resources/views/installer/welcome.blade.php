<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>راه‌اندازی بازارگاه</title>
<style>
:root{font-family:Tahoma,Arial,sans-serif;color:#242033;background:#f6f4fb}
*{box-sizing:border-box}body{margin:0;padding:24px}.wrap{max-width:760px;margin:32px auto;background:#fff;border:1px solid #e8e3f2;border-radius:22px;padding:28px;box-shadow:0 12px 40px #27134a0c}
h1{color:#5732a3;margin-top:0}.muted{color:#6b6577;line-height:1.9}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
label{display:block;font-size:13px;font-weight:bold;margin:14px 0 7px}input{width:100%;padding:12px;border:1px solid #d9d2e7;border-radius:10px;font:inherit}
button{width:100%;margin-top:22px;background:#6639b8;color:white;border:0;border-radius:12px;padding:15px;font:inherit;font-weight:bold;cursor:pointer}button:disabled{background:#aaa;cursor:not-allowed}
.checks{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:16px 0}.check{padding:10px;background:#faf9fc;border-radius:9px;font-size:13px}.ok{color:#16804b}.bad{color:#b42318}
.error{background:#fff1f0;color:#a32218;border-radius:10px;padding:12px;margin:12px 0}.section{border-top:1px solid #eee9f5;margin-top:24px;padding-top:12px}
@media(max-width:600px){.grid,.checks{grid-template-columns:1fr}.wrap{padding:20px;margin:8px auto}}
</style></head>
<body><main class="wrap">
<h1>راه‌اندازی بازارگاه</h1>
<p class="muted">به نصب‌کننده فارسی خوش آمدید. اطلاعات اتصال پایگاه داده و حساب مدیر اصلی را وارد کنید. پیش از ادامه، از خالی بودن دیتابیس یا داشتن نسخه پشتیبان مطمئن شوید.</p>
<h2>بررسی اولیه سرور</h2>
<div class="checks">
@foreach($requirements as $label=>$passed)
<div class="check {{ $passed?'ok':'bad' }}">{{ $passed?'✓':'✕' }} {{ $label }}</div>
@endforeach
</div>
@php($requirementsPassed = !in_array(false, $requirements, true))
@if(!$requirementsPassed)
<div class="error"><strong>پیش‌نیازهای سرور کامل نیست.</strong> تا رفع همه موارد ناموفق، نصب را اجرا نکنید.</div>
@endif
@if($errors->any())
<div class="error"><strong>نصب انجام نشد:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ route('installer.install') }}">
@csrf
<input type="hidden" name="installer_token" value="{{ $token }}">
<div class="section"><h2>مشخصات سایت</h2>
<label>نام فروشگاه</label><input name="site_name" value="{{ old('site_name','بازارگاه') }}" required maxlength="120">
<label>آدرس سایت با https</label><input type="url" name="site_url" value="{{ old('site_url',url('/')) }}" required>
</div>
<div class="section"><h2>اتصال پایگاه داده MySQL</h2>
<div class="grid">
<div><label>میزبان دیتابیس</label><input name="db_host" value="{{ old('db_host','localhost') }}" required></div>
<div><label>پورت</label><input type="number" name="db_port" value="{{ old('db_port','3306') }}" required></div>
<div><label>نام دیتابیس</label><input name="db_database" value="{{ old('db_database') }}" required></div>
<div><label>نام کاربری دیتابیس</label><input name="db_username" value="{{ old('db_username') }}" required></div>
</div>
<label>رمز عبور دیتابیس</label><input type="password" name="db_password" autocomplete="new-password">
<p class="muted">این اطلاعات در فایل تنظیمات خصوصی سرور ذخیره می‌شوند. از دیتابیس اختصاصی با دسترسی محدود استفاده کنید.</p>
</div>
<div class="section"><h2>حساب مدیر اصلی</h2>
<label>نام مدیر</label><input name="admin_name" value="{{ old('admin_name') }}" required maxlength="120">
<label>ایمیل مدیر</label><input type="email" name="admin_email" value="{{ old('admin_email') }}" required maxlength="190">
<div class="grid">
<div><label>رمز عبور (حداقل ۱۲ نویسه)</label><input type="password" name="admin_password" required minlength="12" autocomplete="new-password"></div>
<div><label>تکرار رمز عبور</label><input type="password" name="admin_password_confirmation" required minlength="12" autocomplete="new-password"></div>
</div>
</div>
<button type="submit" {{ !$requirementsPassed ? "disabled" : "" }}>بررسی و نصب بازارگاه</button>
@if(!$requirementsPassed)<p class="muted">ابتدا موارد قرمز را در هاست برطرف کنید و صفحه را دوباره بارگذاری کنید.</p>@endif
</form>
<p class="muted">با اجرای نصب، جداول پایگاه داده ساخته و حساب مدیر ایجاد می‌شود. پس از نصب، مسیر راه‌اندازی قفل خواهد شد.</p>
</main></body></html>
