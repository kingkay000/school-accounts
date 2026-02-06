#!/bin/bash
set -e

# Ensure storage permissions
echo "Setting permissions..."
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Setup .env if not strictly provided by the environment (HF Spaces injects vars, but Laravel needs a file)
# If .env doesn't exist, we create it.
if [ ! -f .env ]; then
    echo "Creating .env from .env.example"
    cp .env.example .env
fi

# Ensure APP_KEY exists either from environment or .env file
if [ -z "${APP_KEY}" ] && ! grep -qE '^APP_KEY=base64:' .env; then
    echo "APP_KEY missing; generating and persisting to .env"
    GENERATED_KEY=$(php -r 'echo "base64:".base64_encode(random_bytes(32));')
    if grep -qE '^APP_KEY=' .env; then
        sed -i "s#^APP_KEY=.*#APP_KEY=${GENERATED_KEY}#" .env
    else
        echo "APP_KEY=${GENERATED_KEY}" >> .env
    fi
fi

# Run migrations (safe to run on every deploy)
echo "Running migrations..."
php artisan migrate --force


# Cache config/routes/views
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM in background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx..."
nginx -g "daemon off;"
