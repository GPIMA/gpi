# Backend GPI — API Laravel et base de données

Le backend rend l'application GPI fonctionnelle : authentification, rôles, équipements, incidents, alertes, supervision, prédictions et chatbot.

## Stack

```txt
Laravel 13
PHP 8.3+
Laravel Sanctum
SQLite en local
PostgreSQL en production
```

## Démarrage local

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan serve
```

API locale :

```txt
http://localhost:8000/api
```

Health check :

```txt
http://localhost:8000/api/health
```

## Identifiants admin de test

```txt
Email       : admin@gpi.local
Mot de passe: Gpi@2026
```

Ces valeurs peuvent être modifiées dans `backend/.env` :

```env
ADMIN_EMAIL=admin@gpi.local
ADMIN_PASSWORD=Gpi@2026
```

## Tables principales

La base de données contient :

- `users`
- `personal_access_tokens`
- `equipements`
- `affectations`
- `metriques`
- `regle_alertes`
- `alertes`
- `incidents`
- `notifications`
- `modele_ias`
- `predictions`
- `scan_reseaux`
- `conversations`
- `messages`

## Endpoints publics

```txt
GET  /api/health
POST /api/login
GET  /api/enums
```

## Endpoints protégés

```txt
GET  /api/me
POST /api/logout
GET  /api/dashboard
GET  /api/equipements
GET  /api/equipements/{id}
GET  /api/supervision/apercu
GET  /api/alertes
GET  /api/incidents
POST /api/incidents
GET  /api/predictions
GET  /api/assistant/conversations
POST /api/assistant/message
```

## Endpoints administrateur

```txt
POST   /api/equipements
PUT    /api/equipements/{id}
DELETE /api/equipements/{id}
POST   /api/scan-reseau
GET    /api/utilisateurs
POST   /api/utilisateurs
PUT    /api/utilisateurs/{id}
DELETE /api/utilisateurs/{id}
```

## Liaison frontend

Dans `frontend/.env` :

```env
VITE_API_URL=http://localhost:8000
```

En production :

```env
VITE_API_URL=https://votre-backend.com
```
