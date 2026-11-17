#!/usr/bin/env bash
set -euo pipefail
source "$(dirname -- "${BASH_SOURCE[0]}")/common.sh"
oldbear_env_required
oldbear_runtime
oldbear_compose exec -T wordpress sh -c 'find wp-content/themes/oldbearstudios wp-content/plugins/oldbear-studio /opt/oldbear-tests -name "*.php" -print0 | xargs -0 -n1 php -l'
oldbear_compose run --rm --no-deps cli wp eval-file /opt/oldbear-tests/integration.php

