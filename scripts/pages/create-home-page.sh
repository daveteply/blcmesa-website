#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
cd "${PROJECT_ROOT}"

CONTENT_FILE="${PROJECT_ROOT}/wp-content/themes/blcmesa-theme/patterns/home-content.html"

# Automatically use local WP-CLI inside Dev Container or docker compose on host
if [ -f /var/www/html/wp-load.php ] && command -v wp >/dev/null 2>&1; then
  WP_CLI="wp --path=/var/www/html"
else
  WP_CLI="docker compose run --rm -T wpcli wp"
fi

echo "=========================================================="
echo " Publishing 'Home' Page to WordPress (/)"
echo "=========================================================="

if [ ! -f "${CONTENT_FILE}" ]; then
  echo "Error: Content file ${CONTENT_FILE} not found."
  exit 1
fi

# Check if page with slug 'home' already exists
EXISTING_ID=$(${WP_CLI} post list --post_type=page --name=home --field=ID --format=ids 2>/dev/null || true)

if [ -n "${EXISTING_ID}" ]; then
  echo "Found existing 'Home' page (ID: ${EXISTING_ID}). Updating content..."
  ${WP_CLI} post update "${EXISTING_ID}" - \
    --post_title="Home" \
    --post_name="home" \
    --post_status="publish" < "${CONTENT_FILE}"
  PAGE_ID="${EXISTING_ID}"
else
  echo "Creating new 'Home' page..."
  PAGE_ID=$(${WP_CLI} post create - \
    --post_type=page \
    --post_title="Home" \
    --post_name="home" \
    --post_status="publish" \
    --porcelain < "${CONTENT_FILE}")
fi

# Configure WordPress to use this page as the static front page
echo "Configuring static homepage (page ID: ${PAGE_ID})..."
${WP_CLI} option update show_on_front page
${WP_CLI} option update page_on_front "${PAGE_ID}"

# Flush rewrite rules
echo "Flushing rewrite rules..."
${WP_CLI} rewrite flush --hard || true

echo ""
echo "=========================================================="
echo " ✅ 'Home' page is live and configured as the static homepage!"
echo " - Page ID:     ${PAGE_ID}"
echo " - Front-end:   http://localhost:8080/"
echo " - Admin Edit:  http://localhost:8080/wp-admin/post.php?post=${PAGE_ID}&action=edit"
echo "=========================================================="
