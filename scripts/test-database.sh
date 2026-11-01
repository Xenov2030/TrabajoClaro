#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
container="tc-db-test-$$"
trap 'docker rm -f "$container" >/dev/null 2>&1 || true' EXIT
docker run -d --name "$container" --tmpfs /var/lib/postgresql/data -e POSTGRES_HOST_AUTH_METHOD=trust postgres:17 >/dev/null
for attempt in {1..30}; do
 if docker exec "$container" pg_isready -U postgres >/dev/null 2>&1; then break; fi
 sleep 1
done
docker exec -i "$container" psql -U postgres -v ON_ERROR_STOP=1 < tests/database/bootstrap.sql
docker exec -i "$container" psql -U postgres -v ON_ERROR_STOP=1 < supabase/migrations/202611010001_trabajoclaro.sql
docker exec -i "$container" psql -U postgres -v ON_ERROR_STOP=1 < tests/database/workflow.sql
