#!/bin/bash
set -e

echo 'Waiting for database...'
sleep 30

echo '================================================================'
echo 'Migrating database...'
echo '================================================================'
php artisan october:migrate
php artisan key:generate

echo '================================================================'
echo ' Synchronizing project...'
echo '================================================================'
php artisan project:sync

echo '================================================================'
echo 'Setting permissions...'
echo '================================================================'
chown -R www-data:www-data /var/www/html/storage
chmod -R 755 /var/www/html/storage

exec "$@"
