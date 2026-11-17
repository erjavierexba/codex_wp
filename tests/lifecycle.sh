#!/usr/bin/env bash
# Run only in the fresh CI installation. Verify stop, restart, reinstall and purge.
set -euo pipefail
source "$(dirname -- "${BASH_SOURCE[0]}")/../scripts/common.sh"
oldbear_runtime
export OLDBEAR_NO_OPEN=1
cd "$OLDBEAR_ROOT"
original_home="$(oldbear_compose run --rm --no-deps cli wp option get page_on_front)"
original_credentials="$(sha256sum .env)"
bash Desinstalar.sh --keep-data
test -f data/wordpress/wp-load.php
test -f data/.oldbear-managed
bash Iniciar.sh
test "$original_home" = "$(oldbear_compose run --rm --no-deps cli wp option get page_on_front)"
bash Instalar.sh
test "$original_credentials" = "$(sha256sum .env)"
test "$original_home" = "$(oldbear_compose run --rm --no-deps cli wp option get page_on_front)"
printf 'BORRAR OLDBEAR\n' | bash Desinstalar.sh --purge
test ! -e data/wordpress
test ! -e data/mysql
test ! -f .env
printf 'Instalación, reinicio, conservación y borrado verificados.\n'
