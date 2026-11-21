#!/usr/bin/env bash
set -euo pipefail

# Packages ONLY the custom theme (wp-content/themes/blcmesa-theme) into a
# clean zip archive safe for ongoing Cloudways re-uploads without touching
# the live WordPress database, church admin edits, or media uploads.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

THEME_DIR="${PROJECT_ROOT}/wp-content/themes/blcmesa-theme"
BACKUP_DIR="${PROJECT_ROOT}/backups"
mkdir -p "${BACKUP_DIR}"

if [ ! -d "${THEME_DIR}" ]; then
  echo "Error: Theme directory not found at ${THEME_DIR}" >&2
  exit 1
fi

THEME_VERSION=$(grep -m1 '^Version:' "${THEME_DIR}/style.css" | awk '{print $2}' | tr -d '\r' || echo "dev")
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
PACKAGE_FILE="${BACKUP_DIR}/blcmesa-theme_${THEME_VERSION}_${TIMESTAMP}.zip"

echo "Packaging blcmesa-theme v${THEME_VERSION} (theme-only, database & uploads untouched)..."

if command -v zip >/dev/null 2>&1; then
  (cd "${PROJECT_ROOT}/wp-content/themes" && zip -rq "${PACKAGE_FILE}" blcmesa-theme -x "*.DS_Store" "*Thumbs.db")
elif command -v python3 >/dev/null 2>&1; then
  python3 -c '
import os, sys, zipfile
themes_dir, out = sys.argv[1], sys.argv[2]
theme_path = os.path.join(themes_dir, "blcmesa-theme")
with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as zf:
    for root, _, files in os.walk(theme_path):
        for f in files:
            if f in (".DS_Store", "Thumbs.db"):
                continue
            full = os.path.join(root, f)
            zf.write(full, os.path.relpath(full, themes_dir))
' "${PROJECT_ROOT}/wp-content/themes" "${PACKAGE_FILE}"
else
  echo "Error: Neither zip nor python3 is installed to create archive." >&2
  exit 1
fi

echo ""
echo "=========================================================="
echo " ✅ Theme-only deployment package created!"
echo " Version:  ${THEME_VERSION}"
echo " Location: ${PACKAGE_FILE}"
echo ""
echo " Safe Cloudways update steps:"
echo "  1. Upload via WP Admin > Appearance > Themes > Add New > Upload Theme"
echo "     (click 'Replace current with uploaded') OR extract into"
echo "     public_html/wp-content/themes/ via SFTP/SSH."
echo "  2. Purge Breeze & Varnish caches on Cloudways."
echo "  (Live pages, sermons, and media uploads are NOT affected.)"
echo "=========================================================="
