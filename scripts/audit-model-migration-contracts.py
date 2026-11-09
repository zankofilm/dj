#!/usr/bin/env python3
"""Static contract audit for model class references and migration table creation."""
from pathlib import Path
import re, sys

base = Path(__file__).resolve().parents[1]
models = base / "app" / "Models"
migrations = base / "database" / "migrations"
known = {p.stem for p in models.glob("*.php")}
errors = []

for path in models.glob("*.php"):
    source = path.read_text(encoding="utf-8")
    pattern = r"\b(?:belongsTo|hasMany|hasOne|belongsToMany)\(\s*([A-Z][A-Za-z0-9_]*)::class"
    for cls in re.findall(pattern, source):
        if cls not in known and cls != "self":
            errors.append(f"{path.name}: unresolved model class {cls}")

created = {}
for path in sorted(migrations.glob("*.php")):
    source = path.read_text(encoding="utf-8")
    for table in re.findall(r"Schema::create\(\s*['\"]([^'\"]+)['\"]", source):
        if table in created:
            errors.append(f"Duplicate Schema::create `{table}` in {created[table]} and {path.name}")
        created[table] = path.name

if errors:
    print("\\n".join(errors))
    sys.exit(1)
print(f"PASS: {len(known)} model files; all statically referenced model classes resolve.")
print(f"PASS: {len(created)} unique table creation targets across {len(list(migrations.glob('*.php')))} migrations.")
print("NOTE: Static check only; it does not execute Eloquent relationships or MySQL migrations.")
