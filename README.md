# HK — Système Intelligent de Gestion de Parc IT

Intelligent IT fleet management system. Built from a supplied UML class diagram
and use-case diagram. **French** is the primary language, **English** secondary.

The project is split into two independently deployable applications so each can
be hosted on a different platform:

| App | Stack | Role |
| --- | --- | --- |
| [`backend/`](backend) | Laravel 13 (PHP 8.5) · PostgreSQL · Sanctum | MVC REST API |
| [`frontend/`](frontend) | React + Vite + TypeScript | "NOC console" SPA |

The frontend talks to the backend **only** over `VITE_API_URL`; the API trusts
**only** the origins listed in `FRONTEND_URL` (CORS). They share no code.

## Architecture (MVC)

- **Model** — Eloquent models + PostgreSQL migrations, one per domain entity from
  the class diagram. The abstract `Utilisateur` hierarchy (Administrateur /
  Technicien / Employé) maps to a single `users` table with a `role`
  discriminator (`App\Enums\RoleUtilisateur`).
- **View** — JSON API Resources (`app/Http/Resources`); the React app is the
  presentation layer.
- **Controller** — thin resource controllers (`app/Http/Controllers`) delegating
  business logic to services (`app/Services`).

### No hardcoding
- The 8 data-dictionary enums live in `app/Enums` and are exposed, localized,
  through `GET /api/enums`. The frontend's option lists, filters and labels all
  come from there — none are duplicated in React.
- Tunables (thresholds, scan/simulation params, seeded admin) live in
  `config/parc.php` and the environment, never inline in classes.
- Secrets (DB, admin password, chatbot API key) are environment-only.

## Prerequisites
- PHP 8.5 + Composer, Node 20+, PostgreSQL 17 (all installed via Homebrew).

## Run locally

```bash
# 1. Database (once)
brew services start postgresql@17

# 2. Backend  →  http://localhost:8000
cd backend
cp .env.example .env           # then set DB_PASSWORD + ADMIN_PASSWORD
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --port=8000

# 3. Frontend →  http://localhost:5173
cd ../frontend
npm install
npm run dev
```

Sign in with the seeded administrator (the `ADMIN_EMAIL` / `ADMIN_PASSWORD`
from `backend/.env`).

## Features

All modules are implemented, French primary / English secondary throughout:

- **Authentication & roles** — Sanctum bearer tokens; Administrateur / Technicien
  / Employé with route-level RBAC.
- **Équipements** — fleet CRUD, filters, pagination, employee assignments, and a
  simulated SNMP network scan that auto-discovers devices.
- **Supervision** — per-device CPU/RAM/disk metrics with history charts; a
  schedulable `parc:superviser` tick simulates readings and runs the alert engine.
- **Alertes & règles** — configurable threshold rules evaluated against metrics;
  alerts can be taken over and resolved.
- **Incidents** — employees report faults; technicians take over and resolve them;
  the reporter is notified (in-app notification bell).
- **Prédictions (IA)** — a statistical model projects metric trends into failure
  probabilities and raises preventive alerts. Swappable for a trained model.
- **Assistant (chatbot)** — IT-help chat with a pluggable driver: an offline
  rule-based knowledge base by default, or any OpenAI-compatible API via env.
- **Administration** — user management for administrators.

### Assistant driver

Set in `backend/.env` (no keys in code):

```dotenv
CHATBOT_DRIVER=rule          # offline knowledge base (default)
# or, for a free OpenAI-compatible API (Groq, OpenRouter, …):
CHATBOT_DRIVER=openai
CHATBOT_BASE_URL=https://api.groq.com/openai/v1
CHATBOT_API_KEY=sk-...
CHATBOT_MODEL=llama-3.1-8b-instant
```

Missing key ⇒ automatic fallback to the rule-based driver.
