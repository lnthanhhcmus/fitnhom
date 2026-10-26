#!/usr/bin/env bash
# Dựng amd/build/*.min.js của local_fitnhom từ amd/src/*.js (Babel → AMD có tên mô-đun, rồi terser), không cần grunt của Moodle.
#   bash dev/amd.sh
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
TOOL="${AMD_TOOL:-$HOME/amdtool}"
if [ ! -x "$TOOL/node_modules/.bin/babel" ]; then
  mkdir -p "$TOOL"; (cd "$TOOL" && npm init -y >/dev/null && npm i --silent @babel/core@7 @babel/cli@7 @babel/preset-env@7 @babel/plugin-transform-modules-amd@7 terser@5)
fi
cat > "$TOOL/babel.config.js" <<'JS'
module.exports = {
  presets: [['@babel/preset-env', {targets: {chrome: '90', firefox: '90', safari: '14'}, modules: false}]],
  plugins: [['@babel/plugin-transform-modules-amd']],
  moduleIds: true,
  getModuleId: (name) => 'local_fitnhom/' + name.replace(/^.*[\\/]/, '').replace(/\.js$/, ''),
};
JS
mkdir -p "$REPO/local_fitnhom/amd/build"
for f in "$REPO"/local_fitnhom/amd/src/*.js; do
  n=$(basename "$f" .js)
  (cd "$TOOL" && node_modules/.bin/babel --config-file "$TOOL/babel.config.js" "$f" -o "/tmp/fitnhom-amd-$n.js")
  "$TOOL/node_modules/.bin/terser" "/tmp/fitnhom-amd-$n.js" -c -m -o "$REPO/local_fitnhom/amd/build/$n.min.js"
  echo "amd: $n"
done
