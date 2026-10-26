#!/usr/bin/env bash
# Đóng gói hai plugin Fit-Nhóm thành zip cài qua Quản trị trang › Plugin › Cài plugin (cài local_fitnhom trước, rồi
# assignsubmission_fitnhom). Ra dist/local_fitnhom_<release>.zip, dist/assignsubmission_fitnhom_<release>.zip và SHA256SUMS
# (release lấy từ version.php; không gói tests/). dist/ không đưa vào kho — bản phát hành chép lên Drive Fit-Nhom\Cai-dat\.
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$REPO/dist"; mkdir -p "$OUT"
if grep -rIl --include='*.php' --include='*.md' --include='*.json' -E "ghp_[A-Za-z0-9]{20,}|password\s*=\s*'[^']{6,}'" \
    "$REPO/local_fitnhom" "$REPO/assignsubmission_fitnhom" 2>/dev/null | grep -v /tests/ ; then
  echo "Dừng: có vẻ có bí mật trong mã" >&2; exit 1
fi
cd "$OUT"
for plugin in local_fitnhom assignsubmission_fitnhom; do
  REL=$(grep -oP "release\s*=\s*'\K[^']+" "$REPO/$plugin/version.php")
  TMP=$(mktemp -d)
  rsync -a --exclude tests --exclude '.git*' "$REPO/$plugin/" "$TMP/fitnhom/"
  rm -f "${plugin}_$REL.zip"
  (cd "$TMP" && zip -qr "$OUT/${plugin}_$REL.zip" fitnhom)
  rm -rf "$TMP"; echo "đã ghi $OUT/${plugin}_$REL.zip"
done
sha256sum local_fitnhom_"$REL".zip assignsubmission_fitnhom_"$REL".zip | tee "SHA256SUMS_$REL.txt"
