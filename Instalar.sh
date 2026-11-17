#!/usr/bin/env bash
set -euo pipefail
source "$(dirname -- "${BASH_SOURCE[0]}")/scripts/common.sh"
oldbear_title
test "$(uname -s)" = Linux || oldbear_die 'Este instalador está preparado para Linux.'
if ! command -v docker >/dev/null || ! docker compose version >/dev/null 2>&1 || ! command -v openssl >/dev/null; then
    bash "$OLDBEAR_ROOT/scripts/install-docker.sh"
fi
oldbear_runtime
oldbear_write_env
oldbear_prepare_data
oldbear_compose config --quiet
printf 'Preparando WordPress, PHP, Apache, MariaDB y correo de prueba…\n'
oldbear_compose pull
oldbear_compose up -d db wordpress mailpit
oldbear_wait_core
oldbear_compose run --rm --no-deps --entrypoint sh cli -c '
set -eu
if ! wp core is-installed >/dev/null 2>&1; then
  test "${#WP_ADMIN_PASSWORD}" -ge 16 || { echo "Usa una contraseña de al menos 16 caracteres." >&2; exit 1; }
  case "$WP_ADMIN_PASSWORD" in change-this*) echo "Cambia la contraseña de ejemplo." >&2; exit 1;; esac
  test "$WP_ADMIN_EMAIL" != "you@example.com" || { echo "Configura tu correo en .env." >&2; exit 1; }
  printf "%s\n" "$WP_ADMIN_PASSWORD" | wp core install --url="$WP_URL" --title="OldBearStudios" --admin_user="$WP_ADMIN_USER" --admin_email="$WP_ADMIN_EMAIL" --skip-email --prompt=admin_password
  wp option add oldbear_fresh_install 1
fi
wp eval-file /opt/oldbear-tests/bootstrap.php
if ! wp plugin is-installed elementor; then wp plugin install elementor --version=3.34.2; fi
wp plugin activate elementor
wp oldbear studio-demo
'
printf '\nListo. Tu web: %s/\nPanel: %s/wp-admin/\n' "$(oldbear_value WP_URL)" "$(oldbear_value WP_URL)"
if test -f "$OLDBEAR_ROOT/ACCESO.txt"; then printf 'El usuario y la contraseña están en ACCESO.txt, dentro de esta carpeta.\n'; fi
printf 'Para cerrar: bash Detener.sh · Para volver a abrir: bash Iniciar.sh\n'
oldbear_open
