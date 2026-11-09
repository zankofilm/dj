#!/usr/bin/env python3
"""Generate SHA-256 checksums for all release files except the manifest itself."""
from pathlib import Path
import hashlib
base = Path(__file__).resolve().parents[1]
manifest = base / "RELEASE-MANIFEST.sha256"
rows = []
for path in sorted(p for p in base.rglob("*") if p.is_file() and p != manifest):
    rel = path.relative_to(base).as_posix()
    digest = hashlib.sha256(path.read_bytes()).hexdigest()
    rows.append(f"{digest}  {rel}")
manifest.write_text("\n".join(rows) + "\n", encoding="utf-8")
print(f"Generated SHA-256 manifest for {len(rows)} files.")
