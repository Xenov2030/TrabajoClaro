#!/usr/bin/env bash
set -euo pipefail
cd /workspace/TrabajoClaro
docker compose up -d db
docker compose run --rm app php artisan migrate --force
docker compose up -d app scheduler
check_file=$(mktemp /tmp/trabajoclaro-ready.XXXXXX)
trap 'rm -f "$check_file"' EXIT
curl --noproxy 127.0.0.1 --retry 10 --retry-connrefused --retry-all-errors --retry-delay 1 --fail --silent --show-error \
    http://127.0.0.1:8000/ -o "$check_file"
rg -q 'Probar demo' "$check_file"
echo 'TrabajoClaro: landing disponible y MySQL con migraciones aplicadas.'
