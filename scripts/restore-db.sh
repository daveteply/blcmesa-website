#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

if [ -z "${1:-}" ]; then
  echo "Usage: $0 <path_to_backup_file.sql or .sql.gz>"
  echo "Available backups in backups/:"
  ls -lh "${PROJECT_ROOT}/backups" 2>/dev/null || echo "No backups found."
  exit 1
fi

RESTORE_FILE="$1"

if [ ! -f "${RESTORE_FILE}" ]; then
  echo "Error: File '${RESTORE_FILE}' does not exist."
  exit 1
fi

echo "Restoring database from: ${RESTORE_FILE}..."

if [ -f /var/www/html/wp-load.php ] && command -v wp >/dev/null 2>&1; then
  WP_IMPORT="wp --path=/var/www/html db import -"
else
  WP_IMPORT="docker compose run --rm -T wpcli wp db import -"
fi

if [[ "${RESTORE_FILE}" == *.gz ]]; then
  gunzip -c "${RESTORE_FILE}" | ${WP_IMPORT}
else
  ${WP_IMPORT} < "${RESTORE_FILE}"
fi

echo "✅ Database restore completed successfully!"
