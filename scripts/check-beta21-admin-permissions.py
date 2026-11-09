#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
middleware=(base/"app/Http/Middleware/RequirePermission.php").read_text(encoding="utf-8")
bootstrap=(base/"bootstrap/app.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
checks=[
("unauthenticated users are rejected","abort_unless($user, 401)",middleware),
("inactive users are rejected","$user->status === 'active'",middleware),
("permission is checked on authenticated user","$user->hasPermission($permission)",middleware),
("permission middleware alias is registered","'permission' => \App\Http\Middleware\RequirePermission::class",bootstrap),
("admin area has an access permission","middleware('permission:admin.access')",routes),
("dashboard has a dedicated permission","middleware('permission:admin.dashboard.view')",routes),
("user management has a dedicated permission","middleware('permission:users.manage')",routes),
("catalog management has a dedicated permission","middleware('permission:catalog.manage')",routes),
("seller review has a dedicated permission","middleware('permission:sellers.review')",routes),
]
failed=[]
for label,needle,source in checks:
    if needle not in source: failed.append(label)
    else: print("PASS:",label)
if failed:
    print("FAIL:",failed); sys.exit(1)
print(f"PASS: {len(checks)} admin authorization checks.")
