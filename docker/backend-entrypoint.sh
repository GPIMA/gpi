#!/bin/sh
# Démarrage du backend en développement local (docker-compose.yml).
# Idempotent : peut être relancé sans danger (composer install et migrate
# ne refont rien d'inutile s'il n'y a rien de nouveau).
set -e

cd /app

if [ ! -f .env ]; then
    echo "== Création de .env à partir de .env.example =="
    cp .env.example .env
fi

echo "== composer install =="
composer install --no-interaction --no-progress

if ! grep -q '^APP_KEY=base64' .env; then
    echo "== Génération de APP_KEY =="
    php artisan key:generate --force
fi

mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite

echo "== Migrations + seed =="
php artisan migrate --seed --force

echo "== Serveur Laravel sur http://localhost:8000 =="
exec php artisan serve --host=0.0.0.0 --port=8000
