FROM php:8.4-fpm-bookworm AS app

# Installation des dépendances système + extensions PHP
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-install -j"$(nproc)" opcache pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Autoriser Composer à tourner en root (conteneur Docker)
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY --chown=www-data:www-data . .
COPY docker/php/zz-env.conf /usr/local/etc/php-fpm.d/zz-env.conf

RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]


FROM nginx:1.29-alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public

EXPOSE 80