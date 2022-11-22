# Multi Stage Build: Part 1
# -------------------------
FROM php:7.4-apache as intermediate

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libpng-dev \
    libzip4 \
    libzip-dev \
    git \
    && docker-php-ext-install zip \
    && docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer create-project october/october . "^2.0" --no-interaction --prefer-dist

RUN echo 'APP_ENV=csatar-dev' > .env

# RUN sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/' .env && \
#     sed -i 's/DB_PORT=3306/DB_PORT=3306/' .env && \
#     sed -i 's/DB_DATABASE=database/DB_DATABASE=csatar/' .env && \
#     sed -i 's/DB_PASSWORD=/DB_PASSWORD=root/' .env


ARG LICENSE_KEY

# Artisan commands
RUN php artisan key:generate && \
    php artisan project:set ${LICENSE_KEY} && \
    php artisan october:build

# Delete license key
# RUN rm auth.json && \
#     rm storage/cms/project.json

# Multi Stage Build: Part 2
# -------------------------
FROM php:7.4-apache
LABEL maintainer="October CMS <hello@octobercms.com> (@octobercms)"

# Enables apache rewrite w/ security
RUN a2enmod rewrite expires && \
    sed -i 's/ServerTokens OS/ServerTokens ProductOnly/g' \
    /etc/apache2/conf-available/security.conf

# Installs dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libyaml-dev \
    libwebp-dev \
    libzip4 \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libpq-dev \
    g++ \
    git \
    cron \
    vim \
    nano \
    mc \
    ssh-client \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install exif \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Sets recommended PHP.ini settings (https://secure.php.net/manual/en/opcache.installation.php)
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'upload_max_filesize=128M'; \
    echo 'post_max_size=128M'; \
    echo 'expose_php=off'; \
    } > /usr/local/etc/php/conf.d/php-recommended.ini

RUN pecl install apcu \
    && pecl install yaml-2.0.4 \
    && docker-php-ext-enable apcu yaml

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy install from previous image
COPY --chown=www-data:www-data --from=intermediate /var/www/html /var/www/html

# Sets user to www-data
USER www-data

# Returns to root user
USER root

# Provides container inside image for data persistence
VOLUME ["/var/www/html"]

RUN pecl install -o -f xdebug-3.1.0 \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable xdebug

ENV XDEBUG_CONFIG="idekey=anything-works-here"
ENV PHP_IDE_CONFIG="serverName=Docker"

RUN echo 'xdebug.remote_port=9000' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.remote_enable=1' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.remote_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
