#!/usr/bin/env python3
"""Static guard: detect duplicate Schema::create targets across Laravel migrations."""
from pathlib import Path
import re
import sys

base = Path(__file__).resolve().parents[1] / "database" / "migrations"
seen = {}
errors = []
for path in sorted(base.glob("*.php")):
    source = path.read_text(encoding="utf-8")
    for table in re.findall(r"Schema::create\(\s*['\"]([^'\"]+)['\"]", source):
        if table in seen:
            errors.append(f"Duplicate create for table `{table}`: {seen[table]} and {path.name}")
        else:
            seen[table] = path.name

if errors:
    print("\n".join(errors))
    sys.exit(1)

print(f"PASS: {len(seen)} unique Schema::create targets across {len(list(base.glob('*.php')))} migrations.")
