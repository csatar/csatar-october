docker-compose exec -u root web php artisan key:generate

echo ' Synchronizing project...'
docker-compose exec -u root web php artisan project:sync

echo 'Migrating database...'
docker-compose exec -u root web php artisan october:migrate

echo 'Setting permissions...'
docker-compose exec -u root web chown -R www-data:www-data /var/www/html/storage
docker-compose exec -u root web chmod -R 755 /var/www/html/storage

echo 'Setting up debugbar'
docker-compose exec -u root web composer require rainlab/debugbar-plugin

echo "DONE"
