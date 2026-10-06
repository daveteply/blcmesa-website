#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs -d '\n')
fi

BACKUP_DIR="${PROJECT_ROOT}/backups"
mkdir -p "${BACKUP_DIR}"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/blcmesa_db_${TIMESTAMP}.sql.gz"

echo "Creating database backup from MariaDB container..."
docker compose run --rm wpcli wp db export - | gzip > "${BACKUP_FILE}"

echo "✅ Backup successfully saved to: ${BACKUP_FILE}"
