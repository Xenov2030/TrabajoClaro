#!/usr/bin/env bash
# Preparación local repetible para el entorno administrado; no despliega.
set -euo pipefail
cd /workspace/TrabajoClaro
python3 - <<'PY'
from pathlib import Path
import secrets
p=Path('.env')
if not p.exists():
    s=Path('.env.example').read_text()
    s=s.replace('DB_PASSWORD=\n','DB_PASSWORD='+secrets.token_hex(24)+'\n')
    s=s.replace('DB_ROOT_PASSWORD=\n','DB_ROOT_PASSWORD='+secrets.token_hex(24)+'\n')
    p.write_text(s)
    p.chmod(0o600)
PY
if [ -n "${CODEX_PROXY_CERT:-}" ]; then
    docker build --secret "id=proxy_ca,src=$CODEX_PROXY_CERT" -f docker/Dockerfile -t trabajoclaro-php .
else
    docker build -f docker/Dockerfile -t trabajoclaro-php .
fi
docker compose up -d db
docker compose run --rm --no-deps \
    -v /etc/ssl/certs/ca-certificates.crt:/run/ca.pem:ro \
    -e COMPOSER_CAFILE=/run/ca.pem -e GIT_SSL_CAINFO=/run/ca.pem \
    -e COMPOSER_HOME=/app/.composer-local \
    app composer install --prefer-source --no-interaction
npm ci --no-audit --no-fund
npm run build
if ! python3 - <<'PY'
from pathlib import Path
import sys
present=any(line.startswith('APP_KEY=') and line.split('=',1)[1].strip().strip("\"'") for line in Path('.env').read_text().splitlines())
sys.exit(0 if present else 1)
PY
then
    docker compose run --rm --no-deps app php artisan key:generate
fi
docker compose run --rm app php artisan migrate --force
