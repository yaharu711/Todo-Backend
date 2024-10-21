#!/usr/bin/env bash
set -ex
echo "Running composer"
composer install --no-dev --working-dir=/var/www

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Caching view"
php artisan view:clear

echo "Running migrations..."
php artisan migrate --force

apache2-foreground