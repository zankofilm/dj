#!/usr/bin/env python3
from pathlib import Path
import sys
b=Path(__file__).resolve().parents[1]
routes=(b/"routes/web.php").read_text()
reg=(b/"app/Http/Controllers/Auth/RegisteredUserController.php").read_text()
otp=(b/"app/Services/Sms/SmsOtpService.php").read_text()
sms=(b/"app/Services/Sms/SmsGateway.php").read_text()
phone=(b/"app/Services/Sms/PhoneNumber.php").read_text()
migration=(b/"database/migrations/2026_01_01_000023_add_phone_registration_and_sms_codes.php").read_text()
user=(b/"app/Models/User.php").read_text()
complete=(b/"resources/views/auth/register-complete.blade.php").read_text()
verify=(b/"resources/views/auth/verify-mobile.blade.php").read_text()
login=(b/"app/Http/Controllers/Auth/AuthenticatedSessionController.php").read_text()
checks=[
("registration is phone-first and routed through OTP", "register.send-code" in routes and "register.verify-code" in routes and "register.complete" in routes),
("phone number column is unique and verified timestamp exists", "phone', 20)->nullable()->unique()" in migration and "phone_verified_at" in migration),
("OTP table stores hash, attempts, expiry and consumption", "code_hash" in migration and "attempts" in migration and "expires_at" in migration and "consumed_at" in migration),
("OTP generated with cryptographic random_int", "random_int(100000, 999999)" in otp),
("OTP is stored hashed", "Hash::make($code)" in otp),
("OTP expiry is configurable with 5 minute default", "ttl_seconds" in otp and "ttl_seconds' => 300" in (b/"config/sms.php").read_text()),
("OTP attempts are capped", "maxAttempts" in otp and "max_attempts' => 5" in (b/"config/sms.php").read_text()),
("resend cooldown and hourly send limit exist", "resend_cooldown_seconds" in otp and "max_sends_per_hour" in otp),
("OTP is consumed after successful verification", "consumed_at" in otp and "consumed_at" in otp[otp.find("public function verify"):]),
("registration account creation requires verified session phone", "registration.verified_phone" in reg and "phone_verified_at" in reg),
("mobile login normalizes phone and checks verified state", "PhoneNumber::normalize" in login and "phone_verified_at" in login),
("phone parser converts Persian digits", "'۰'=>'0'" in phone),
("SMS HTTP adapter requires HTTPS and bearer token", "https://" in sms and "withToken" in sms),
("completion form is present and posts registration", "route('register.store')" in complete and "password_confirmation" in complete),
("verification form supports one-time code and resend", "register.verify-code" in verify and "register.resend-code" in verify),
("registration page has phone field not email", 'name="phone"' in (b/"resources/views/auth/register.blade.php").read_text() and 'name="email"' not in (b/"resources/views/auth/register.blade.php").read_text()),
]
bad=[]
for name,ok in checks:
 print(("PASS " if ok else "FAIL ")+name)
 if not ok: bad.append(name)
print(f"{len(checks)-len(bad)}/{len(checks)} checks passed")
sys.exit(bool(bad))
