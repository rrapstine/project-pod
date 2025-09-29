#!/bin/bash
set -e

echo "Starting Laravel application setup..."

# Copy mounted .env file if it exists
if [ -f /tmp/.env.host ]; then
    echo "Copying .env from host..."
    cp /tmp/.env.host /var/www/html/.env
else
    echo "No .env.host found, checking if .env already exists..."
    if [ ! -f /var/www/html/.env ]; then
        echo "Creating .env from .env.example..."
        cp /var/www/html/.env.example /var/www/html/.env
    fi
fi

# Generate application key if it doesn't exist
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Cache configuration for better performance
echo "Caching configuration..."
php artisan config:cache

# Create storage link if it doesn't exist
if [ ! -L "public/storage" ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# Run database migrations if needed (optional - uncomment if desired)
# echo "Running database migrations..."
# php artisan migrate --force

echo "Laravel setup complete. Starting Octane server..."

# Start Laravel Octane with Swoole
exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=80