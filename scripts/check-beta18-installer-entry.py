#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
wrapper=base/"public/setup.php"
if not wrapper.is_file():
    print("FAIL: public installer entry missing"); sys.exit(1)
source=wrapper.read_text(encoding="utf-8")
expected="require dirname(__DIR__) . '/setup.php';"
if expected not in source:
    print("FAIL: public installer does not delegate to root installer"); sys.exit(1)
root_installer=(base/"setup.php").read_text(encoding="utf-8")
if "$base = __DIR__;" not in root_installer:
    print("FAIL: root installer base path assumption changed"); sys.exit(1)
if "installed.lock" not in root_installer:
    print("FAIL: installed lock check missing"); sys.exit(1)
print("PASS: public DocumentRoot installer delegates to root installer.")
print("PASS: private path resolution remains anchored to package root.")
print("PASS: installed lock remains enforced by canonical installer.")
