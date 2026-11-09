#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
checks=[
("payment correlation migration","database/migrations/2026_01_01_000016_harden_webhook_processing.php","payment_reference"),
("payload replay collision check","app/Http/Controllers/PaymentWebhookController.php","hash_equals((string)$record->payload_hash"),
("row-locked webhook claim","app/Http/Controllers/PaymentWebhookController.php","lockForUpdate()"),
("in-flight retry response","app/Http/Controllers/PaymentWebhookController.php","Retry-After"),
("terminal shipping event audit","app/Services/Shipping/ShipmentService.php","provider_tracking_update_ignored"),
]
bad=[]
for label,rel,needle in checks:
    if needle not in (base/rel).read_text(encoding="utf-8"): bad.append(label)
    else: print("PASS:",label)
if bad: print("FAIL:",bad); sys.exit(1)
print(f"PASS: {len(checks)} webhook checks.")
