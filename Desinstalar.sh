#!/usr/bin/env bash
set -euo pipefail
source "$(dirname -- "${BASH_SOURCE[0]}")/scripts/common.sh"
oldbear_title
if ! test -f "$OLDBEAR_ROOT/.env"; then printf 'No hay instalación configurada en esta carpeta.\n'; exit 0; fi
mode="${1:-}"
if test -z "$mode"; then
    printf '1) Desinstalar contenedores y conservar web, base de datos y claves.\n2) Borrar también la web, base de datos y claves de esta carpeta.\n0) Cancelar.\n'
    read -r -p 'Elige [1]: ' choice || exit 0
    case "${choice:-1}" in 1) mode=--keep-data;; 2) mode=--purge;; 0) exit 0;; *) oldbear_die 'Opción no válida.';; esac
fi
case "$mode" in --keep-data|--purge) ;; *) oldbear_die 'Usa --keep-data o --purge.';; esac
if test "$mode" = --purge; then
    oldbear_safe_data
    printf '\nSe borrarán los datos y las claves de esta instalación en:\n%s\n' "$OLDBEAR_ROOT"
    read -r -p 'Escribe BORRAR OLDBEAR para confirmar: ' confirmation || exit 0
    if test "$confirmation" != 'BORRAR OLDBEAR'; then printf 'Cancelado. No se ha borrado nada.\n'; exit 0; fi
fi
oldbear_runtime
oldbear_stop
if test "$mode" = --purge; then
    oldbear_purge_data
    printf '\nDesinstalado. Datos y credenciales eliminados; puedes borrar esta carpeta.\n'
else
    printf '\nContenedores eliminados. Datos y claves conservados. Iniciar.sh los recupera.\n'
fi
printf 'Docker y sus imágenes compartidas permanecen disponibles para otras aplicaciones.\n'
