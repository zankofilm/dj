#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
routes=(base/"routes/web.php").read_text(encoding="utf-8")
auth=(base/"app/Http/Controllers/Auth/AuthenticatedSessionController.php").read_text(encoding="utf-8")
register=(base/"app/Http/Controllers/Auth/RegisteredUserController.php").read_text(encoding="utf-8")
account=(base/"app/Http/Controllers/AccountController.php").read_text(encoding="utf-8")
header=(base/"resources/views/partials/storefront-header.blade.php").read_text(encoding="utf-8")
login=(base/"resources/views/auth/login.blade.php").read_text(encoding="utf-8")
signup=(base/"resources/views/auth/register.blade.php").read_text(encoding="utf-8")
account_view=(base/"resources/views/account/index.blade.php").read_text(encoding="utf-8")
order_view=(base/"resources/views/orders/show.blade.php").read_text(encoding="utf-8")
provider=(base/"app/Providers/MarketplaceServiceProvider.php").read_text(encoding="utf-8")
checks=[
("login routes exist and are guest-only","name('login')" in routes and "name('login.store')" in routes and "Route::middleware(['guest', SetLocale::class])" in routes),
("registration routes are throttled","name('register')" in routes and "throttle:3,10" in routes),
("logout is authenticated and invalidates session","name('logout')" in routes and "middleware('auth')" in routes and "session()->invalidate()" in auth and "regenerateToken()" in auth),
("login regenerates session and checks active account","session()->regenerate()" in auth and "status !== 'active'" in auth),
("registration requires verified phone and confirmed strong password","phone_verified_at" in register and "confirmed" in register and "Password::min(10)->letters()->numbers()" in register),
("registration stores hashed password and active status","Hash::make" in register and "'status' => 'active'" in register),
("account endpoints are protected by auth group","Route::get('/account'" in routes and "Route::middleware('auth')->group" in routes),
("account data is scoped to authenticated user","where('user_id', $request->user()->id)" in account),
("account orders paginate and load status stats","paginate(10)" in account and "'pending' =>" in account and "'completed' =>" in account),
("account page provides order history and profile summary","account-order-list" in account_view and "account-profile" in account_view),
("order detail is scoped to owner and includes seller orders","where('user_id', $request->user()->id)" in account and "sellerOrders.seller" in account),
("order detail includes items and payment continuation","order->items" in order_view and "payments.start" in order_view),
("shared header exposes login account and logout","route('login')" in header and "route('account.index')" in header and "route('logout')" in header),
("auth views receive shared appearance settings","'auth.login'" in provider and "'auth.register'" in provider),
("auth pages include CSRF-protected forms","@csrf" in login and "@csrf" in signup),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed: print("FAILED:",failed);sys.exit(1)
print(f"PASS: {len(checks)} beta 34 auth/account regression checks (updated for mobile OTP registration).")
