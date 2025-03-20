#!/usr/bin/env bash
set -ex

# secretsファイルを作成
echo "Making secrets file"
mkdir -p /var/www/storage/app/secrets

# secretsをコピー
cp /etc/secrets/firebase_root_service_account_private_file.json \
   /var/www/storage/app/secrets/firebase_root_service_account_private_file.json

# appディレクトリ以下すべての所有権を www-data に設定
chown -R www-data:www-data /var/www/storage/app

# ファイルのパーミッションを600に設定（所有者のみ読み書き）
chmod 600 /var/www/storage/app/secrets/firebase_root_service_account_private_file.json

# appとsecretsのディレクトリに実行権限を付与 (700: 所有者が読み書き実行)
chmod 700 /var/www/storage/app
chmod 700 /var/www/storage/app/secrets

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