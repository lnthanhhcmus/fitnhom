#!/usr/bin/env bash
# Chép hai plugin Fit-Nhóm vào Moodle phát triển, nâng cấp CSDL, xóa đệm. Dùng sau mỗi lần sửa mã.
#   bash dev/sync.sh
set -euo pipefail
ROOT="${MOODLE_DEV_ROOT:-$HOME/moodle-dev}"
REPO="$(cd "$(dirname "$0")/.." && pwd)"
rsync -a --delete "$REPO/local_fitnhom/" "$ROOT/moodle/local/fitnhom/"
rsync -a --delete "$REPO/assignsubmission_fitnhom/" "$ROOT/moodle/mod/assign/submission/fitnhom/"
cd "$ROOT/moodle" && php admin/cli/upgrade.php --non-interactive >/dev/null && php admin/cli/purge_caches.php >/dev/null
# Moodle phát triển dùng chung với FIT-CNL: buộc test.sh của FIT-CNL khởi tạo lại PHPUnit lần chạy kế.
rm -f "$ROOT/.fitcnl-phpu-version"
echo "sync: $(php -r "define('CLI_SCRIPT',1); require 'config.php'; echo get_config('local_fitnhom','version') ?: 'chưa cài', ' / ', get_config('assignsubmission_fitnhom','version') ?: 'chưa cài';")"
