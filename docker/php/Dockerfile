FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g npm@latest

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the Xdebug source file into the container
# due to docker-compose.yml php is using context: ../, need to use full path here
COPY ./docker/php/xdebug-3.3.0.tgz /tmp/

# Manually install Xdebug
RUN tar -xvzf /tmp/xdebug-3.3.0.tgz -C /tmp/ && \
    cd /tmp/xdebug-3.3.0 && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    docker-php-ext-enable xdebug && \
    rm -rf /tmp/xdebug-3.3.0 /tmp/xdebug-3.3.0.tgz

RUN echo "xdebug.mode=coverage,debug,develop" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN echo "upload_max_filesize=200M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size=200M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_input_time=300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit=2G" >> /usr/local/etc/php/conf.d/uploads.ini

COPY package*.json ./
RUN npm install

COPY ./vendor /var/www/html/vendor

WORKDIR /var/www/html
