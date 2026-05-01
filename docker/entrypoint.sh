#!/usr/bin/env bash
set -euo pipefail

# Ensure the SQLite volume mount exists and is writable (Fly mounts /data).
mkdir -p /data
[ -f /data/database.sqlite ] || touch /data/database.sqlite
chown -R www-data:www-data /data /app/storage /app/bootstrap/cache

# First-boot seed: if the DB is empty, migrate + seed. Subsequent boots keep state.
TABLES=$(sqlite3 /data/database.sqlite ".tables" 2>/dev/null || echo "")
if [ -z "${TABLES}" ]; then
    echo "[entrypoint] Empty database — running fresh migrate + seed."
    php artisan migrate --force --no-interaction
    php artisan db:seed --force --no-interaction
else
    echo "[entrypoint] Existing database detected — running migrations."
    php artisan migrate --force --no-interaction || true
fi

# Cache config / routes / views for prod.
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
