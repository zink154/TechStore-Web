#!/bin/bash
set -e

# Cache configuration for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Idempotent seed (only if no data exists yet)
php artisan db:seed --force

# Adapt Apache port to Render's PORT env var
if [ ! -z "$PORT" ]; then
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
fi

# Start Apache
exec apache2-foreground
