#!/usr/bin/env bash
set -euo pipefail
export OLDBEAR_NO_OPEN=1
exec bash "$(dirname -- "${BASH_SOURCE[0]}")/../Instalar.sh"

