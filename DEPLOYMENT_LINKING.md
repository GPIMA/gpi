# Liaison complète GPI — Frontend, Backend et Database

Objectif : rendre tout le projet fonctionnel ensemble.

## 1. Ce qui est déjà relié dans le code

Le frontend React utilise `VITE_API_URL` pour appeler le backend Laravel.

Exemple :

```env
VITE_API_URL=https://gpi-backend.onrender.com
```

Le backend Laravel expose l'API sur `/api` :

```txt
https://gpi-backend.onrender.com/api
```

La page login appelle :

```txt
POST /api/login
```

Puis le token est sauvegardé dans le navigateur et envoyé automatiquement dans les appels API protégés.

## 2. Backend + database

Le fichier `render.yaml` prépare un déploiement backend avec :

- service web Laravel `gpi-backend`
- database PostgreSQL `gpi-database`
- CORS autorisant `https://gpi-umber.vercel.app`
- health check `/api/health`
- variables d'environnement essentielles

## 3. Étapes pour terminer en ligne

### Étape A — Déployer le backend

Déployer le dossier `backend` sur Render/Railway/VPS.

Si Render est utilisé, le fichier `render.yaml` peut créer automatiquement :

- le service backend
- la base PostgreSQL

Variables importantes :

```env
APP_ENV=production
APP_DEBUG=false
FRONTEND_URL=https://gpi-umber.vercel.app
DB_CONNECTION=pgsql
ADMIN_EMAIL=admin@gpi.local
ADMIN_PASSWORD=Gpi@2026
```

### Étape B — Récupérer l'URL backend

Exemple :

```txt
https://gpi-backend.onrender.com
```

Tester :

```txt
https://gpi-backend.onrender.com/api/health
```

La réponse doit être :

```json
{
  "status": "ok",
  "service": "GPI API",
  "version": "1.0.0"
}
```

### Étape C — Lier Vercel au backend

Dans Vercel > Project Settings > Environment Variables, ajouter :

```env
VITE_API_URL=https://gpi-backend.onrender.com
```

Ensuite redéployer le frontend.

## 4. Liens finaux

Vitrine :

```txt
https://gpi-umber.vercel.app/
```

Login :

```txt
https://gpi-umber.vercel.app/login
```

Dashboard après connexion :

```txt
https://gpi-umber.vercel.app/dashboard
```

Backend API :

```txt
https://gpi-backend.onrender.com/api
```

## 5. Identifiants admin

```txt
Email : admin@gpi.local
Mot de passe : Gpi@2026
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
