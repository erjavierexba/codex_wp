#!/usr/bin/env bash
set -euo pipefail
source "$(dirname -- "${BASH_SOURCE[0]}")/scripts/common.sh"
oldbear_env_required
oldbear_runtime
oldbear_compose ps
oldbear_compose logs --tail=60 wordpress db mailpit
