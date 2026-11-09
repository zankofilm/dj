#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
command -v php >/dev/null || { echo "PHP CLI required"; exit 1; }
PORT="${PORT:-18765}"
LOG="$(mktemp)"
php -S "127.0.0.1:${PORT}" -t "${ROOT}" >"${LOG}" 2>&1 &
PID=$!
cleanup() { kill "${PID}" >/dev/null 2>&1 || true; rm -f "${LOG}"; }
trap cleanup EXIT
for _ in $(seq 1 30); do
  if curl -fsS "http://127.0.0.1:${PORT}/setup.php" -o /tmp/marketplace-setup-smoke.html; then break; fi
  sleep 0.2
done
grep -q 'راه‌اندازی بازارگاه' /tmp/marketplace-setup-smoke.html
grep -q 'بررسی پیش‌نیازهای سرور' /tmp/marketplace-setup-smoke.html
grep -q 'اطلاعات دیتابیس MySQL' /tmp/marketplace-setup-smoke.html
echo "PASS: standalone Persian installer GET page renders."
echo "Note: this smoke test does not execute database installation or validate Laravel runtime."
rm -f /tmp/marketplace-setup-smoke.html
