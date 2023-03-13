docker-compose exec -u root web pecl install xdebug-3.1.5
docker-compose exec -u root web docker-php-ext-enable xdebug

docker-compose exec -u root web sh -c "echo \"zend_extension=xdebug\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker-compose exec -u root web sh -c "echo \"[xdebug]\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker-compose exec -u root web sh -c "echo \"xdebug.mode=develop,debug\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker-compose exec -u root web sh -c "echo \"xdebug.client_host=host.docker.internal\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker-compose exec -u root web sh -c "echo \"xdebug.client_port=9000\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker-compose exec -u root web sh -c "echo \"xdebug.discover_client_host=1\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"