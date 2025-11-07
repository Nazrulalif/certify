#!/bin/sh
set -e

echo "=€ Starting Laravel Certificate Generator..."

# Create database directory if using SQLite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    echo "=æ Setting up SQLite database..."
    mkdir -p /var/www/database

    # Create SQLite database file if it doesn't exist
    if [ ! -f /var/www/database/database.sqlite ]; then
        touch /var/www/database/database.sqlite
        echo " SQLite database file created"
    fi

    # Set proper permissions
    chown -R www-data:www-data /var/www/database
    chmod -R 775 /var/www/database
fi

# Initialize storage directory if empty
if [ ! -d /var/www/storage/framework ]; then
    echo "=Á Initializing storage directory..."
    cp -R /var/www/storage-init/* /var/www/storage/
    chown -R www-data:www-data /var/www/storage
    chmod -R 775 /var/www/storage
fi

# Create required storage subdirectories
mkdir -p /var/www/storage/app/public/certificates
mkdir -p /var/www/storage/app/public/templates
mkdir -p /var/www/storage/app/public/qr-codes
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs

# Set proper permissions
chown -R www-data:www-data /var/www/storage
chmod -R 775 /var/www/storage

# Create symbolic link for storage if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "= Creating storage symbolic link..."
    php artisan storage:link
fi

# Wait for database to be ready (if using PostgreSQL)
if [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "ó Waiting for PostgreSQL to be ready..."
    until php artisan db:show > /dev/null 2>&1; do
        echo "PostgreSQL is unavailable - sleeping"
        sleep 2
    done
    echo " PostgreSQL is ready!"
fi

# Run migrations if AUTO_MIGRATE is enabled
if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
    echo "= Running database migrations..."
    php artisan migrate --force --no-interaction
    echo " Migrations completed"
fi

# Seed database if AUTO_SEED is enabled
if [ "${AUTO_SEED:-false}" = "true" ]; then
    echo "<1 Seeding database..."
    php artisan db:seed --force --no-interaction
    echo " Seeding completed"
fi

# Cache optimization
if [ "$APP_ENV" = "production" ]; then
    echo "¡ Optimizing application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo " Optimization completed"
fi

echo "( Laravel Certificate Generator is ready!"

# Execute the main command (php-fpm)
exec "$@"
