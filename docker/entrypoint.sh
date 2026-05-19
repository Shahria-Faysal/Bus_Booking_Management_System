#!/usr/bin/env bash
set -euo pipefail

if [[ -n "${RENDER_EXTERNAL_URL:-}" ]]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
else
    export APP_URL="http://localhost:${PORT:-8000}"
fi

# Write into .env BEFORE caching
echo "APP_URL=${APP_URL}" >> .env

php artisan migrate --force
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true
php artisan storage:link || true

php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"