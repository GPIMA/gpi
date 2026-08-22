# Image de développement local pour le backend Laravel (docker-compose.yml).
# Contrairement à backend/Dockerfile (prod, Railway/Render), le code applicatif
# n'est PAS copié dans l'image : il est monté en volume par docker-compose,
# ce qui permet d'éditer les fichiers sur la machine hôte et de les voir pris
# en compte immédiatement, sans reconstruire l'image.
FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libonig-dev libxml2-dev libsqlite3-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_sqlite mbstring xml zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
