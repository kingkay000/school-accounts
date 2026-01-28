#!/bin/bash

# Ensure storage permissions
echo "Setting permissions..."
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Setup .env if not strictly provided by the environment (HF Spaces injects vars, but Laravel needs a file)
# If .env doesn't exist, we create it.
if [ ! -f .env ]; then
    echo "Creating .env from .env.example"
    cp .env.example .env
    # Generate key
    php artisan key:generate
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
