#!/bin/sh
set -e

# Environment variables come from docker compose, so caches are built at start, not at image build.
if [ "$APP_ENV" = "production" ]; then
    php artisan optimize --no-interaction
    php artisan filament:optimize --no-interaction || true
fi

# Only the api service migrates (RUN_MIGRATIONS=true), so workers never race it.
if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

exec "$@"
