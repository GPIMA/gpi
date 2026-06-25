# GPI backend — Laravel 13 / PHP 8.4 + Apache for Railway.
FROM php:8.4-apache

# System libs + PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Point Apache DocumentRoot at Laravel's public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-interaction --no-progress \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Railway injects $PORT — Apache listens on it via start.sh
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
