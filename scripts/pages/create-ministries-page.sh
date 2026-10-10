#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
cd "${PROJECT_ROOT}"

CONTENT_FILE="${PROJECT_ROOT}/wp-content/themes/blcmesa-theme/patterns/ministries-content.html"

# In this repo, WordPress runtime and WP-CLI run via the containerized wpcli service
WP_CLI="docker compose run --rm -T wpcli wp"

echo "=========================================================="
echo " Publishing 'Ministries' Page to WordPress (/ministries/)"
echo "=========================================================="

if [ ! -f "${CONTENT_FILE}" ]; then
  echo "Error: Content file ${CONTENT_FILE} not found."
  exit 1
fi

# Check if page with slug 'ministries' already exists
EXISTING_ID=$(${WP_CLI} post list --post_type=page --name=ministries --field=ID --format=ids 2>/dev/null || true)

if [ -n "${EXISTING_ID}" ]; then
  echo "Found existing 'Ministries' page (ID: ${EXISTING_ID}). Updating content..."
  cat "${CONTENT_FILE}" | ${WP_CLI} post update "${EXISTING_ID}" - \
    --post_title="Ministries" \
    --post_name="ministries" \
    --post_status="publish"
  PAGE_ID="${EXISTING_ID}"
else
  echo "Creating new 'Ministries' page..."
  PAGE_ID=$(cat "${CONTENT_FILE}" | ${WP_CLI} post create - \
    --post_type=page \
    --post_title="Ministries" \
    --post_name="ministries" \
    --post_status="publish" \
    --porcelain)
fi

# Flush rewrite rules
echo "Flushing rewrite rules..."
${WP_CLI} rewrite flush --hard || true

echo ""
echo "=========================================================="
echo " ✅ 'Ministries' page is live and ready for church staff!"
echo " - Page ID:     ${PAGE_ID}"
echo " - Front-end:   http://localhost:8080/ministries/"
echo " - Admin Edit:  http://localhost:8080/wp-admin/post.php?post=${PAGE_ID}&action=edit"
echo "=========================================================="
