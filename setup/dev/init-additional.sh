docker exec -u root csatar-octobercms3 pecl install xdebug-3.2.1
docker exec -u root csatar-octobercms3 docker-php-ext-enable xdebug

docker exec -u root csatar-octobercms3 sh -c "echo \"zend_extension=xdebug\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csatar-octobercms3 sh -c "echo \"[xdebug]\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csatar-octobercms3 sh -c "echo \"xdebug.mode=develop,debug\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csatar-octobercms3 sh -c "echo \"xdebug.client_host=host.docker.internal\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csatar-octobercms3 sh -c "echo \"xdebug.client_port=9000\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
docker exec -u root csatar-octobercms3 sh -c "echo \"xdebug.discover_client_host=1\" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"

echo 'Setting up debugbar'
# uncomment the following line if you want to use the debugbar plugin, or you can install it manually later with the same command
# docker exec -u root csatar-octobercms3 composer require rainlab/debugbar-plugin

docker exec -u root csatar-octobercms3 apt-get update && apt-get install -y --no-install-recommends mc