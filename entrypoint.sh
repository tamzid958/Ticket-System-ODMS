#!/bin/bash
set -e

echo "[Entrypoint] Running Laravel migrations..."
php artisan migrate --force || true
php artisan shield:generate --all --panel panel || true
php artisan db:seed || true

php artisan optimize:clear
php artisan optimize

exec "$@"
