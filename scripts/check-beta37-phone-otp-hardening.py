#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
def read(p): return (base/p).read_text(encoding='utf-8')
controller=read('app/Http/Controllers/Auth/RegisteredUserController.php')
config=read('config/sms.php')
migration=read('database/migrations/2026_01_01_000023_add_phone_registration_and_sms_codes.php')
checks=[
('same redirect is used for known and unknown phone numbers', "return redirect()->route('register')->with('otp_sent', 'اگر شماره قابل ثبت‌نام باشد" in controller),
('SMS defaults to fail-closed HTTP driver', "'driver' => env('SMS_DRIVER', 'http')" in config),
('development log driver cannot run in production', "app()->environment('production')" in read('app/Services/Sms/SmsGateway.php') and 'Log SMS driver is disabled in production' in read('app/Services/Sms/SmsGateway.php')),
('SMS HTTP transport requires HTTPS and bearer token', "str_starts_with(strtolower($endpoint), 'https://')" in read('app/Services/Sms/SmsGateway.php') and "withToken($token)" in read('app/Services/Sms/SmsGateway.php')),
('migration rollback refuses to strand phone-only accounts', 'whereNull(\'email\')->exists()' in migration and 'Rollback متوقف شد' in migration),
('OTP form and resend workflow remain connected', "route('register.verify-code')" in read('resources/views/auth/verify-mobile.blade.php') and "route('register.resend-code')" in read('resources/views/auth/verify-mobile.blade.php')),
]
failed=[]
for label,ok in checks:
 print(('PASS: ' if ok else 'FAIL: ')+label)
 if not ok: failed.append(label)
if failed: print('FAILED:',failed);sys.exit(1)
print(f'PASS: {len(checks)} beta 37 hardening checks.')
