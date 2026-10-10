#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
cd "${PROJECT_ROOT}"

CONTENT_FILE="${PROJECT_ROOT}/wp-content/themes/blcmesa-theme/patterns/about-content.html"

# In this repo, WordPress runtime and WP-CLI run via the containerized wpcli service
WP_CLI="docker compose run --rm -T wpcli wp"

echo "=========================================================="
echo " Publishing 'About Us' Page to WordPress"
echo "=========================================================="

if [ ! -f "${CONTENT_FILE}" ]; then
  echo "Error: Content file ${CONTENT_FILE} not found."
  exit 1
fi

# Check if page with slug 'about' already exists
EXISTING_ID=$(${WP_CLI} post list --post_type=page --name=about --field=ID --format=ids 2>/dev/null || true)

if [ -n "${EXISTING_ID}" ]; then
  echo "Found existing 'About Us' page (ID: ${EXISTING_ID}). Updating content..."
  cat "${CONTENT_FILE}" | ${WP_CLI} post update "${EXISTING_ID}" - \
    --post_title="About Us" \
    --post_name="about" \
    --post_status="publish"
  PAGE_ID="${EXISTING_ID}"
else
  echo "Creating new 'About Us' page..."
  PAGE_ID=$(cat "${CONTENT_FILE}" | ${WP_CLI} post create - \
    --post_type=page \
    --post_title="About Us" \
    --post_name="about" \
    --post_status="publish" \
    --porcelain)
fi

# Flush rewrite rules
echo "Flushing rewrite rules..."
${WP_CLI} rewrite flush --hard || true

echo ""
echo "=========================================================="
echo " ✅ 'About Us' page is live and ready for church staff!"
echo " - Page ID:     ${PAGE_ID}"
echo " - Front-end:   http://localhost:8080/about/"
echo " - Admin Edit:  http://localhost:8080/wp-admin/post.php?post=${PAGE_ID}&action=edit"
echo "=========================================================="
