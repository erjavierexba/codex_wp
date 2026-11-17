#!/usr/bin/env bash
set -euo pipefail
source "$(dirname -- "${BASH_SOURCE[0]}")/scripts/common.sh"
oldbear_title
oldbear_env_required
oldbear_runtime
oldbear_prepare_data
oldbear_compose up -d db wordpress mailpit
oldbear_wait_core
printf '\nWeb: %s/\nPanel: %s/wp-admin/\n' "$(oldbear_value WP_URL)" "$(oldbear_value WP_URL)"
oldbear_open
