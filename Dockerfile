# Dockerfile
# --- Étape 1 : Build (installation des dépendances et compilation des assets) ---
FROM dunglas/frankenphp:php8.4 AS builder

WORKDIR /app

# Installation des extensions PHP nécessaires (pdo_pgsql pour PostgreSQL, etc.)
RUN install-php-extensions \
    pdo_pgsql \
    zip \
    opcache \
    pcntl

# Copie des fichiers de dépendances pour profiter du cache Docker
COPY composer.json composer.lock ./

# Installation des dépendances sans les paquets de dev
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

# Copie du reste du code source
COPY . .

# Construction des assets frontend (si vous utilisez Vite/Tailwind)
# RUN npm ci && npm run build

# --- Étape 2 : Image de production finale ---
FROM dunglas/frankenphp:php8.4

WORKDIR /app

# Récupération de l'application déjà buildée depuis l'étape précédente
COPY --from=builder /app /app

# Définition du serveur FrankenPHP pour écouter sur le port fourni par Railway
# Railway injecte la variable $PORT, on l'utilise pour la configuration de Caddy
ENV SERVER_NAME=":${PORT:-8080}"

# Exposition du port (documentation, Railway utilise $PORT)
EXPOSE 8080

# Commande de démarrage : FrankenPHP sert l'application Laravel
# Le Caddyfile est optionnel si on utilise les valeurs par défaut de FrankenPHP pour Laravel
CMD ["frankenphp", "php-server", "-r", "public/"]