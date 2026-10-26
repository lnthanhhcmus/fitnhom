#!/usr/bin/env bash
# phpcs (chuẩn moodle) + moodlecheck cho hai plugin Fit-Nhóm. Công cụ dùng chung thư mục ~/phpcs với FIT-CNL.
#   bash dev/lint.sh          # kiểm
#   bash dev/lint.sh --fix    # phpcbf tự sửa rồi kiểm lại
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
TOOLS="${PHPCS_HOME:-$HOME/phpcs}"
VER=3.13.2
mkdir -p "$TOOLS"; cd "$TOOLS"
[ -f phpcs.phar ] || curl -sSL -o phpcs.phar "https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/$VER/phpcs.phar"
[ -f phpcbf.phar ] || curl -sSL -o phpcbf.phar "https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/$VER/phpcbf.phar"
for r in moodlehq/moodle-cs PHPCSStandards/PHPCSUtils PHPCSStandards/PHPCSExtra; do
  d=$(basename "$r"); [ -d "$d" ] || git clone -q --depth 1 "https://github.com/$r.git" "$d"
done
php phpcs.phar --config-set installed_paths "$TOOLS/moodle-cs,$TOOLS/PHPCSUtils,$TOOLS/PHPCSExtra" >/dev/null
ROOT="${MOODLE_DEV_ROOT:-$HOME/moodle-dev}"
cd "$ROOT/moodle"
[ "${1:-}" = "--fix" ] && php "$TOOLS/phpcbf.phar" --standard=moodle --extensions=php local/fitnhom mod/assign/submission/fitnhom || true
php "$TOOLS/phpcs.phar" --standard=moodle --extensions=php local/fitnhom mod/assign/submission/fitnhom && echo "phpcs: sạch"
if [ -d local/moodlecheck ]; then
  MC=$(php local/moodlecheck/cli/moodlecheck.php -p=local/fitnhom,mod/assign/submission/fitnhom -f=text | grep -v "^$" || true)
  LOI=$(printf '%s\n' "$MC" | grep -vc "^local/\|^mod/" || true)
  if [ "$LOI" -gt 0 ]; then printf '%s\n' "$MC" | grep -v "^local/\|^mod/" -B1 | head -60; echo "moodlecheck: $LOI dòng lỗi"; exit 1; fi
  echo "moodlecheck: sạch"
fi
