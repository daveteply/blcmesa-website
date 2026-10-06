#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs -d '\n')
fi

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
TEMP_EXPORT_DIR="${PROJECT_ROOT}/tmp/export_${TIMESTAMP}"
BACKUP_DIR="${PROJECT_ROOT}/backups"
PACKAGE_FILE="${BACKUP_DIR}/blcmesa_migration_package_${TIMESTAMP}.zip"

mkdir -p "${TEMP_EXPORT_DIR}" "${BACKUP_DIR}"

echo "1. Exporting current database..."
docker compose run --rm -T wpcli wp db export "${TEMP_EXPORT_DIR}/database.sql"

echo "2. Copying themes and plugins..."
mkdir -p "${TEMP_EXPORT_DIR}/wp-content"
cp -r "${PROJECT_ROOT}/wp-content/themes" "${TEMP_EXPORT_DIR}/wp-content/"
cp -r "${PROJECT_ROOT}/wp-content/plugins" "${TEMP_EXPORT_DIR}/wp-content/"

if [ -d "${PROJECT_ROOT}/wp-content/uploads" ]; then
  echo "Copying uploads media folder..."
  cp -r "${PROJECT_ROOT}/wp-content/uploads" "${TEMP_EXPORT_DIR}/wp-content/"
fi

echo "3. Creating migration instructions file..."
cat << 'EOF' > "${TEMP_EXPORT_DIR}/README_MIGRATION.txt"
======================================================================
Bethlehem Lutheran Church (blcmesa.org) - Hosting Migration Package
======================================================================

This package contains everything needed to deploy the site to production:
1. database.sql: Full MySQL database dump with posts, pages, and options.
2. wp-content/themes/blcmesa-theme: The custom Gutenberg Block Theme.
3. wp-content/plugins: Any plugins installed for the site.
4. wp-content/uploads: All sermon audio/video clips, PDFs, and images.

DEPLOYMENT INSTRUCTIONS:
----------------------------------------------------------------------
Option A: Standard WordPress Hosting (Bluehost, DreamHost, SiteGround, etc.)
1. Install a clean WordPress instance on your host domain (blcmesa.org).
2. Upload and extract the contents of wp-content/ (themes, plugins, uploads)
   into the host's wp-content/ directory using SFTP or File Manager.
3. Import database.sql via the host's phpMyAdmin or WP-CLI.
4. In WP-CLI or phpMyAdmin, run a search-replace to update URLs from localhost to production:
   wp search-replace "http://localhost:8080" "https://blcmesa.org" --all-tables

Option B: WordPress All-in-One WP Migration / Duplicator Plugin
You can also use standard WordPress migration plugins if preferred by the host.
======================================================================
EOF

echo "4. Creating zip archive..."
(cd "${TEMP_EXPORT_DIR}" && zip -r "${PACKAGE_FILE}" ./*)

# Clean up temp directory
rm -rf "${TEMP_EXPORT_DIR}"

echo ""
echo "=========================================================="
echo " ✅ Hosting migration package created successfully!"
echo " Location: ${PACKAGE_FILE}"
echo "=========================================================="
