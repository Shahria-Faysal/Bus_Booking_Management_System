#!/usr/bin/env bash
set -euo pipefail   # exit on error, undefined variable, or pipeline failure

# ----------------------------------------------------------------
# Resolve the public URL of the app.
# Render injects RENDER_EXTERNAL_URL; for local runs we fall back
# to http://localhost:<PORT> (or 8000 if PORT is not set).
# ----------------------------------------------------------------
if [[ -n "${RENDER_EXTERNAL_URL:-}" ]]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
else
    export APP_URL="http://localhost:${PORT:-8000}"
fi

# Vite uses VITE_ASSET_URL to prefix asset URLs; keep it in sync.
export VITE_ASSET_URL="${APP_URL}"

# Optional: pre‑warm Laravel caches (no‑output if already cached)
php artisan config:cache
php artisan route:cache || true   # ignore failure if routes not cached yet
php artisan view:cache  || true

# If you serve files from storage/app/public, ensure the symlink exists.
php artisan storage:link || true

# ----------------------------------------------------------------
# Finally start the Laravel development server.
# Render expects the process to stay in the foreground.
# ----------------------------------------------------------------
php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
