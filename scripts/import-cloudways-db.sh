#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# Import a Manual Cloudways Database Export into Local WordPress
# ==============================================================================
# Workflow:
#   1. Export the database from Cloudways Admin Portal:
#      Application -> Access Details -> Launch Database Manager -> Export
#      (Format: SQL, Output: gzip or open/save) and save to backups/
#   2. Run this script inside the VS Code Dev Container (or WSL):
#      bash scripts/import-cloudways-db.sh backups/<exported-file>.sql.gz
#      (or: bash scripts/import-cloudways-db.sh --latest)
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

# Load .env safely (matches backup-db.sh / export-for-hosting.sh)
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
LOCAL_URL="${WP_SITE_URL:-http://localhost:8080}"
LOCAL_URL="${LOCAL_URL%/}"

INPUT_FILE=""
USE_LATEST=false
BACKUP_ONLY=false
SKIP_LOCAL_BACKUP=false
DRY_RUN=false
REMOTE_URL_OVERRIDE=""
UPLOADS_ARCHIVE=""

usage() {
  cat <<EOF
Usage: bash scripts/import-cloudways-db.sh [OPTIONS] [<path-to-export.sql|.sql.gz|.zip>]

Imports a manual Cloudways Database Manager / phpMyAdmin SQL export into local
WordPress, automatically rewriting URLs, matching table prefixes, disabling
Cloudways-only cache plugins locally, and saving a local rollback snapshot first.

Options:
  --latest                Auto-select the newest Cloudways export in backups/
  --backup-only           Save/compress the export into backups/ without importing locally
  --remote-url <URL>      Override auto-detected remote site URL (e.g. https://blcmesa.org)
  --uploads <zip|dir>     Optional: also sync a downloaded wp-content/uploads zip or folder
  --no-local-backup       Skip taking a pre-import safety backup of your local DB
  --dry-run               Inspect the export file & show planned actions without changing DB
  -h, --help              Show this help message

Examples:
  bash scripts/import-cloudways-db.sh backups/cloudways-export.sql.gz
  bash scripts/import-cloudways-db.sh --latest
  bash scripts/import-cloudways-db.sh --backup-only ~/Downloads/wordpress.sql
  bash scripts/import-cloudways-db.sh --dry-run backups/cloudways-export.sql.gz
EOF
}

while [ $# -gt 0 ]; do
  case "$1" in
    --latest)
      USE_LATEST=true
      shift
      ;;
    --backup-only)
      BACKUP_ONLY=true
      shift
      ;;
    --no-local-backup)
      SKIP_LOCAL_BACKUP=true
      shift
      ;;
    --dry-run)
      DRY_RUN=true
      shift
      ;;
    --remote-url)
      if [ -z "${2:-}" ]; then
        echo "Error: --remote-url requires a URL argument." >&2
        exit 1
      fi
      REMOTE_URL_OVERRIDE="${2%/}"
      shift 2
      ;;
    --uploads)
      if [ -z "${2:-}" ]; then
        echo "Error: --uploads requires a path to a .zip archive or directory." >&2
        exit 1
      fi
      UPLOADS_ARCHIVE="$2"
      shift 2
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    -*)
      echo "Error: Unknown option '$1'" >&2
      echo ""
      usage
      exit 1
      ;;
    *)
      if [ -n "${INPUT_FILE}" ]; then
        echo "Error: Multiple input files provided ('${INPUT_FILE}' and '$1')." >&2
        exit 1
      fi
      INPUT_FILE="$1"
      shift
      ;;
  esac
done

mkdir -p "${BACKUP_DIR}"

# Locate latest export in backups/ if --latest was specified
if [ "${USE_LATEST}" = true ] && [ -z "${INPUT_FILE}" ]; then
  INPUT_FILE="$(
    find "${BACKUP_DIR}" -maxdepth 1 -type f \
      \( -name '*.sql' -o -name '*.sql.gz' -o -name '*.zip' \) \
      ! -name 'pre_import_local_*' \
      ! -name 'blcmesa_db_*' \
      ! -name 'blcmesa_migration_package_*' \
      ! -name 'blcmesa-theme_*' \
      -printf '%T@ %p\n' 2>/dev/null \
    | sort -nr \
    | head -n 1 \
    | cut -d' ' -f2- || true
  )"
  if [ -z "${INPUT_FILE}" ]; then
    echo "Error: No Cloudways SQL export (.sql, .sql.gz, or .zip) found in backups/." >&2
    echo "Export your database from Cloudways Database Manager and save it in backups/ first." >&2
    exit 1
  fi
fi

if [ -z "${INPUT_FILE}" ]; then
  usage
  echo ""
  echo "Available SQL/archive files in backups/:"
  ls -lh "${BACKUP_DIR}"/*.sql "${BACKUP_DIR}"/*.sql.gz "${BACKUP_DIR}"/*.zip 2>/dev/null || echo "  (none found)"
  exit 1
fi

if [ ! -f "${INPUT_FILE}" ]; then
  echo "Error: File '${INPUT_FILE}' does not exist." >&2
  exit 1
fi

# Resolve absolute path of input file
INPUT_ABS="$(cd "$(dirname "${INPUT_FILE}")" && pwd)/$(basename "${INPUT_FILE}")"
TIMESTAMP="$(date +"%Y%m%d_%H%M%S")"
TEMP_DIR="${PROJECT_ROOT}/tmp/cloudways_import_${TIMESTAMP}"
WORK_SQL="${TEMP_DIR}/import.sql"
mkdir -p "${TEMP_DIR}"
trap 'rm -rf "${TEMP_DIR}"' EXIT

echo "=========================================================="
echo " Cloudways Database Import -> Local WordPress"
echo "=========================================================="
echo " Source file: ${INPUT_ABS}"
echo " Local URL:   ${LOCAL_URL}"
echo ""

# 1. Extract SQL stream from .sql, .sql.gz, or .zip
echo "1. Extracting and sanitizing SQL dump..."
case "${INPUT_ABS}" in
  *.sql.gz|*.gz)
    gunzip -c "${INPUT_ABS}" > "${WORK_SQL}.raw"
    ;;
  *.zip)
    if ! command -v unzip >/dev/null 2>&1; then
      echo "Error: 'unzip' is required to extract .zip SQL archives." >&2
      exit 1
    fi
    SQL_ENTRY="$(unzip -Z1 "${INPUT_ABS}" | grep -iE '\.sql$' | head -n 1 || true)"
    if [ -z "${SQL_ENTRY}" ]; then
      echo "Error: No .sql file found inside '${INPUT_ABS}'." >&2
      exit 1
    fi
    echo "   Found SQL entry inside zip: ${SQL_ENTRY}"
    unzip -p "${INPUT_ABS}" "${SQL_ENTRY}" > "${WORK_SQL}.raw"
    ;;
  *.sql)
    cp "${INPUT_ABS}" "${WORK_SQL}.raw"
    ;;
  *)
    echo "Error: Unsupported file extension. Expected .sql, .sql.gz, or .zip." >&2
    exit 1
    ;;
esac

if [ ! -s "${WORK_SQL}.raw" ]; then
  echo "Error: Extracted SQL dump is empty." >&2
  exit 1
fi

# Archive a compressed copy into backups/ if the source file lives outside backups/
ARCHIVED_PATH="${INPUT_ABS}"
case "${INPUT_ABS}" in
  "${BACKUP_DIR}"/*)
    echo "   Backup already stored in backups/: $(basename "${INPUT_ABS}")"
    ;;
  *)
    ARCHIVED_PATH="${BACKUP_DIR}/cloudways_db_${TIMESTAMP}.sql.gz"
    if [ "${DRY_RUN}" = true ]; then
      echo "   [dry-run] Would archive compressed copy to: ${ARCHIVED_PATH}"
    else
      gzip -c "${WORK_SQL}.raw" > "${ARCHIVED_PATH}"
      echo "   Archived compressed copy to: ${ARCHIVED_PATH}"
    fi
    ;;
esac

if [ "${BACKUP_ONLY}" = true ]; then
  echo ""
  echo "✅ Backup archived at: ${ARCHIVED_PATH} (--backup-only mode; local DB untouched)."
  exit 0
fi

# Sanitize SQL for smooth local MariaDB import:
# - Strip MariaDB 10.11/11 sandbox directive that can break older mysql/wpcli clients
# - Strip CREATE DATABASE / USE statements targeting remote Cloudways DB names
# - Rewrite DEFINER clauses to CURRENT_USER
# - Map MySQL 8 utf8mb4_0900_ai_ci collation to MariaDB-compatible utf8mb4_unicode_ci
sed -E \
  -e '/enable the sandbox mode/d' \
  -e '/^[[:space:]]*CREATE[[:space:]]+DATABASE[[:space:]]/Id' \
  -e '/^[[:space:]]*USE[[:space:]]+`?[^`;]+`?[[:space:]]*;/Id' \
  -e 's/DEFINER=`[^`]+`@`[^`]+`/DEFINER=CURRENT_USER/g' \
  -e 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' \
  "${WORK_SQL}.raw" > "${WORK_SQL}"
rm -f "${WORK_SQL}.raw"

# Auto-detect table prefix from CREATE TABLE `<prefix>options`
DETECTED_PREFIX="$(
  grep -m1 -iE 'CREATE TABLE( IF NOT EXISTS)? `?[^`[:space:]]+options`?' "${WORK_SQL}" \
    | sed -E 's/.*CREATE TABLE( IF NOT EXISTS)? `?([^`[:space:]]+)options`?.*/\2/I' || true
)"
echo "   Detected table prefix in dump: ${DETECTED_PREFIX:-'(unknown — will check after import)'}"

# Automatically pick local WP-CLI inside Dev Container or docker compose on host
if [ -f /var/www/html/wp-load.php ] && command -v wp >/dev/null 2>&1; then
  WP_CLI=(wp --path=/var/www/html)
else
  WP_CLI=(docker compose run --rm -T wpcli wp)
fi

LOCAL_PREFIX="$("${WP_CLI[@]}" config get table_prefix 2>/dev/null | tr -d '\r' || echo "wp_")"
echo "   Current local table prefix:    ${LOCAL_PREFIX}"

if [ "${DRY_RUN}" = true ]; then
  echo ""
  echo "[dry-run] Planned steps (no changes made):"
  if [ "${SKIP_LOCAL_BACKUP}" = false ]; then
    echo "  - Save local rollback snapshot to: ${BACKUP_DIR}/pre_import_local_${TIMESTAMP}.sql.gz"
  fi
  echo "  - Reset local MariaDB and import sanitized SQL from: ${ARCHIVED_PATH}"
  if [ -n "${DETECTED_PREFIX}" ] && [ "${DETECTED_PREFIX}" != "${LOCAL_PREFIX}" ]; then
    echo "  - Update local wp-config.php table_prefix: ${LOCAL_PREFIX} -> ${DETECTED_PREFIX}"
  fi
  echo "  - Auto-detect remote siteurl/home and run serialized wp search-replace -> ${LOCAL_URL}"
  echo "  - Deactivate Cloudways-only cache/security plugins locally and flush caches/permalinks"
  if [ -n "${UPLOADS_ARCHIVE}" ]; then
    echo "  - Sync uploads from: ${UPLOADS_ARCHIVE}"
  fi
  exit 0
fi

# 2. Save safety snapshot of local DB before overwriting
ROLLBACK_FILE=""
if [ "${SKIP_LOCAL_BACKUP}" = false ]; then
  ROLLBACK_FILE="${BACKUP_DIR}/pre_import_local_${TIMESTAMP}.sql.gz"
  echo ""
  echo "2. Saving local safety snapshot before overwriting..."
  if "${WP_CLI[@]}" db export - 2>/dev/null | gzip > "${ROLLBACK_FILE}" && [ -s "${ROLLBACK_FILE}" ]; then
    echo "   Local safety snapshot saved: ${ROLLBACK_FILE}"
  else
    rm -f "${ROLLBACK_FILE}"
    ROLLBACK_FILE=""
    echo "   (Local DB appears empty or uninitialized — skipped safety snapshot.)"
  fi
else
  echo ""
  echo "2. Skipping local safety snapshot (--no-local-backup)."
fi

# 3. Reset local DB and import sanitized Cloudways SQL
echo ""
echo "3. Resetting local MariaDB and importing Cloudways dump..."
"${WP_CLI[@]}" db reset --yes --quiet || true
"${WP_CLI[@]}" db import - < "${WORK_SQL}"

# 4. Sync local wp-config.php table_prefix if Cloudways uses a custom prefix
if [ -z "${DETECTED_PREFIX}" ]; then
  OPTIONS_TABLE="$("${WP_CLI[@]}" db query "SHOW TABLES LIKE '%options';" --skip-column-names 2>/dev/null | tr -d '\r' | head -n 1 || true)"
  if [ -n "${OPTIONS_TABLE}" ]; then
    DETECTED_PREFIX="${OPTIONS_TABLE%options}"
  fi
fi

if [ -n "${DETECTED_PREFIX}" ] && [ "${DETECTED_PREFIX}" != "${LOCAL_PREFIX}" ]; then
  echo ""
  echo "4. Updating local wp-config.php table_prefix (${LOCAL_PREFIX} -> ${DETECTED_PREFIX})..."
  "${WP_CLI[@]}" config set table_prefix "${DETECTED_PREFIX}" --type=variable --quiet
else
  echo ""
  echo "4. Local table prefix already matches (${LOCAL_PREFIX})."
fi

# 5. Auto-detect remote URLs and run serialized search-replace
echo ""
echo "5. Rewriting remote URLs to ${LOCAL_URL}..."
REMOTE_SITEURL="${REMOTE_URL_OVERRIDE:-$("${WP_CLI[@]}" option get siteurl 2>/dev/null | tr -d '\r' || true)}"
REMOTE_HOME="$("${WP_CLI[@]}" option get home 2>/dev/null | tr -d '\r' || true)"
REMOTE_SITEURL="${REMOTE_SITEURL%/}"
REMOTE_HOME="${REMOTE_HOME%/}"

replace_url() {
  local from_url="$1"
  local to_url="$2"
  if [ -z "${from_url}" ] || [ "${from_url}" = "${to_url}" ]; then
    return 0
  fi
  echo "   Replacing ${from_url} -> ${to_url}"
  "${WP_CLI[@]}" search-replace "${from_url}" "${to_url}" --all-tables --precise --skip-columns=guid --quiet

  # Also catch opposite scheme (http vs https) for mixed-scheme links in post content
  if [[ "${from_url}" == https://* ]]; then
    local http_variant="http://${from_url#https://}"
    if [ "${http_variant}" != "${to_url}" ]; then
      "${WP_CLI[@]}" search-replace "${http_variant}" "${to_url}" --all-tables --precise --skip-columns=guid --quiet
    fi
  elif [[ "${from_url}" == http://* ]]; then
    local https_variant="https://${from_url#http://}"
    if [ "${https_variant}" != "${to_url}" ]; then
      "${WP_CLI[@]}" search-replace "${https_variant}" "${to_url}" --all-tables --precise --skip-columns=guid --quiet
    fi
  fi
}

replace_url "${REMOTE_SITEURL}" "${LOCAL_URL}"
if [ -n "${REMOTE_HOME}" ] && [ "${REMOTE_HOME}" != "${REMOTE_SITEURL}" ]; then
  replace_url "${REMOTE_HOME}" "${LOCAL_URL}"
fi

"${WP_CLI[@]}" option update siteurl "${LOCAL_URL}" --quiet
"${WP_CLI[@]}" option update home "${LOCAL_URL}" --quiet

# 6. Local-dev hygiene: deactivate Cloudways-only plugins, flush caches & permalinks
echo ""
echo "6. Applying local development hygiene..."
LOCAL_DEACTIVATE_PLUGINS=(
  breeze
  object-cache-pro
  redis-cache
  malcare-security
  bv-cloudways-automated-migration
  wp-rocket
  wordfence
)
for plugin in "${LOCAL_DEACTIVATE_PLUGINS[@]}"; do
  if "${WP_CLI[@]}" plugin is-active "${plugin}" 2>/dev/null; then
    echo "   Deactivating production-only plugin locally: ${plugin}"
    "${WP_CLI[@]}" plugin deactivate "${plugin}" --quiet || true
  fi
done

# Ensure local site is discouraged from search indexing and caches/permalinks are clean
"${WP_CLI[@]}" option update blog_public 0 --quiet || true
"${WP_CLI[@]}" cache flush --quiet || true
"${WP_CLI[@]}" rewrite flush --hard --quiet || true

# 7. Optional uploads archive/folder sync
if [ -n "${UPLOADS_ARCHIVE}" ]; then
  echo ""
  echo "7. Syncing media uploads from: ${UPLOADS_ARCHIVE}..."
  mkdir -p "${PROJECT_ROOT}/wp-content/uploads"
  if [ -d "${UPLOADS_ARCHIVE}" ]; then
    cp -ru "${UPLOADS_ARCHIVE%/}"/. "${PROJECT_ROOT}/wp-content/uploads/"
    echo "   Merged uploads directory into wp-content/uploads/."
  elif [ -f "${UPLOADS_ARCHIVE}" ] && [[ "${UPLOADS_ARCHIVE}" == *.zip ]]; then
    if ! command -v unzip >/dev/null 2>&1; then
      echo "   Warning: 'unzip' not found; skipped uploads extraction." >&2
    else
      unzip -qo "${UPLOADS_ARCHIVE}" -d "${PROJECT_ROOT}/wp-content/uploads/"
      echo "   Extracted uploads archive into wp-content/uploads/."
    fi
  else
    echo "   Warning: Unsupported --uploads path '${UPLOADS_ARCHIVE}' (expected directory or .zip)." >&2
  fi
fi

echo ""
echo "=========================================================="
echo " ✅ Cloudways database imported into local WordPress!"
echo "=========================================================="
echo " - Local Site:     ${LOCAL_URL}"
echo " - Local Admin:    ${LOCAL_URL}/wp-admin/"
echo " - Remote URL:     ${REMOTE_SITEURL:-'(unknown)'} -> ${LOCAL_URL}"
echo " - Table Prefix:   ${DETECTED_PREFIX:-${LOCAL_PREFIX}}"
echo " - Saved Backup:   ${ARCHIVED_PATH}"
if [ -n "${ROLLBACK_FILE}" ]; then
  echo " - Rollback Cmd:   bash scripts/restore-db.sh ${ROLLBACK_FILE}"
fi
echo ""
echo " Note: Local WP Admin logins now match the Cloudways site's users."
echo "=========================================================="
