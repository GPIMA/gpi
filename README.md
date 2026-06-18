# GPI — Gestion de Parc Informatique

GPI est une application web complète pour gérer, superviser et organiser un parc informatique. Le projet contient une vitrine publique, une interface de connexion, un dashboard applicatif, une API Laravel et une base de données.

## Structure du dépôt

```txt
GPIMA/gpi
├── backend/                  # API Laravel, auth, database, seeders, services métier
├── frontend/                 # Application React/Vite + vitrine publique
├── render.yaml               # Déploiement backend + database sur Render
└── README.md                 # Vue générale du projet
```

## Applications

| Partie | Chemin | Rôle |
| --- | --- | --- |
| Vitrine | `frontend/public/vitrine/` | Présentation publique du projet GPI |
| Assistant CV | `frontend/public/assistant-cv/` | Analyse CV, score ATS, CV optimisé et lettre de motivation |
| Frontend | `frontend/` | Interface React : login, dashboard, modules |
| Backend | `backend/` | API Laravel sécurisée avec Sanctum |
| Database | `backend/database/` | Migrations, factories et seeders |

## Liens principaux

```txt
Frontend production : https://gpi-umber.vercel.app/
Vitrine             : https://gpi-umber.vercel.app/vitrine/
Assistant CV        : https://gpi-umber.vercel.app/assistant-cv/
Login               : https://gpi-umber.vercel.app/login
Dashboard           : https://gpi-umber.vercel.app/dashboard
```

## Démarrage local

### 1. Backend

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

### 2. Frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

Frontend local :

```txt
http://localhost:5173
```

## Identifiants admin de test

```txt
Email       : admin@gpi.local
Mot de passe: Gpi@2026
```

## Fonctionnalités principales

- Authentification par token Sanctum.
- Gestion des utilisateurs par rôles : administrateur, technicien, employé.
- Gestion complète des équipements informatiques.
- Affectations des équipements aux employés.
- Déclaration et suivi des incidents.
- Alertes automatiques selon les métriques.
- Supervision CPU, RAM et disque.
- Notifications internes.
- Prédictions IA simulées.
- Assistant chatbot.
- Vitrine publique professionnelle.

## Déploiement

Le frontend est prévu pour Vercel. Le backend Laravel doit être déployé séparément sur Render, Railway, VPS ou autre hébergeur compatible Laravel.

Après déploiement backend, ajouter dans Vercel :

```env
VITE_API_URL=https://votre-backend.com
```

Puis redéployer le frontend.
