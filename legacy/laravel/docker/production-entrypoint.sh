#!/usr/bin/env sh
set -eu
# Las migraciones se ejecutan explícitamente antes de habilitar la versión.
if [ "$1" = "apache2-foreground" ]; then
    test -n "${APP_KEY:-}" || { echo 'Falta APP_KEY en el entorno.' >&2; exit 1; }
    mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    php artisan config:cache
    php artisan view:cache
fi
exec "$@"
