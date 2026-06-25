# GPI backend — Laravel 13 / PHP 8.4 for Railway.
# 8.4 required: the composer.lock (generated on PHP 8.5) pulls Symfony 8.1,
# which needs php >= 8.4.1. Predictable build with the Postgres + mbstring exts.
FROM php:8.4-cli

# System libs + PHP extensions (pdo_pgsql for Supabase, mbstring/xml for Laravel).
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
COPY . .

# Dev deps are kept: the database seeder relies on Faker (fake()) for the demo
# fleet/users, which lives in require-dev.
RUN composer install --optimize-autoloader --no-interaction --no-progress \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
