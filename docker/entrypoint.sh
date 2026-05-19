#!/usr/bin/env bash
set -e

# Render injects RENDER_EXTERNAL_URL; fallback for local builds
export APP_URL="${RENDER_EXTERNAL_URL:-http://localhost:${PORT:-8000}"
export VITE_ASSET_URL="${APP_URL}"

php artisan config:cache   # optional cache warm‑up
php artisan storage:link   # if you serve files from storage/app/public
php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
