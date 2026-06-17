# Déploiement et liaison complète GPI

Objectif : connecter proprement la vitrine, le frontend React, le backend Laravel et la base de données.

## 1. Architecture de déploiement

```txt
Utilisateur
   ↓
Vercel — Frontend React + vitrine
   ↓ VITE_API_URL
Backend Laravel — Render / Railway / VPS
   ↓
Base de données PostgreSQL
```

## 2. Frontend

Le frontend est prévu pour Vercel.

Variable Vercel nécessaire :

```env
VITE_API_URL=https://votre-backend.com
```

Liens frontend :

```txt
https://gpi-umber.vercel.app/
https://gpi-umber.vercel.app/vitrine/
https://gpi-umber.vercel.app/login
https://gpi-umber.vercel.app/dashboard
```

## 3. Backend

Le backend Laravel doit être déployé séparément.

Le dépôt contient `render.yaml`, qui prépare :

- un service web `gpi-backend`
- une base PostgreSQL `gpi-database`
- le CORS vers `https://gpi-umber.vercel.app`
- un health check `/api/health`

Variables backend importantes :

```env
APP_ENV=production
APP_DEBUG=false
FRONTEND_URL=https://gpi-umber.vercel.app
DB_CONNECTION=pgsql
ADMIN_EMAIL=admin@gpi.local
ADMIN_PASSWORD=à définir dans l'hébergeur
```

## 4. Test backend

Après déploiement backend :

```txt
https://votre-backend.com/api/health
```

Réponse attendue :

```json
{
  "status": "ok",
  "service": "GPI API",
  "version": "1.0.0"
}
```

## 5. Test connexion

1. Ouvrir :

```txt
https://gpi-umber.vercel.app/login
```

2. Entrer les identifiants administrateur définis dans les variables backend.

3. Après connexion :

```txt
https://gpi-umber.vercel.app/dashboard
```

## 6. Test local complet

Terminal 1 : backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan serve
```

Terminal 2 : frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

Ouvrir :

```txt
http://localhost:5173/login
```

## 7. Points importants

- Vercel ne lance pas Laravel directement.
- Laravel doit être déployé sur un hébergeur backend.
- `VITE_API_URL` doit toujours pointer vers le backend.
- `FRONTEND_URL` doit autoriser le domaine frontend pour éviter les erreurs CORS.
