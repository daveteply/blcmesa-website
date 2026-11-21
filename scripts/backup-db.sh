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

BACKUP_DIR="${PROJECT_ROOT}/backups"
mkdir -p "${BACKUP_DIR}"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/blcmesa_db_${TIMESTAMP}.sql.gz"

echo "Creating database backup from MariaDB container..."
if [ -f /var/www/html/wp-load.php ] && command -v wp >/dev/null 2>&1; then
  wp --path=/var/www/html db export - | gzip > "${BACKUP_FILE}"
else
  docker compose run --rm wpcli wp db export - | gzip > "${BACKUP_FILE}"
fi

echo "✅ Backup successfully saved to: ${BACKUP_FILE}"
