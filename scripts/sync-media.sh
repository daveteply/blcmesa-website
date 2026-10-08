#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

if command -v python3 >/dev/null 2>&1; then
  python3 "${SCRIPT_DIR}/sync-media.py" "$@"
else
  echo "Error: Python 3 is required to run this script." >&2
  exit 1
fi
