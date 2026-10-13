#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "=========================================================="
echo " Publishing All Site Pages to Local WordPress Database"
echo "=========================================================="

bash "${SCRIPT_DIR}/create-about-page.sh"
echo ""
bash "${SCRIPT_DIR}/create-ministries-page.sh"
echo ""
bash "${SCRIPT_DIR}/create-sermons-page.sh"
echo ""
bash "${SCRIPT_DIR}/create-prayers-page.sh"
echo ""
bash "${SCRIPT_DIR}/create-contact-page.sh"

echo ""
echo "=========================================================="
echo " ✅ All pages updated in WordPress database successfully!"
echo "=========================================================="
