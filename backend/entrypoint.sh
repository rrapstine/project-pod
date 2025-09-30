#!/bin/bash
set -e

echo "Starting Laravel application setup..."

# Check if vendor directory exists and has dependencies (from Docker build or volume)
# We need to check if vendor directory actually has content, not just if it exists
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then
    echo "Installing dependencies..."
    composer install --optimize-autoloader --no-scripts --no-interaction
    echo "Dependencies installed, continuing setup..."
else
    echo "Dependencies already available, skipping installation..."
fi

# Debug: Show current working directory and PHP
echo "Current directory: $(pwd)"
echo "PHP version: $(php --version | head -1)"

# Dependencies are now installed
echo "Dependencies installed, continuing setup..."


# Ensure directories exist (container will have proper permissions)
echo "Setting up directories..."
mkdir -p /var/www/html/storage/framework/{cache,sessions,views}
mkdir -p /var/www/html/bootstrap/cache

# Ensure .env esists
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
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

# Start Laravel using built-in PHP server (simpler for development)
exec php artisan serve --host=0.0.0.0 --port=80
