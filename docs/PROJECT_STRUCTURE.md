# Organisation du dépôt GPI

Ce document explique la structure propre du dépôt et le rôle de chaque dossier.

## Vue générale

```txt
GPIMA/gpi
├── backend/
│   ├── app/
│   │   ├── Enums/              # Rôles, états, types et statuts métier
│   │   ├── Http/
│   │   │   ├── Controllers/    # Endpoints API
│   │   │   ├── Requests/       # Validation des formulaires API
│   │   │   └── Resources/      # Format JSON renvoyé au frontend
│   │   ├── Models/             # Modèles Eloquent
│   │   └── Services/           # Logique métier : supervision, IA, chatbot
│   ├── config/                 # Configuration Laravel + paramètres GPI
│   ├── database/
│   │   ├── migrations/         # Structure de la base de données
│   │   ├── factories/          # Génération de données de test
│   │   └── seeders/            # Données initiales
│   ├── routes/api.php          # Routes API consommées par React
│   └── Dockerfile              # Déploiement backend
│
├── frontend/
│   ├── public/
│   │   └── vitrine/            # Site vitrine public en HTML/CSS/JS
│   ├── src/
│   │   ├── app/                # Layout application, routes, navigation
│   │   ├── components/         # Composants réutilisables
│   │   ├── features/           # Pages/modules métier
│   │   ├── lib/                # API client, i18n, utilitaires
│   │   └── styles/             # Design system global
│   └── package.json
│
├── docs/                       # Documentation propre du projet
├── render.yaml                 # Blueprint Render pour backend + PostgreSQL
└── README.md                   # Présentation principale
```

## Règles d’organisation

- Tout le backend reste dans `backend/`.
- Tout le frontend React reste dans `frontend/src/`.
- La vitrine publique reste dans `frontend/public/vitrine/`.
- Les guides et explications restent dans `docs/`.
- Le fichier `render.yaml` reste à la racine, car Render le lit depuis la racine du dépôt.

## Pages publiques

```txt
/                       -> redirection vers /vitrine/
/vitrine/               -> vitrine publique
/login                  -> connexion application
/dashboard              -> dashboard après connexion
```

## API backend

```txt
/api/health
/api/login
/api/me
/api/dashboard
/api/equipements
/api/incidents
/api/alertes
/api/predictions
/api/assistant/message
```

## Logique professionnelle

Le projet est organisé en couches :

1. **Vitrine** : présente le projet.
2. **Frontend React** : interface utilisateur.
3. **API Laravel** : logique métier.
4. **Database** : stockage structuré.
5. **Services** : supervision, prédiction, scan et chatbot.
