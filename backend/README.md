# Backend GPI

API Laravel du projet **Gestion de Parc Informatique**.

## Rôle

Le backend gère :

- l’authentification par Laravel Sanctum ;
- les utilisateurs et rôles ;
- les équipements informatiques ;
- les affectations ;
- les incidents ;
- les alertes ;
- les métriques de supervision ;
- les prédictions ;
- les notifications ;
- le chatbot.

## Démarrage rapide

```bash
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

## Structure importante

```txt
app/Http/Controllers/    # Contrôleurs API
app/Http/Requests/       # Validation
app/Http/Resources/      # Réponses JSON
app/Models/              # Modèles Eloquent
app/Services/            # Logique métier
app/Enums/               # Enumérations métier
database/migrations/     # Tables
database/seeders/        # Données initiales
routes/api.php           # API REST
```

Documentation complète : [`../docs/BACKEND.md`](../docs/BACKEND.md)
