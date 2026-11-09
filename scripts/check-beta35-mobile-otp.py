from pathlib import Path
import sys
b=Path(__file__).resolve().parents[1]
files={
 'migration':(b/'database/migrations/2026_01_01_000023_add_phone_registration_and_sms_codes.php').read_text(),
 'user':(b/'app/Models/User.php').read_text(),
 'controller':(b/'app/Http/Controllers/Auth/RegisteredUserController.php').read_text(),
 'login':(b/'app/Http/Controllers/Auth/AuthenticatedSessionController.php').read_text(),
 'otp':(b/'app/Services/Sms/SmsOtpService.php').read_text(),
 'phone':(b/'app/Services/Sms/PhoneNumber.php').read_text(),
 'gateway':(b/'app/Services/Sms/SmsGateway.php').read_text(),
 'routes':(b/'routes/web.php').read_text(),
 'register_view':(b/'resources/views/auth/register.blade.php').read_text(),
 'verify_view':(b/'resources/views/auth/verify-mobile.blade.php').read_text(),
 'complete_view':(b/'resources/views/auth/register-complete.blade.php').read_text(),
 'env':(b/'.env.example').read_text(),
}
checks=[
('phone and phone_verified_at migration',"$table->string('phone', 20)->nullable()->unique()" in files['migration'] and 'phone_verified_at' in files['migration']),
('OTP table stores hash and expiry, not plaintext',"'code_hash'" in files['migration'] and 'expires_at' in files['migration'] and 'sms_verification_codes' in files['migration']),
('user model mass-assigns phone verification fields',"'phone'" in files['user'] and "'phone_verified_at'" in files['user']),
('OTP generation is six digits and hash stored',"random_int(100000, 999999)" in files['otp'] and 'Hash::make($code)' in files['otp']),
('OTP verify uses transaction and row lock', 'DB::transaction' in files['otp'] and 'lockForUpdate' in files['otp']),
('OTP expiry, attempts and resend cooldown enforced','expires_at' in files['otp'] and 'max_attempts' in files['otp'] and 'resend_cooldown_seconds' in files['otp']),
('phone normalization accepts Persian digits and local Iran number','۰' in files['phone'] and "^09[0-9]{9}$" in files['phone']),
('registration separates send, verify and complete steps',"register.send-code" in files['routes'] and "register.verify-code" in files['routes'] and "register.complete" in files['routes']),
('resend route is throttled',"register.resend-code" in files['routes'] and 'throttle:3,10' in files['routes']),
('registration page asks phone only', 'name="phone"' in files['register_view'] and 'name="password"' not in files['register_view']),
('OTP page has one-time-code autocomplete', 'autocomplete="one-time-code"' in files['verify_view']),
('password is collected only after phone verification', 'registration.verified_phone' in files['controller'] and 'register-complete' in files['controller'] and 'name="password"' in files['complete_view']),
('login accepts phone or legacy email','phone' in files['login'] and 'email' in files['login']),
('production log SMS disabled','environment(\'production\')' in files['gateway']),
('HTTPS provider and bearer token configured',"SMS_DRIVER=http" in files['env'] and 'SMS_HTTP_ENDPOINT=https://' in files['env'] and 'SMS_HTTP_TOKEN=' in files['env']),
]
failed=[]
for label,ok in checks:
 print(('PASS: ' if ok else 'FAIL: ')+label)
 if not ok: failed.append(label)
print(f'RESULT: {len(checks)-len(failed)}/{len(checks)} checks passed')
sys.exit(bool(failed))
