#!/usr/bin/env bash
set -euo pipefail
VERSION="${1:-1.0.0}"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="${ROOT}/build/release"
STAGE="${BUILD_DIR}/stage"
OUT="${BUILD_DIR}/marketplace-${VERSION}-install.zip"
command -v php >/dev/null || { echo "ERROR: PHP CLI is required on the build runner."; exit 1; }
command -v composer >/dev/null || { echo "ERROR: Composer 2 is required on the build runner."; exit 1; }
command -v rsync >/dev/null || { echo "ERROR: rsync is required on the build runner."; exit 1; }
command -v python3 >/dev/null || { echo "ERROR: Python 3 is required to generate the release manifest."; exit 1; }
php -r 'exit(version_compare(PHP_VERSION, "8.5.0", ">=") ? 0 : 1);' || { echo "ERROR: PHP 8.5+ is required for this release build."; exit 1; }
cd "${ROOT}"
composer validate --no-check-publish
if [[ ! -f composer.lock ]]; then echo "ERROR: composer.lock is missing. Run composer update in CI before packaging."; exit 1; fi
rm -rf "${STAGE}"; mkdir -p "${STAGE}"
# Keep example env templates, never copy a real .env into a release.
rsync -a --exclude='.git' --exclude='build' --exclude='tests' \
  --exclude='.env' --include='.env.example' --include='.env.shared-hosting.example' --exclude='.env.*' \
  --exclude='node_modules' --exclude='public/build' --exclude='.phpunit.cache' \
  "${ROOT}/" "${STAGE}/"
cd "${STAGE}"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
test -f vendor/autoload.php || { echo "ERROR: Composer vendor autoloader was not created."; exit 1; }
test -f vendor/laravel/framework/src/Illuminate/Foundation/Application.php || { echo "ERROR: Laravel framework runtime is missing from vendor."; exit 1; }
test -f .env.example || { echo "ERROR: .env.example was not included in the release stage."; exit 1; }
test -f setup.php || { echo "ERROR: root setup.php is missing from release stage."; exit 1; }
php artisan package:discover --ansi
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 0750 {} \;
python3 scripts/generate-release-manifest.py
python3 scripts/verify-release-manifest.py
rm -rf tests .phpunit.cache
find . -name '.DS_Store' -delete
rm -f .env
mkdir -p "${BUILD_DIR}"; rm -f "${OUT}"
# ZIP contents at archive root: extraction into /shop yields /shop/setup.php.
zip -qr "${OUT}" .
unzip -tq "${OUT}"
python3 - "${OUT}" <<'PY'
import sys, zipfile
p=sys.argv[1]
with zipfile.ZipFile(p) as z:
    names=set(z.namelist())
    required={'setup.php','public/index.php','vendor/autoload.php','.env.example','composer.lock'}
    missing=sorted(required-names)
    if missing: raise SystemExit('Release ZIP missing required root files: '+', '.join(missing))
    if any(n.startswith('stage/') for n in names): raise SystemExit('Unexpected nested stage directory in ZIP')
print('PASS: ZIP root layout and required installer files verified.')
PY
echo "Installable artifact created: ${OUT}"
echo "Extract the ZIP contents directly into public_html/shop; installer: /shop/setup.php"
