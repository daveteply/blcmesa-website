#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

if [ -f .env ]; then
  while IFS='=' read -r key value || [ -n "${key}" ]; do
    [[ "${key}" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || continue
    value="${value%$'\r'}"
    value="${value#\"}"
    value="${value%\"}"
    value="${value#\'}"
    value="${value%\'}"
    export "${key}=${value}"
  done < .env
fi

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
TEMP_EXPORT_DIR="${PROJECT_ROOT}/tmp/export_${TIMESTAMP}"
BACKUP_DIR="${PROJECT_ROOT}/backups"
PACKAGE_FILE="${BACKUP_DIR}/blcmesa_migration_package_${TIMESTAMP}.zip"

mkdir -p "${TEMP_EXPORT_DIR}" "${BACKUP_DIR}"
trap 'rm -rf "${TEMP_EXPORT_DIR}"' EXIT

echo "1. Exporting current database..."
if [ -f /var/www/html/wp-load.php ] && command -v wp >/dev/null 2>&1; then
  wp --path=/var/www/html db export - > "${TEMP_EXPORT_DIR}/database.sql"
else
  docker compose run --rm -T wpcli wp db export - > "${TEMP_EXPORT_DIR}/database.sql"
fi

# Strip MariaDB 11+ client sandbox directive for compatibility with Cloudways MariaDB 10.11
if grep -q 'enable the sandbox mode' "${TEMP_EXPORT_DIR}/database.sql"; then
  sed -i '/enable the sandbox mode/d' "${TEMP_EXPORT_DIR}/database.sql"
fi

echo "2. Copying custom theme (blcmesa-theme) and non-default plugins..."
mkdir -p "${TEMP_EXPORT_DIR}/wp-content/themes" "${TEMP_EXPORT_DIR}/wp-content/plugins"
cp -r "${PROJECT_ROOT}/wp-content/themes/blcmesa-theme" "${TEMP_EXPORT_DIR}/wp-content/themes/"

if [ -d "${PROJECT_ROOT}/wp-content/plugins" ]; then
  for item in "${PROJECT_ROOT}/wp-content/plugins"/*; do
    [ -e "${item}" ] || continue
    base="$(basename "${item}")"
    case "${base}" in
      akismet|hello.php|index.php|.gitkeep)
        continue
        ;;
      *)
        cp -r "${item}" "${TEMP_EXPORT_DIR}/wp-content/plugins/"
        ;;
    esac
  done
fi

if [ -d "${PROJECT_ROOT}/wp-content/uploads" ]; then
  echo "Copying uploads media folder..."
  cp -r "${PROJECT_ROOT}/wp-content/uploads" "${TEMP_EXPORT_DIR}/wp-content/"
fi

echo "3. Creating Cloudways migration instructions file..."
cat << 'EOF' > "${TEMP_EXPORT_DIR}/README_MIGRATION.txt"
======================================================================
Bethlehem Lutheran Church (blcmesa.org) - Initial Hosting Migration
======================================================================

IMPORTANT WARNING:
Use this FULL package ONLY for the initial one-time launch on Cloudways.
Once church admins begin editing pages, posts, or media on Cloudways,
NEVER re-import database.sql from local (it will overwrite live edits).
For ongoing code/theme updates, use: bash scripts/export-theme.sh

Package contents:
1. database.sql: Full MySQL/MariaDB database dump (sanitized for MariaDB 10.11+).
2. wp-content/themes/blcmesa-theme: Custom Gutenberg Block Theme.
3. wp-content/plugins: Any custom/added plugins (default plugins excluded).
4. wp-content/uploads: All church images, bulletins, and PDFs.

CLOUDWAYS INITIAL DEPLOYMENT STEPS:
----------------------------------------------------------------------
1. Launch a WordPress application on Cloudways (PHP 8.3 or 8.4, MariaDB 10.11+).
2. Upload wp-content/themes/blcmesa-theme, wp-content/uploads, and any plugins
   into public_html/wp-content/ via SFTP/SSH.
3. Import database.sql into the Cloudways database via SSH/WP-CLI or Database Manager:
     wp db import database.sql
4. Run URL search-replace (replacing localhost with your staging or live URL):
     wp search-replace "http://localhost:8080" "https://blcmesa.org" --all-tables --precise
     wp theme activate blcmesa-theme
     wp rewrite flush --hard
     wp cache flush
5. Cloudways Post-Launch Configuration:
   - Exclude /contact/ from Breeze & Varnish full-page caching so CSRF nonces stay valid.
   - Install and configure FluentSMTP (or Cloudways Elastic Email) for office@blcmesa.org.
   - Verify SPF/DKIM/DMARC DNS records for blcmesa.org.
======================================================================
EOF

echo "4. Creating zip archive..."
if command -v zip >/dev/null 2>&1; then
  (cd "${TEMP_EXPORT_DIR}" && zip -rq "${PACKAGE_FILE}" ./*)
elif command -v python3 >/dev/null 2>&1; then
  python3 -c '
import os, sys, zipfile
src, out = sys.argv[1], sys.argv[2]
with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as zf:
    for root, _, files in os.walk(src):
        for f in files:
            full = os.path.join(root, f)
            zf.write(full, os.path.relpath(full, src))
' "${TEMP_EXPORT_DIR}" "${PACKAGE_FILE}"
else
  echo "Error: Neither zip nor python3 is installed to create archive." >&2
  exit 1
fi

echo ""
echo "=========================================================="
echo " ✅ Initial hosting migration package created!"
echo " Location: ${PACKAGE_FILE}"
echo " Note: For ongoing theme-only updates after launch, use:"
echo "       bash scripts/export-theme.sh"
echo "=========================================================="
