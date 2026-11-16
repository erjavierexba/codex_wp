#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose exec -T wordpress sh -c 'find wp-content/themes/oldbearstudios wp-content/plugins/oldbear-studio /opt/oldbear-tests -name "*.php" -print0 | xargs -0 -n1 php -l'
docker compose run --rm cli eval-file /opt/oldbear-tests/integration.php
