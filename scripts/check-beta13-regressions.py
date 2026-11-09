#!/usr/bin/env python3
"""Static regression checks for seller workflow and installer/admin safety."""
from pathlib import Path
import sys

base = Path(__file__).resolve().parents[1]
checks = {
    "seller application show route exists": (
        "routes/web.php",
        "seller.application.show"
    ),
    "seller application POST is throttled": (
        "routes/web.php",
        "throttle:5,10"
    ),
    "last super-admin protection exists": (
        "app/Http/Controllers/Admin/UserManagementController.php",
        "آخرین مدیر کل فعال"
    ),
    "safe env replacement callback exists": (
        "app/Http/Controllers/InstallerController.php",
        "preg_replace_callback"
    ),
    "seller application duplicate query is locked": (
        "app/Http/Controllers/SellerApplicationController.php",
        "lockForUpdate()"
    ),
}
failed=[]
for label,(rel,needle) in checks.items():
    content=(base/rel).read_text(encoding="utf-8")
    if needle not in content:
        failed.append(label)
    else:
        print("PASS:",label)
if failed:
    print("FAIL:",", ".join(failed))
    sys.exit(1)
print(f"PASS: {len(checks)} regression checks.")
