# Backend GPI — Laravel API + Base de données

Ce backend rend l'application GPI fonctionnelle : authentification, base de données, gestion des équipements, incidents, alertes, supervision, prédictions et chatbot.

## Démarrage local rapide

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan serve
```

API locale : `http://localhost:8000/api`

Health check : `http://localhost:8000/api/health`

## Compte administrateur de test

Email : `admin@gpi.local`

Mot de passe : `Gpi@2026`

Ces valeurs peuvent être changées dans `.env` :

```env
ADMIN_EMAIL=admin@gpi.local
ADMIN_PASSWORD=Gpi@2026
```

## Frontend

Dans `frontend/.env`, configurer :

```env
VITE_API_URL=http://localhost:8000
```

Puis :

```bash
cd frontend
npm install
npm run dev
```

## Base de données créée

La migration `2026_06_17_120000_create_gpi_domain_tables.php` crée les tables principales :

- `users`
- `personal_access_tokens`
- `equipements`
- `affectations`
- `metriques`
- `alertes`
- `regle_alertes`
- `incidents`
- `notifications`
- `modele_ias`
- `predictions`
- `scan_reseaux`
- `conversations`
- `messages`

## Endpoints principaux

Public :

- `GET /api/health`
- `POST /api/login`
- `GET /api/enums`

Protégé par token :

- `GET /api/me`
- `POST /api/logout`
- `GET /api/dashboard`
- `GET /api/equipements`
- `POST /api/incidents`
- `GET /api/alertes`
- `GET /api/predictions`
- `GET /api/assistant/conversations`
- `POST /api/assistant/message`

Admin :

- `POST /api/equipements`
- `PUT /api/equipements/{equipement}`
- `DELETE /api/equipements/{equipement}`
- `POST /api/scan-reseau`
- `GET /api/utilisateurs`

## Déploiement production

Pour connecter le frontend Vercel au backend, il faut déployer le backend Laravel sur un hébergeur qui supporte PHP/Laravel, par exemple Render, Railway, VPS, ou autre.

Ensuite, dans Vercel, ajouter la variable :

```env
VITE_API_URL=https://votre-backend.com
```

Puis redéployer le frontend.
