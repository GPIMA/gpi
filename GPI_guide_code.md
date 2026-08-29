# GPI — Guide du code (préparation soutenance)

Architecture : **backend Laravel (API REST)** + **frontend React/Vite/TypeScript (SPA)**, connectés en HTTP/JSON, auth par token Sanctum. Le backend expose `/api/*`, le frontend consomme via React Query. i18n FR/EN des deux côtés (labels d'enums traduits par le backend, textes d'interface traduits par le frontend).

---

## 1. Backend (`backend/app`)

### 1.1 Enums (`app/Enums`)
Chaque enum PHP « backé » (`: string`) représente une liste fermée de valeurs métier (états, rôles, statuts...). Tous utilisent le trait `HasOptions`, qui expose `label()` (traduction via `lang/fr|en/enums.php`) et `options()` (liste `{value, label}` envoyée au frontend pour peupler les `<select>`). Un seul contrôleur, `EnumController`, régénère et sert tous ces dictionnaires en un seul appel `/api/enums` — le frontend n'a donc qu'un seul point d'entrée pour connaître les valeurs possibles de chaque champ.

Enums présents : `RoleUtilisateur` (SUPER_ADMIN/ADMIN/TECHNICIEN/EMPLOYE), `EtatEquipement` (EN_LIGNE/HORS_LIGNE/EN_MAINTENANCE/EN_PANNE), `TypeEquipement` (PC, SOURIS, CLAVIER, ECRAN, SOCLE...), `StatutIncident`, `Severite`, `TypeAlerte`, `EtatAlerte`, `CanalNotification`, `MotifRetourPoste`, `StatutDemandeChangementEtat` (EN_ATTENTE/APPROUVEE/REJETEE — ajouté pour la nouvelle fonctionnalité).

### 1.2 Modèles Eloquent (`app/Models`)
Chaque modèle correspond à une table et porte : `$fillable` (champs assignables en masse), `casts()` (types PHP ↔ colonnes, notamment enum ↔ colonne string), les relations Eloquent (`belongsTo`, `hasMany`), et parfois de petites méthodes métier.

- **Equipement** — poste/périphérique du parc. Relations : `metriques`, `alertes`, `predictions`, `affectations`, `historiques`, `demandesChangementEtat`, `demandeChangementEtatEnAttente` (la demande EN_ATTENTE en cours, ajoutée pour la nouvelle fonctionnalité).
- **Incident** — signalement de panne. Relations : `equipement`, `equipementRemplacement` (poste de prêt), `employe`, `technicien`, `commentaires`, `piecesJointes`. Contient la logique du cycle de vie complet : `prendreEnCharge()`, `resoudre()`, `demanderRestitution()`, `reouvrir()`. Génère automatiquement sa référence `INC-000123` à la création (hook `booted()`).
- **User** — compte (les 4 rôles cohabitent dans une seule table, discriminés par `role`). Méthodes `estSuperAdmin()`, `estTechnicien()`, etc. et `estAdminOuPlus()` utilisées partout pour les contrôles d'accès. Champ `localisation` = site (Rabat/Casablanca/Tanger), utilisé pour le scoping des Admin/Techniciens.
- **Affectation** — historique de qui a eu quel équipement et quand (`date_affectation`, `date_retour`, `statut` EN_COURS/TERMINEE).
- **Alerte**, **RegleAlerte**, **Metrique** — supervision : une `RegleAlerte` définit un seuil (`evaluer(Metrique)` retourne vrai/faux), une `Metrique` est un relevé CPU/RAM/disque, une `Alerte` est créée quand une règle est franchie.
- **Prediction**, **ModeleIA** — prédiction de pannes.
- **Notification** — message envoyé à un utilisateur (lu/non lu).
- **Historique** — la table d'audit centrale : qui (`user_id` = concerné, `auteur_id` = qui a agi) a fait quoi (`action`, `description`) sur quel équipement/incident, avec un avant/après JSON optionnel.
- **Conversation**, **Message** — fils de discussion avec l'assistant (chatbot).
- **DemandeInscription** — demande de compte déposée depuis la vitrine publique.
- **DemandeChangementEtat**, **DemandeChangementEtatCommentaire** *(nouveaux)* — la demande d'approbation quand un technicien change l'état d'un équipement, et les commentaires de discussion associés.
- **IncidentCommentaire**, **IncidentPieceJointe** — discussion et pièces jointes d'un incident.

### 1.3 Contrôleurs (`app/Http/Controllers`)
Chaque contrôleur expose les actions REST d'un domaine. Tous appliquent le même patron de contrôle d'accès par site : *Super Admin = tout voit/traite sans restriction ; Admin/Technicien avec `localisation` renseignée = uniquement leur site ; sans `localisation` = traité comme non restreint.*

| Contrôleur | Rôle |
|---|---|
| `AuthController` | `login` (Sanctum token), `me`, `logout`. |
| `EquipementController` | CRUD du parc. `index`/`show` filtrent par site pour Admin/Technicien scopés ; `update` intercepte un changement d'`etat` fait par un Technicien et le redirige vers une `DemandeChangementEtat` au lieu de l'appliquer directement (voir §1.6). |
| `IncidentController` | Cycle de vie complet d'un incident : signalement, prise en charge, résolution, demande de restitution du poste, traitement du retour (3 motifs : maintenance sur place / nouvelle date avec poste de prêt / remplacement définitif), réouverture (fenêtre de 5 jours), suppression (uniquement si encore "Ouvert"), commentaires, assignation par un admin. Toute action passe par `HistoriqueService::log()` et souvent `NotificationService::notifier()`. |
| `AlerteController` | Liste des alertes, prise en charge, résolution. |
| `RegleAlerteController` | CRUD des règles de seuils (Admin/Super Admin). |
| `PredictionController` | Liste des prédictions, déclenchement manuel (`generer`), infos du modèle. |
| `DashboardController` | Agrège les KPI (répartition du parc par état, alertes actives/récentes) pour la page d'accueil. |
| `UtilisateurController` | CRUD des comptes utilisateurs (Admin/Super Admin). |
| `NotificationController` | Liste des notifications de l'utilisateur connecté, marquage lu. |
| `ConversationController` | Fils de discussion avec l'assistant : liste, détail, envoi d'un message (délègue la réponse à `ChatbotService`). |
| `HistoriqueController` | Historique d'un équipement ou d'un utilisateur + ajout de commentaires libres. |
| `DemandeInscriptionController` | `store` public (vitrine) ; `index`/`approuver`/`rejeter` scopés par site pour l'Admin, non restreints pour le Super Admin (correction appliquée cette session). |
| `DemandeChangementEtatController` *(nouveau)* | `index` (scopé par site), `approuver`/`rejeter` (Admin/Super Admin, avec contrôle de site via `autoriserTraitement`), `commentaires`/`ajouterCommentaire` (discussion technicien ↔ admin, accès contrôlé via `autoriserAcces`). |
| `ContactController` | Formulaire de contact public de la vitrine. |
| `EnumController` | Sert tous les dictionnaires d'enums en un appel. |

### 1.4 Requests (`app/Http/Requests`)
Chaque `FormRequest` centralise la validation d'une action (ex. `StoreIncidentRequest`, `ResoudreIncidentRequest`, `TraiterRetourRequest`, `StoreDemandeChangementEtatCommentaireRequest`). Le contrôleur reste ainsi concentré sur la logique métier, pas sur la validation des champs.

### 1.5 Resources (`app/Http/Resources`)
Chaque `*Resource` transforme un modèle Eloquent en JSON pour le frontend : renomme les champs en camelCase, inclut les labels traduits des enums, charge conditionnellement les relations (`whenLoaded`). Ex. `EquipementResource` inclut désormais le bloc `demandeChangementEtatEnAttente` pour que le frontend sache si une demande est en cours sur cet équipement.

### 1.6 Services (`app/Services`)
Logique métier transverse, pas liée à une seule route :

- **HistoriqueService::log()** — point d'entrée unique pour tracer une action dans la table `historiques` (utilisé par presque tous les contrôleurs).
- **NotificationService::notifier()** — crée une notification en base (canal INTERFACE affiché dans l'app ; EMAIL/SMS sont des points d'extension prévus mais non branchés).
- **SupervisionService** — simule un relevé de métriques (CPU/RAM/disque, déterministe par équipement via une seed `mt_srand`) pour chaque équipement EN_LIGNE, puis évalue les `RegleAlerte` actives et crée les `Alerte` correspondantes sans doublon. Appelé par la commande `parc:superviser`.
- **PredictionService** — projette la tendance récente des métriques (moyenne récente vs ancienne, pente plafonnée) pour estimer une probabilité de panne par ressource (cpu/ram/disque) et génère une alerte préventive si le seuil est franchi.
- **ChatbotService** — choisit le moteur configuré (`RuleBasedDriver` par défaut hors-ligne, ou `OpenAiCompatibleDriver`/`AnthropicDriver` si une clé API est configurée) et transmet l'historique de conversation.
- **Chatbot/RuleBasedDriver** — base de connaissances par mots-clés (mot de passe, imprimante, réseau, lenteur, écran, email, incident...) : scoring simple par correspondance de mots-clés, réponse par défaut si rien ne correspond.

### 1.7 Console (`app/Console/Commands`)
`SuperviserParc` (`php artisan parc:superviser`) — déclenche un tick de `SupervisionService`, destiné à être planifié (cron) pour simuler la supervision en continu.

### 1.8 Routes (`routes/api.php`)
Trois niveaux de protection :
1. **Public** — `/health`, `/contact`, `/demandes-inscription` (POST), `/login`, `/enums`.
2. **`auth:sanctum`** — tout utilisateur connecté (lecture des équipements, incidents, notifications, assistant...).
3. **`role:...`** imbriqué — `SUPER_ADMIN,ADMIN` pour la gestion (créer/supprimer équipements, règles, utilisateurs, traiter les demandes d'inscription et de changement d'état) ; `SUPER_ADMIN,ADMIN,TECHNICIEN` pour les actions techniciens (résoudre un incident, modifier l'état d'un équipement, consulter/commenter les demandes de changement d'état).

Le middleware `role:` (`EnsureRole`) est le garde-fou serveur — la protection React (`RequireRole`) n'est qu'un confort d'UX, la vraie sécurité est ici.

### 1.9 Base de données (`database/`)
- **migrations/** — une migration par table/évolution de schéma, dans l'ordre chronologique. Les deux dernières ajoutées : `create_demandes_changement_etat_table` et `create_demande_changement_etat_commentaires_table`.
- **seeders/** — `DatabaseSeeder` orchestre le peuplement initial (utilisateurs de démo, etc.) ; `PeripheriquesDemoSeeder` (nouveau, idempotent via `updateOrCreate`) ajoute 8 exemples de Souris/Clavier/Écran/Socle, exécutable indépendamment (`db:seed --class=PeripheriquesDemoSeeder`).

---

## 2. Frontend (`frontend/src`)

### 2.1 `app/` — cœur applicatif
- **App.tsx** — déclare toutes les routes avec `react-router-dom` : `/login` public, tout le reste sous `RequireAuth` (redirige vers `/login` si non connecté) + `AppShell` (mise en page avec menu). Les pages sont chargées en lazy (`React.lazy`) pour garder le bundle initial léger. `demandes-inscription` et `demandes-changement-etat` sont en plus enveloppées dans `RequireRole` (ajouté cette session).
- **RequireAuth.tsx** — vérifie la session (état `ready`/`user` du contexte d'auth), affiche un loader tant que l'état n'est pas connu, sinon redirige vers `/login`.
- **RequireRole.tsx** *(nouveau)* — garde de rôle réutilisable : si l'utilisateur connecté n'a pas un rôle dans la liste autorisée, redirection silencieuse (par défaut vers `/equipements`) au lieu d'afficher une page vide/en erreur. Complète la protection serveur (`role:` middleware), ne la remplace pas.
- **AppShell** — layout général (barre de nav, sélecteur de langue, cloche de notifications).
- **navigation.ts** — définit les entrées du menu (label, icône, route, rôles autorisés à la voir).

### 2.2 `features/` — un dossier par domaine métier
Chaque domaine suit le même patron : `api.ts` (hooks React Query — `useXxx` pour lire, `useMutation` pour écrire, avec invalidation du cache après succès) + `XxxPage.tsx` (composant de page) + parfois `XxxForm.tsx`/modales dédiées.

- **auth/** — `LoginPage.tsx`, `auth-context.tsx` (contexte React exposant `user`, `login`, `logout`, `ready`).
- **equipements/** — `EquipementsPage.tsx` (liste/filtre du parc, bouton **Affecter** maintenant visible pour Admin *et* Technicien via `canManage`, badge « demande en attente »), `EquipementForm.tsx` (création/édition ; pour un Technicien, le champ état affiche un avertissement « passera par une demande d'approbation » au lieu de l'appliquer directement).
- **incidents/** — page + formulaire + modale de détail avec fil de discussion, boutons d'action selon rôle/statut (prendre en charge, résoudre, demander restitution, traiter retour, rouvrir, supprimer, assigner).
- **demandesChangementEtat/** *(nouveau)* — `DemandesChangementEtatPage.tsx` : tableau des demandes (scopé côté serveur par site), modale « Consulter » (`DemandeDetailModal`) avec fil de discussion (commentaires technicien ↔ admin) et boutons Approuver/Rejeter pour les admins ; `api.ts` : hooks `useDemandesChangementEtat`, `useApprouverDemande`, `useRejeterDemande`, `useCommentairesDemande`, `useAjouterCommentaireDemande`.
- **alertes/** — `AlertesPage.tsx` : liste des alertes, actions prendre en charge/résoudre.
- **predictions/** — `PredictionsPage.tsx` : liste des prédictions de panne, déclenchement manuel.
- **assistant/** — `AssistantPage.tsx` : interface de chat (liste des conversations à gauche, fil de messages à droite, formulaire d'envoi) branchée sur `ConversationController`/`ChatbotService`.
- **regles/** — `ReglesPage.tsx`/`RegleForm.tsx` : CRUD des règles de seuils d'alerte (Admin/Super Admin).
- **dashboard/** — `DashboardPage.tsx` : KPI (taille du parc, en ligne, alertes actives, disponibilité %), répartition par état (barres), flux d'activité récent (dernières alertes).
- **administration/** — `AdministrationPage.tsx` (gestion des utilisateurs), `DemandesInscriptionPage.tsx` (traitement des demandes de compte, désormais protégée et scopée par site).

### 2.3 `components/` — UI partagée
`PageHeader` (bandeau titre/sous-titre uniforme), `StatusPill` (pastille colorée pour un statut/enum — la carte `SIGNAL` inclut désormais EN_ATTENTE/APPROUVEE/REJETEE), `NotificationsBell`, `SearchableSelect`, `NetworkBackdrop`/`BrandMark` (identité visuelle), `LanguageSwitcher`, `ErrorBoundary` (attrape les erreurs de rendu d'une page lazy-loadée).

### 2.4 `lib/` — fondations transverses
- **api/client.ts** — instance Axios unique : URL de base selon l'environnement, intercepteur qui ajoute le token Bearer et l'en-tête `X-Locale` (langue courante) à chaque requête, gestion du 401 (purge du token).
- **api/types.ts** — types TypeScript partagés (formes des réponses API : `Equipement`, `Incident`, `DemandeChangementEtat`, etc.), garde le frontend synchronisé avec les Resources backend.
- **api/enums.ts** — hook(s) pour consommer `/api/enums` et peupler les `<select>`.
- **i18n/** — configuration i18next (`index.ts`) + fichiers `locales/fr.json`/`en.json` (textes d'interface, distincts des labels d'enums qui viennent du backend).

---

## 3. Comment expliquer l'architecture en une phrase
Le backend est l'unique source de vérité : il porte toutes les règles métier et tous les contrôles d'accès (rôle + site) via les `role:` middlewares et les vérifications explicites dans les contrôleurs ; le frontend ne fait que refléter ces règles pour l'UX (masquer un bouton, rediriger une route), jamais les appliquer lui-même. C'est pourquoi chaque nouvelle fonctionnalité de cette session (approbation des changements d'état, protection des pages de demandes) a d'abord été sécurisée côté Laravel, puis seulement reflétée côté React.
