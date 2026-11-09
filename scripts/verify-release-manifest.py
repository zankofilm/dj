#!/usr/bin/env python3
"""Verify SHA-256 hashes in RELEASE-MANIFEST.sha256."""
from pathlib import Path
import hashlib, sys
base=Path(__file__).resolve().parents[1]
manifest=base/"RELEASE-MANIFEST.sha256"
if not manifest.is_file():
    print("FAIL: manifest missing"); sys.exit(1)
failed=[]
count=0
for line in manifest.read_text(encoding="utf-8").splitlines():
    if not line.strip(): continue
    expected, rel=line.split("  ",1)
    path=base/rel
    if not path.is_file():
        failed.append(f"missing: {rel}")
        continue
    actual=hashlib.sha256(path.read_bytes()).hexdigest()
    if actual != expected: failed.append(f"hash mismatch: {rel}")
    count+=1
if failed:
    print("\n".join(failed)); sys.exit(1)
print(f"PASS: {count} package files match SHA-256 manifest.")
