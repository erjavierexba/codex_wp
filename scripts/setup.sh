#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
test -f .env || { echo 'Copia .env.example a .env y configura tus datos.' >&2; exit 1; }
docker compose config --quiet
docker compose up -d db wordpress mailpit
ready=0
for attempt in $(seq 1 30); do
  if docker compose run --rm cli wp core version >/dev/null 2>&1; then ready=1; break; fi
  sleep 2
done
test "$ready" = 1 || { docker compose run --rm cli wp core version; echo 'WordPress no está listo. Revisa docker compose logs.' >&2; exit 1; }
docker compose run --rm --entrypoint sh cli -c '
set -eu
if ! wp core is-installed >/dev/null 2>&1; then
  test "${#WP_ADMIN_PASSWORD}" -ge 16 || { echo "Usa una contraseña de al menos 16 caracteres." >&2; exit 1; }
  case "$WP_ADMIN_PASSWORD" in change-this*) echo "Cambia la contraseña de ejemplo." >&2; exit 1;; esac
  test "$WP_ADMIN_EMAIL" != "you@example.com" || { echo "Configura tu correo en .env." >&2; exit 1; }
  printf "%s\n" "$WP_ADMIN_PASSWORD" | wp core install --url="$WP_URL" --title="OldBearStudios" --admin_user="$WP_ADMIN_USER" --admin_email="$WP_ADMIN_EMAIL" --skip-email --prompt=admin_password
  wp option add oldbear_fresh_install 1
fi
wp eval-file /opt/oldbear-tests/bootstrap.php
'
echo 'WordPress preparado. Consulta WP_URL en .env; el panel está en /wp-admin/.'
