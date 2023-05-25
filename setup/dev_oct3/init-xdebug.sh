docker exec -u root csat-oct3 pecl install xdebug-3.2.1
docker exec -u root csat-oct3 docker-php-ext-enable xdebug

docker exec -u root csat-oct3 sh -c "echo \"zend_extension=xdebug\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csat-oct3 sh -c "echo \"[xdebug]\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csat-oct3 sh -c "echo \"xdebug.mode=develop,debug\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csat-oct3 sh -c "echo \"xdebug.client_host=host.docker.internal\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csat-oct3 sh -c "echo \"xdebug.client_port=9000\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csat-oct3 sh -c "echo \"xdebug.discover_client_host=1\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"