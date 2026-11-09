#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
source=(base/"setup.php").read_text(encoding="utf-8")
checks=[
("admin email normalized to lowercase", "$adminEmail = strtolower(trim((string)($_POST['admin_email'] ?? '')));"),
("state persistence uses atomic rename", "if (!rename($temporary, $path))"),
("existing account requires admin_created stage", "$stage !== 'admin_created'"),
("existing account ID must match recorded install state", "$expectedAdminId !== (int) $admin->id"),
("new account can only be created after seeding", "if ($stage !== 'database_seeded')"),
("seed stage persisted through helper", "write_install_state($statePath, $state);"),
]
failed=[]
for label,needle in checks:
    if needle not in source:
        failed.append(label)
    else:
        print("PASS:",label)
if failed:
    print("FAIL:",failed); sys.exit(1)
print(f"PASS: {len(checks)} installer security checks.")
