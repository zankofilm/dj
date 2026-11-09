#!/usr/bin/env python3
"""Static regression checks for stale webhook recovery safety."""
from pathlib import Path
import sys

base = Path(__file__).resolve().parents[1]
rel = "app/Console/Commands/RecoverStalePaymentWebhooksCommand.php"
source = (base / rel).read_text(encoding="utf-8")
checks = [
    ("only processing events are selected", "where('processing_status', 'processing')"),
    ("only events with a lease timestamp are selected", "whereNotNull('processing_started_at')"),
    ("lease age threshold is applied", "where('processing_started_at', '<=', $cutoff)"),
    ("event is rechecked under row lock", "lockForUpdate()"),
    ("recovery moves event to manual review", "'processing_status' => 'needs_review'"),
    ("automatic payment replay is explicitly avoided", "پردازش خودکار مجدد امن نیست"),
]
failed = []
for label, needle in checks:
    if needle not in source:
        failed.append(label)
    else:
        print("PASS:", label)
if failed:
    print("FAIL:", "; ".join(failed))
    sys.exit(1)
print(f"PASS: {len(checks)} stale-webhook recovery checks.")
