#!/usr/bin/env bash
set -euo pipefail
test "$(uname -s)" = Linux || { echo 'Este instalador requiere Linux.' >&2; exit 1; }
test -r /etc/os-release || { echo 'No se puede identificar la distribución Linux.' >&2; exit 1; }
source /etc/os-release
case "${ID:-} ${ID_LIKE:-}" in
    *ubuntu*) ;;
    *) printf 'En esta distribución, instala Docker Engine y Compose v2 siguiendo https://docs.docker.com/engine/install/ y vuelve a ejecutar Instalar.sh. La preparación automática de requisitos está pensada para Ubuntu 24.04 y derivados.\n' >&2; exit 1;;
esac
command -v sudo >/dev/null || { echo 'Se necesita sudo para instalar los requisitos.' >&2; exit 1; }
printf 'Se instalarán Docker y Compose desde los repositorios configurados de Ubuntu.\nEl sistema pedirá tu contraseña de administrador. PHP y MariaDB se ejecutarán en contenedores.\n'
sudo -v
sudo apt-get update
packages=(openssl)
if ! command -v docker >/dev/null; then packages+=(docker.io); fi
if ! docker compose version >/dev/null 2>&1; then
    if apt-cache show docker-compose-v2 >/dev/null 2>&1; then packages+=(docker-compose-v2)
    elif apt-cache show docker-compose-plugin >/dev/null 2>&1; then packages+=(docker-compose-plugin)
    else echo 'No se encuentra Compose v2. Activa universe o instala Docker Compose desde su documentación oficial.' >&2; exit 1; fi
fi
sudo apt-get install -y "${packages[@]}"
sudo systemctl start docker
printf 'Requisitos preparados.\n'
