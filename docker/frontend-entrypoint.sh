#!/bin/sh
# Démarrage du frontend en développement local (docker-compose.yml).
set -e

cd /app

if [ ! -f .env ]; then
    echo "== Création de .env à partir de .env.example =="
    cp .env.example .env
fi

echo "== npm install =="
npm install

echo "== Serveur Vite sur http://localhost:5173 =="
exec npm run dev -- --host 0.0.0.0 --port 5173
