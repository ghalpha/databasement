#!/bin/sh
set -e

if [ "$APP_ENV" = "production" ]; then
    php artisan optimize
    php artisan migrate --force
    php artisan octane:frankenphp --host=0.0.0.0 --port=8000
else
    php artisan migrate --force
    docker-php-entrypoint --config /etc/frankenphp/Caddyfile --adapter caddyfile
fi
