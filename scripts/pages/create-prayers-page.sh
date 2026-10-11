#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
cd "${PROJECT_ROOT}"

CONTENT_FILE="${PROJECT_ROOT}/wp-content/themes/blcmesa-theme/patterns/prayers-content.html"

# In this repo, WordPress runtime and WP-CLI run via the containerized wpcli service
WP_CLI="docker compose run --rm -T wpcli wp"

echo "=========================================================="
echo " Publishing 'Prayers We Use' Page to WordPress (/prayers-we-use/)"
echo "=========================================================="

if [ ! -f "${CONTENT_FILE}" ]; then
  echo "Error: Content file ${CONTENT_FILE} not found."
  exit 1
fi

# Check if page with slug 'prayers-we-use' already exists
EXISTING_ID=$(${WP_CLI} post list --post_type=page --name=prayers-we-use --field=ID --format=ids 2>/dev/null || true)

if [ -n "${EXISTING_ID}" ]; then
  echo "Found existing 'Prayers We Use' page (ID: ${EXISTING_ID}). Updating content..."
  cat "${CONTENT_FILE}" | ${WP_CLI} post update "${EXISTING_ID}" - \
    --post_title="Prayers We Use" \
    --post_name="prayers-we-use" \
    --post_status="publish"
  PAGE_ID="${EXISTING_ID}"
else
  echo "Creating new 'Prayers We Use' page..."
  PAGE_ID=$(cat "${CONTENT_FILE}" | ${WP_CLI} post create - \
    --post_type=page \
    --post_title="Prayers We Use" \
    --post_name="prayers-we-use" \
    --post_status="publish" \
    --porcelain)
fi

# Flush rewrite rules
echo "Flushing rewrite rules..."
${WP_CLI} rewrite flush --hard || true

echo ""
echo "=========================================================="
echo " ✅ 'Prayers We Use' page is live!"
echo " - Page ID:     ${PAGE_ID}"
echo " - Front-end:   http://localhost:8080/prayers-we-use/"
echo " - Admin Edit:  http://localhost:8080/wp-admin/post.php?post=${PAGE_ID}&action=edit"
echo "=========================================================="
