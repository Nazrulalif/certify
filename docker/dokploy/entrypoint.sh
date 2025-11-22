#!/bin/sh
set -e

echo "🚀 Starting Certify application..."

# Create required directories if they don't exist
echo "📁 Ensuring storage directories exist..."
mkdir -p /var/www/storage/app/public/certificates
mkdir -p /var/www/storage/app/public/templates
mkdir -p /var/www/storage/app/public/qr-codes
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
mkdir -p /var/www/database
mkdir -p /var/log/supervisor

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/database
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/database

# Create SQLite database if it doesn't exist
if [ ! -f /var/www/database/database.sqlite ]; then
    echo "📦 Creating SQLite database..."
    touch /var/www/database/database.sqlite
    chown www-data:www-data /var/www/database/database.sqlite
    chmod 664 /var/www/database/database.sqlite
fi

# Ensure storage symlink exists
if [ ! -L /var/www/public/storage ]; then
    echo "🔗 Creating storage symlink..."
    ln -sf /var/www/storage/app/public /var/www/public/storage
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force --no-interaction

# Cache configuration for production
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear any stale caches
php artisan cache:clear 2>/dev/null || true

echo "✅ Application ready!"

# Execute the main command (supervisord)
exec "$@"
