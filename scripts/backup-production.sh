#!/usr/bin/env bash
# Uso: backup-production.sh /ruta/protegida/beta.env /directorio/privado/backups
set -euo pipefail
umask 077
repo_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
prod_env_file=$(realpath "${1:?Falta la ruta del archivo de entorno}")
backup_dir=${2:?Falta el directorio de respaldos}
mkdir -p "$backup_dir"
backup_dir=$(realpath "$backup_dir")
case "$backup_dir/" in "$repo_dir/"*) echo 'Guardá los respaldos fuera del repositorio y del directorio web.' >&2; exit 1;; esac
backup_file=$(mktemp "$backup_dir/trabajoclaro-$(date -u +%Y%m%dT%H%M%SZ).XXXXXX.sql.gz")
trap 'rm -f "$backup_file"' ERR
cd "$repo_dir"
docker compose --env-file "$prod_env_file" -p "${COMPOSE_PROJECT_NAME:-trabajoclaro-beta}" -f compose.production.yaml exec -T db \
    sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysqldump --single-transaction --quick --no-tablespaces --set-gtid-purged=OFF -u "$MYSQL_USER" "$MYSQL_DATABASE"' \
    | gzip > "$backup_file"
gzip -t "$backup_file"
echo "Respaldo creado en: $backup_file"
