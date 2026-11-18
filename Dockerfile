# ==============================================
# Stage 1 : base commune avec extensions PHP
# ==============================================
FROM dunglas/frankenphp:php8.4 AS base

RUN install-php-extensions \
    pdo_pgsql \
    zip \
    opcache \
    pcntl

# ==============================================
# Stage 2 : builder
# ==============================================
FROM base AS builder

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

ENV COMPOSER_ALLOW_SUPERUSER=1

# 🆕 --no-scripts : n'exécute PAS post-install / package:discover ici
# 🆕 --no-autoloader : génère l'autoload APRÈS le COPY . . (voir plus bas)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# 🆕 Le code source arrive APRÈS composer install (cache Docker préservé)
COPY . .

# 🆕 Maintenant que artisan et le code existent, on régénère l'autoloader
#    Cela déclenche package:discover correctement
RUN composer dump-autoload --no-dev --optimize --no-interaction

# ==============================================
# Stage 3 : image finale
# ==============================================
FROM base

WORKDIR /app

COPY --from=builder --chown=www-data:www-data /app /app

RUN chown -R www-data:www-data \
        /app/storage \
        /app/bootstrap/cache

ENV SERVER_NAME=":${PORT:-8080}"

EXPOSE 8080