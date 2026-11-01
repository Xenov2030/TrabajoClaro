#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
db="tc-integration-db-$$"
rest="tc-integration-rest-$$"
trap 'docker rm -f "$rest" "$db" >/dev/null 2>&1 || true' EXIT
docker run -d --name "$db" --tmpfs /var/lib/postgresql/data -e POSTGRES_HOST_AUTH_METHOD=trust postgres:17 >/dev/null
for attempt in {1..30}; do
 if docker exec "$db" pg_isready -U postgres >/dev/null 2>&1; then break; fi
 sleep 1
done
for file in tests/database/bootstrap.sql supabase/migrations/202611010001_trabajoclaro.sql tests/database/browser-seed.sql; do
 docker exec -i "$db" psql -U postgres -v ON_ERROR_STOP=1 < "$file" >/dev/null
done
docker run -d --name "$rest" --link "$db":db -p 127.0.0.1:54320:3000 \
 -e PGRST_DB_URI=postgres://postgres@db/postgres -e PGRST_DB_SCHEMAS=public -e PGRST_DB_ANON_ROLE=anon \
 -e PGRST_JWT_SECRET=trabajoclaro-test-only-secret-at-least-32-bytes postgrest/postgrest:v13.0.8 >/dev/null
for attempt in {1..30}; do
 if curl --fail --silent http://127.0.0.1:54320/ >/dev/null; then break; fi
 sleep 1
done
npx playwright test --config=playwright.database.config.js
