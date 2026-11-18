# ==============================================
# Stage 1 : base commune avec extensions PHP
# ==============================================
FROM dunglas/frankenphp:php8.4 AS base

# Extensions PHP nécessaires pour Laravel + PostgreSQL
RUN install-php-extensions \
    pdo_pgsql \
    zip \
    opcache \
    pcntl

# ==============================================
# Stage 2 : builder (composer install)
# ==============================================
FROM base AS builder

WORKDIR /app

# 🆕 Composer n'est pas inclus dans dunglas/frankenphp — on le copie
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

# 🆕 Autoriser Composer à tourner en root (conteneur Docker)
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

# Code source après les dépendances (profite du cache Docker)
COPY . .

# Régénère l'autoloader optimisé après ajout des classes du projet
RUN composer dump-autoload --no-dev --optimize --no-interaction

# ==============================================
# Stage 3 : image finale de production
# ==============================================
FROM base

WORKDIR /app

# Récupération de l'app buildée (code + vendor + autoload optimisé)
COPY --from=builder --chown=www-data:www-data /app /app

# Permissions Laravel
RUN chown -R www-data:www-data \
        /app/storage \
        /app/bootstrap/cache

# FrankenPHP lit SERVER_NAME pour savoir sur quel port écouter.
# Railway injecte $PORT dans le conteneur.
ENV SERVER_NAME=":${PORT:-8080}"

EXPOSE 8080

# CMD par défaut de l'image : "frankenphp run"
# Il utilise le Caddyfile par défaut qui sert /app/public