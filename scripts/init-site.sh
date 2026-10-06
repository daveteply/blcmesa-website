#!/usr/bin/env bash
set -euo pipefail

# Determine repository root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

# Load environment variables if .env exists
if [ -f .env ]; then
  # Export non-commented lines from .env
  export $(grep -v '^#' .env | xargs -d '\n')
fi

# Fallback defaults
WP_SITE_URL="${WP_SITE_URL:-http://localhost:8080}"
WP_SITE_TITLE="${WP_SITE_TITLE:-Bethlehem Lutheran Church}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-admin_password}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@blcmesa.org}"
WP_TIMEZONE="${WP_TIMEZONE:-America/Phoenix}"
WP_ACTIVE_THEME="${WP_ACTIVE_THEME:-blcmesa-theme}"
WORDPRESS_PORT="${WORDPRESS_PORT:-8080}"
PHPMYADMIN_PORT="${PHPMYADMIN_PORT:-8081}"

echo "=========================================================="
echo " Starting / Verifying BLC Mesa WordPress Dev Platform"
echo "=========================================================="

# Ensure docker compose is running
docker compose up -d

echo "Waiting for MariaDB database to be healthy..."
until docker compose ps db | grep -q "(healthy)"; do
  sleep 2
done
echo "MariaDB is ready and healthy."

# Wait for WordPress core files to be installed in container
echo "Checking WordPress installation..."
MAX_TRIES=30
COUNT=0
until docker compose run --rm wpcli wp core is-installed 2>/dev/null || [ $COUNT -ge $MAX_TRIES ]; do
  if [ $COUNT -eq 0 ]; then
    echo "WordPress is not yet installed. Initializing automated installation..."
    docker compose run --rm wpcli wp core install \
      --url="${WP_SITE_URL}" \
      --title="${WP_SITE_TITLE}" \
      --admin_user="${WP_ADMIN_USER}" \
      --admin_password="${WP_ADMIN_PASSWORD}" \
      --admin_email="${WP_ADMIN_EMAIL}" \
      --skip-email
    break
  fi
  sleep 2
  COUNT=$((COUNT+1))
done

# Configure WordPress options
echo "Configuring site options..."
docker compose run --rm wpcli wp option update timezone_string "${WP_TIMEZONE}" || true
docker compose run --rm wpcli wp option update blogdescription "A home to share God's love and grace in Mesa, Arizona" || true
docker compose run --rm wpcli wp rewrite structure '/%postname%/' || true

# Activate custom Gutenberg Block Theme
echo "Activating theme: ${WP_ACTIVE_THEME}..."
docker compose run --rm wpcli wp theme activate "${WP_ACTIVE_THEME}" || true

echo ""
echo "=========================================================="
echo " ✅ BLC Mesa WordPress Platform is Ready!"
echo "=========================================================="
echo " - WordPress Site:    ${WP_SITE_URL}"
echo " - WP Admin Area:     ${WP_SITE_URL}/wp-admin"
echo " - phpMyAdmin (DB):   http://localhost:${PHPMYADMIN_PORT}"
echo " - Admin Username:    ${WP_ADMIN_USER}"
echo " - Admin Password:    ${WP_ADMIN_PASSWORD}"
echo " - Active Theme:      ${WP_ACTIVE_THEME} (Gutenberg Block Theme)"
echo "=========================================================="
