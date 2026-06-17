# Frontend GPI

Interface React/Vite du projet **Gestion de Parc Informatique**.

## Rôle

Le frontend contient :

- la vitrine publique ;
- la page de connexion ;
- le dashboard ;
- les modules équipements, incidents, alertes, supervision, prédictions, assistant et administration ;
- le client API qui communique avec le backend Laravel.

## Démarrage rapide

```bash
npm install
cp .env.example .env
npm run dev
```

Frontend local :

```txt
http://localhost:5173
```

## Liaison backend

Dans `.env` :

```env
VITE_API_URL=http://localhost:8000
```

En production :

```env
VITE_API_URL=https://votre-backend.com
```

## Structure importante

```txt
public/vitrine/       # Vitrine publique HTML/CSS/JS
src/app/              # Routes, layout, navigation
src/components/       # Composants réutilisables
src/features/         # Modules métier
src/lib/api/          # Client API Axios
src/styles/           # Design system global
```

## Routes principales

```txt
/              -> vitrine
/vitrine/      -> vitrine publique
/login         -> connexion
/dashboard     -> application après connexion
```

Documentation déploiement : [`../docs/DEPLOYMENT.md`](../docs/DEPLOYMENT.md)
