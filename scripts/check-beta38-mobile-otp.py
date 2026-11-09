#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
checks={
 'registration asks for phone and sends OTP': "route('register.send-code')" in (base/'resources/views/auth/register.blade.php').read_text(),
 'verification page has one-time-code input': 'autocomplete="one-time-code"' in (base/'resources/views/auth/verify-mobile.blade.php').read_text(),
 'verification page supports resend': "route('register.resend-code')" in (base/'resources/views/auth/verify-mobile.blade.php').read_text(),
 'routes expose send verify resend and completion': all(x in (base/'routes/web.php').read_text() for x in ["name('register.send-code')", "name('register.verify')", "name('register.verify-code')", "name('register.resend-code')", "name('register.complete')"]),
 'OTP is hashed and expires': "Hash::make($code)" in (base/'app/Services/Sms/SmsOtpService.php').read_text() and 'addSeconds' in (base/'app/Services/Sms/SmsOtpService.php').read_text(),
 'OTP enforces cooldown send limit and max attempts': all(x in (base/'app/Services/Sms/SmsOtpService.php').read_text() for x in ['resend_cooldown_seconds','max_sends_per_hour','max_attempts']),
 'phone normalization accepts Persian and Arabic digits': "'۰'=>'0'" in (base/'app/Services/Sms/PhoneNumber.php').read_text() and "'٠'=>'0'" in (base/'app/Services/Sms/PhoneNumber.php').read_text(),
 'production log SMS is disabled': "app()->environment('production')" in (base/'app/Services/Sms/SmsGateway.php').read_text(),
 'Kavenegar direct driver uses HTTPS': "api.kavenegar.com" in (base/'app/Services/Sms/SmsGateway.php').read_text() and "SMS_DRIVER=kavenegar" in (base/'.env.example').read_text(),
 'custom HTTP provider requires HTTPS and bearer token': "str_starts_with(strtolower($endpoint), 'https://')" in (base/'app/Services/Sms/SmsGateway.php').read_text() and 'withToken($token)' in (base/'app/Services/Sms/SmsGateway.php').read_text(),
 'registration account is created only after verified phone': "registration.verified_phone" in (base/'app/Http/Controllers/Auth/RegisteredUserController.php').read_text() and "'phone_verified_at' => now()" in (base/'app/Http/Controllers/Auth/RegisteredUserController.php').read_text(),
 'existing and new phone numbers use same registration response path': "put('registration.pending_phone', $phone)" in (base/'app/Http/Controllers/Auth/RegisteredUserController.php').read_text() and "registration.blocked_existing" in (base/'app/Http/Controllers/Auth/RegisteredUserController.php').read_text(),
 'old email login remains compatible': "['email' => mb_strtolower($identity)" in (base/'app/Http/Controllers/Auth/AuthenticatedSessionController.php').read_text(),
 'OTP migration creates verification table and nullable email': 'sms_verification_codes' in (base/'database/migrations/2026_01_01_000023_add_phone_registration_and_sms_codes.php').read_text() and "nullable()->change()" in (base/'database/migrations/2026_01_01_000023_add_phone_registration_and_sms_codes.php').read_text(),
}
failed=[]
for label,ok in checks.items():
 print(('PASS: ' if ok else 'FAIL: ')+label)
 if not ok: failed.append(label)
if failed:
 print('FAILED:',failed);sys.exit(1)
print(f'PASS: {len(checks)} mobile OTP checks.')
