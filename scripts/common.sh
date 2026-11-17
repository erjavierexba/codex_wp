#!/usr/bin/env bash
# Shared Linux launcher functions. Do not source .env as executable shell code.
set -euo pipefail
OLDBEAR_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)"
DOCKER=(docker)

oldbear_die() { printf '\nError: %s\n' "$*" >&2; exit 1; }
oldbear_title() { printf '\n  OLD BEAR STUDIOS\n  Pequeño estudio. Grandes aventuras.\n\n'; }
oldbear_value() {
    local key="$1"
    test -f "$OLDBEAR_ROOT/.env" || return 0
    awk -v key="$key" 'index($0, key "=") == 1 {sub("^[^=]*=", ""); sub("\r$", ""); print; exit}' "$OLDBEAR_ROOT/.env"
}
oldbear_env_required() { test -f "$OLDBEAR_ROOT/.env" || oldbear_die 'Ejecuta primero: bash Instalar.sh'; }
oldbear_runtime() {
    command -v docker >/dev/null || oldbear_die 'Falta Docker. Ejecuta bash Instalar.sh para preparar los requisitos en Ubuntu.'
    if docker info >/dev/null 2>&1; then
        DOCKER=(docker)
    else
        command -v sudo >/dev/null || oldbear_die 'Docker no responde y no hay sudo. Inicia Docker y vuelve a intentarlo.'
        printf 'Docker necesita permisos del sistema. Linux puede pedirte tu contraseña.\n'
        sudo -v
        if ! sudo docker info >/dev/null 2>&1; then
            command -v systemctl >/dev/null || oldbear_die 'Inicia el servicio de Docker antes de continuar.'
            sudo systemctl start docker
        fi
        sudo docker info >/dev/null 2>&1 || oldbear_die 'No se puede conectar con Docker.'
        DOCKER=(sudo docker)
    fi
    "${DOCKER[@]}" compose version >/dev/null 2>&1 || oldbear_die 'Falta Docker Compose v2. Ejecuta bash scripts/install-docker.sh.'
}
oldbear_compose() {
    (cd "$OLDBEAR_ROOT" && "${DOCKER[@]}" compose --project-directory "$OLDBEAR_ROOT" --env-file "$OLDBEAR_ROOT/.env" -f "$OLDBEAR_ROOT/compose.yaml" "$@")
}
oldbear_safe_data() {
    test ! -L "$OLDBEAR_ROOT/data" || oldbear_die 'data/ no puede ser un enlace simbólico.'
    if test -d "$OLDBEAR_ROOT/data"; then
        test -f "$OLDBEAR_ROOT/data/.oldbear-managed" && test ! -L "$OLDBEAR_ROOT/data/.oldbear-managed" || oldbear_die 'La carpeta data/ no está identificada como datos de OldBearStudios.'
    fi
    local part
    for part in wordpress mysql; do
        test ! -L "$OLDBEAR_ROOT/data/$part" || oldbear_die "data/$part no puede ser un enlace simbólico."
    done
}
oldbear_prepare_data() {
    oldbear_safe_data
    mkdir -p "$OLDBEAR_ROOT/data"
    if ! test -f "$OLDBEAR_ROOT/data/.oldbear-managed"; then
        (umask 077; printf 'OldBearStudios local data v1\n' > "$OLDBEAR_ROOT/data/.oldbear-managed")
    fi
    mkdir -p "$OLDBEAR_ROOT/data/wordpress" "$OLDBEAR_ROOT/data/mysql"
}
oldbear_write_env() {
    test ! -L "$OLDBEAR_ROOT/.env" || oldbear_die '.env no puede ser un enlace simbólico.'
    test ! -L "$OLDBEAR_ROOT/ACCESO.txt" || oldbear_die 'ACCESO.txt no puede ser un enlace simbólico.'
    if test -f "$OLDBEAR_ROOT/.env"; then return; fi
    command -v openssl >/dev/null || oldbear_die 'Falta openssl para generar contraseñas. Instálalo con el gestor de paquetes de tu distribución.'
    local db_password root_password admin_password instance web_port mail_port
    db_password="$(openssl rand -hex 24)"
    root_password="$(openssl rand -hex 24)"
    admin_password="$(openssl rand -hex 16)"
    instance="oldbear-$(openssl rand -hex 6)"
    web_port="${OLDBEAR_WEB_PORT:-8080}"
    mail_port="${OLDBEAR_MAIL_PORT:-8025}"
    [[ "$web_port" =~ ^[0-9]+$ && "$mail_port" =~ ^[0-9]+$ ]] || oldbear_die 'Los puertos deben ser numéricos.'
    (( web_port >= 1024 && web_port <= 65535 && mail_port >= 1024 && mail_port <= 65535 && web_port != mail_port )) || oldbear_die 'Elige dos puertos distintos entre 1024 y 65535.'
    (umask 077
        printf 'COMPOSE_PROJECT_NAME=%s\nWP_PORT=%s\nMAIL_PORT=%s\nDB_NAME=oldbear\nDB_USER=oldbear\nDB_PASSWORD=%s\nDB_ROOT_PASSWORD=%s\nWP_ADMIN_USER=oldbear\nWP_ADMIN_PASSWORD=%s\nWP_ADMIN_EMAIL=admin@oldbear.test\nWP_URL=http://localhost:%s\n' "$instance" "$web_port" "$mail_port" "$db_password" "$root_password" "$admin_password" "$web_port" > "$OLDBEAR_ROOT/.env"
        printf 'OLDBEARSTUDIOS — ACCESO LOCAL\n\nWeb: http://localhost:%s\nPanel: http://localhost:%s/wp-admin/\nUsuario: oldbear\nContraseña: %s\nCorreo de prueba: http://localhost:%s\n\nEstas credenciales son privadas. El correo admin@oldbear.test solo se usa en este entorno local.\nGuarda este archivo en privado. Si cambias la contraseña en WordPress, actualízala aquí.\n' "$web_port" "$web_port" "$admin_password" "$mail_port" > "$OLDBEAR_ROOT/ACCESO.txt"
    )
}
oldbear_wait_core() {
    local attempt
    for attempt in $(seq 1 30); do
        if oldbear_compose run --rm --no-deps cli wp core version >/dev/null 2>&1; then return; fi
        sleep 2
    done
    oldbear_compose run --rm --no-deps cli wp core version || true
    oldbear_die 'WordPress no está listo. Ejecuta bash Diagnostico.sh.'
}
oldbear_open() {
    if test "${OLDBEAR_NO_OPEN:-0}" != 1 && test -n "${DISPLAY:-}${WAYLAND_DISPLAY:-}" && command -v xdg-open >/dev/null; then
        xdg-open "$(oldbear_value WP_URL)/" >/dev/null 2>&1 &
    fi
}
oldbear_stop() { oldbear_compose down --remove-orphans; }
oldbear_purge_data() {
    oldbear_safe_data
    test -f "$OLDBEAR_ROOT/data/.oldbear-managed" || oldbear_die 'No hay una carpeta de datos gestionada que borrar.'
    # Delete only these two managed bind mounts through an existing application image.
    "${DOCKER[@]}" run --rm --user 0:0 --network none --read-only --entrypoint sh \
        --mount "type=bind,src=$OLDBEAR_ROOT/data,dst=/oldbear-data" \
        wordpress:php8.3-apache -c 'rm -rf -- /oldbear-data/wordpress /oldbear-data/mysql'
    rm -f -- "$OLDBEAR_ROOT/data/.oldbear-managed"
    if ! rmdir -- "$OLDBEAR_ROOT/data" 2>/dev/null; then printf 'Se han conservado otros archivos que había en data/.\n'; fi
    rm -f -- "$OLDBEAR_ROOT/.env" "$OLDBEAR_ROOT/ACCESO.txt"
}
