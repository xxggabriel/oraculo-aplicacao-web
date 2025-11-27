FROM php:8.3-fpm-alpine AS php-base

RUN apk add --no-cache \
    bash \
    curl \
    git \
    zip \
    unzip \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    zlib-dev \
    postgresql-dev \
    mysql-client \
    $PHPIZE_DEPS \
    && docker-php-ext-install intl zip pdo pdo_mysql pdo_pgsql \
    && docker-php-ext-enable opcache \
    && apk del --no-cache $PHPIZE_DEPS

COPY --from=composer:2.9 /usr/bin/composer /usr/local/bin/composer

FROM php-base AS vendor

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

FROM node:20-alpine AS frontend

WORKDIR /var/www/html

COPY package*.json vite.config.js ./
RUN npm install

COPY resources resources
RUN npm run build

FROM php-base AS app

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=frontend /var/www/html/public/build ./public/build

RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm", "-F"]
