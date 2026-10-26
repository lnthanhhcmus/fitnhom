#!/usr/bin/env bash
# PHPUnit của hai plugin Fit-Nhóm trên Moodle phát triển (chạy dev/sync.sh trước). Tự khởi tạo lại CSDL thử khi mã CSDL đổi.
#   bash dev/test.sh                      # toàn bộ
#   bash dev/test.sh --filter khai_test   # một tệp / một bài
set -euo pipefail
ROOT="${MOODLE_DEV_ROOT:-$HOME/moodle-dev}"
cd "$ROOT/moodle"
STAMP="$ROOT/.fitnhom-phpu-version"
[ -f local/fitnhom/version.php ] || { echo "local_fitnhom chưa cài (dev/sync.sh)"; exit 1; }
CUR=$(cat local/fitnhom/version.php local/fitnhom/db/*.php local/fitnhom/db/*.xml mod/assign/submission/fitnhom/version.php \
  local/fitcnl/version.php local/fitcnl/db/*.xml 2>/dev/null | sha1sum | cut -c1-40)
if [ ! -f "$STAMP" ] || [ "$(cat "$STAMP")" != "$CUR" ]; then
  echo "== mã CSDL đổi: khởi tạo lại PHPUnit =="
  php admin/tool/phpunit/cli/util.php --drop >/dev/null 2>&1 || true
  php admin/tool/phpunit/cli/init.php --no-composer-self-update --no-composer-upgrade >/dev/null
  echo "$CUR" > "$STAMP"
  rm -f "$ROOT/.fitcnl-phpu-version"
fi
vendor/bin/phpunit --testsuite local_fitnhom_testsuite "$@"
vendor/bin/phpunit --testsuite assignsubmission_fitnhom_testsuite "$@"
