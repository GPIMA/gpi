#!/bin/sh
set -e

# Laravel bootstrap
php artisan config:clear

# Apache listens on Railway's injected $PORT (default 8080)
PORT="${PORT:-8080}"
echo "Starting Apache on port $PORT"
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
