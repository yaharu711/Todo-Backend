#!/usr/bin/env bash
mkdir -p /var/www/storage/app/secrets
# 所有権をLaravelの実行ユーザ（例えば www-data）に変更する
chown -R www-data:www-data /var/www/storage/app
# 「/etc/secrets配下はアプリケーション側から読み込みできない」かつ「chmodなどで権限の変更もできない」ため、別の場所にコピーする
cp /etc/secrets/firebase_root_service_account_private_file.json /var/www/storage/app/secrets/firebase_root_service_account_private_file.json
# パーミッション設定 (所有者のみが読み書きできる)
chmod 600 /var/www/storage/app/secrets/firebase_root_service_account_private_file.json

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