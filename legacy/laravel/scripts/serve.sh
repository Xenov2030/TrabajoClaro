#!/usr/bin/env bash
# Servidor local solamente. Oculta tokens de los logs del servidor integrado.
set -euo pipefail
php artisan serve --host=0.0.0.0 --port=8000 2>&1 | sed -u -E 's@/p/[a-f0-9]{64}@/p/[privado]@g'
