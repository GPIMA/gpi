<?php

namespace App\Http\Controllers;

use App\Enums\EtatEquipement;
use App\Enums\MotifRetourPoste;
use App\Enums\StatutIncident;
use App\Services\HistoriqueService;
use App\Http\Requests\ResoudreIncidentRequest;
use App\Http\Requests\DemanderRestitutionRequest;
use App\Http\Requests\StoreIncidentCommentaireRequest;
use App\Http\Requests\ReouvrirIncidentRequest;
use App\Http\Requests\TraiterRetourRequest;
use App\Http\Resources\IncidentCommentaireResource;
use App\Http\Requests\StoreIncidentRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\IncidentResource;
use App\Models\Affectation;
use App\Models\Equipement;
use App\Models\Incident;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IncidentController extends Controller
{
    /**
     * Les employés ne voient que leurs incidents ; techniciens voient ceux de
     * leur site ; admins voient tout ou leur site.
     *
     * Un membre du staff (Admin/Super Admin/Technicien) peut aussi être lui-
     * même concerné par un incident (son propre poste en panne, par
     * exemple) : `mes_incidents=1` bascule alors vers la même vue
     * personnelle qu'un employé (uniquement ses propres incidents). Par
     * défaut, la vue "gestion" exclut ces incidents personnels — ils restent
     * visibles dans la vue "gestion" des AUTRES membres du staff, qui
     * peuvent toujours les assigner à un technicien.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $mesIncidents = $request->boolean('mes_incidents');

        $incidents = Incident::query()
            ->with(['equipement', 'employe', 'technicien', 'piecesJointes'])
            ->when($user->estEmploye() || $mesIncidents, fn ($q) => $q->where('employe_id', $user->id))
            ->when(! $user->estEmploye() && ! $mesIncidents, function ($q) use ($user) {
                $q->where('employe_id', '!=', $user->id);

                if ($user->estTechnicien()) {
                    $q->where('technicien_id', $user->id);
                }

                if ($user->role === \App\Enums\RoleUtilisateur::ADMIN && $user->localisation) {
                    $q->whereHas('equipement', fn ($sub) => $sub->where('localisation', $user->localisation));
                }
            })
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->string('statut')))
            ->when($request->filled('priorite'), fn ($q) => $q->where('priorite', $request->string('priorite')))
            // Distingue les incidents concernant un simple employé de ceux
            // concernant un membre du staff (technicien/admin/super admin)
            // qui a déclaré un problème pour lui-même.
            ->when($request->filled('origine'), function ($q) use ($request) {
                $origine = $request->string('origine')->toString();
                if ($origine === 'employe') {
                    $q->whereHas('employe', fn ($sub) => $sub->where('role', \App\Enums\RoleUtilisateur::EMPLOYE));
                } elseif ($origine === 'personnel') {
                    $q->whereHas('employe', fn ($sub) => $sub->where('role', '!=', \App\Enums\RoleUtilisateur::EMPLOYE));
                }
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $terme = '%'.mb_strtolower($request->string('q')->toString()).'%';
                $q->where(fn ($sub) => $sub
                    ->whereRaw('LOWER(reference) LIKE ?', [$terme])
                    ->orWhereRaw('LOWER(titre) LIKE ?', [$terme]));
            })
            ->orderByRaw("CASE statut WHEN 'OUVERT' THEN 0 WHEN 'EN_COURS' THEN 1 WHEN 'RESOLU' THEN 2 ELSE 3 END")
            ->orderByDesc('date_signalement')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return IncidentResource::collection($incidents);
    }

    /** Signaler un incident (signalerIncident du diagramme). */
    public function store(StoreIncidentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $reporter = $request->user();

        // Un Admin/Super Admin/Technicien déclare pour le compte de
        // l'utilisateur choisi ; un Employé déclare toujours pour lui-même.
        $employeId = $reporter->role !== \App\Enums\RoleUtilisateur::EMPLOYE && ! empty($data['utilisateurId'])
            ? (int) $data['utilisateurId']
            : $reporter->id;

        $incident = Incident::create([
            'equipement_id' => $data['equipementId'],
            'employe_id' => $employeId,
            'titre' => $data['titre'],
            'description' => $data['description'],
            'priorite' => $data['priorite'],
            'statut' => StatutIncident::OUVERT,
            'date_signalement' => now(),
        ]);

        if ($request->hasFile('pieceJointes')) {
            foreach ($request->file('pieceJointes') as $fichier) {
                $chemin = $fichier->store('incidents', 'public');
                $incident->piecesJointes()->create([
                    'chemin' => $chemin,
                    'nom_original' => $fichier->getClientOriginalName(),
                ]);
            }
        }

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_signale',
            $employeId === $reporter->id
                ? "Incident signalé : \"{$incident->titre}\""
                : "Incident signalé par {$reporter->nomComplet()} pour le compte de l'utilisateur : \"{$incident->titre}\"",
            auteurId: $reporter->id,
            incidentId: $incident->id,
        );

        return (new IncidentResource($incident->load(['equipement', 'employe', 'piecesJointes'])))
            ->additional(['message' => __('messages.incident.signale')])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Incident $incident): IncidentResource
    {
        $user = $request->user();
        $incident->loadMissing('equipement');

        if ($user->estEmploye() && $incident->employe_id !== $user->id) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        if ($user->estTechnicien() && $user->localisation && $incident->equipement?->localisation !== $user->localisation) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        if ($user->role === \App\Enums\RoleUtilisateur::ADMIN && $user->localisation && $incident->equipement?->localisation !== $user->localisation) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        return new IncidentResource($incident->load(['equipement', 'employe', 'technicien']));
    }

    /** Prise en charge par le technicien connecté. */
    public function prendre(Request $request, Incident $incident): JsonResponse
    {
        $incident->prendreEnCharge($request->user());

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_pris_en_charge',
            "Incident \"{$incident->titre}\" pris en charge par {$request->user()->nom}",
            auteurId: $request->user()->id,
            incidentId: $incident->id,
        );

        return (new IncidentResource($incident->load(['equipement', 'employe', 'technicien'])))->response();
    }

    /**
     * Résolution + notification de l'employé à l'origine du signalement.
     *
     * Cas particulier motif "Nouvelle date" avec poste remplaçant temporaire :
     * le 1er clic sur "Résoudre" (poste d'origine réparé) ne clôture PAS
     * l'incident tant que l'employé n'a pas rendu le remplaçant — il relance
     * l'employé à la place. Le 2e clic (remplaçant récupéré) clôture sa
     * restitution puis résout l'incident normalement.
     */
    public function resoudre(ResoudreIncidentRequest $request, Incident $incident, NotificationService $notifications): JsonResponse
    {
        $technicien = $request->user();
        $solution = $request->validated()['solution'];
        $incident->load('equipement');

        if ($incident->equipement_remplacement_id && ! $incident->relance_remplacement_le) {
            return $this->relancerRestitutionRemplacement($incident, $technicien, $solution, $notifications);
        }

        if ($incident->equipement_remplacement_id && $incident->relance_remplacement_le) {
            $this->cloturerRestitutionRemplacement($incident, $technicien->id);
        }

        $incident->resoudre($technicien, $solution);
        $incident->load(['equipement', 'employe', 'technicien']);

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_resolu',
            "Incident \"{$incident->titre}\" résolu",
            auteurId: $technicien->id,
            incidentId: $incident->id,
        );

        if ($incident->employe) {
            $notifications->notifier(
                $incident->employe,
                __('messages.incident.signale'),
                __('notifications.incident_resolu', [
                    'titre' => $incident->titre,
                    'equipement' => $incident->equipement?->nom ?? '',
                ]),
            );
        }

        return (new IncidentResource($incident))
            ->additional(['message' => __('messages.incident.resolu')])
            ->response();
    }

    /**
     * 1er clic "Résoudre" alors qu'un poste remplaçant est encore chez
     * l'employé : le poste d'origine repasse "En ligne", un message
     * automatique est posté dans la discussion (+ la note du technicien,
     * facultative) et l'employé est notifié pour venir récupérer son poste
     * et rendre le remplaçant. L'incident reste "En cours".
     */
    private function relancerRestitutionRemplacement(Incident $incident, \App\Models\User $technicien, string $solution, NotificationService $notifications): JsonResponse
    {
        if ($incident->equipement && $incident->equipement->etat === EtatEquipement::EN_MAINTENANCE) {
            $incident->equipement->update(['etat' => EtatEquipement::EN_LIGNE]);
        }

        $remplacant = $incident->equipementRemplacement;

        $message = __('notifications.relance_remplacement', [
            'titre' => $incident->titre,
            'ancien' => $incident->equipement?->nom ?? '',
            'remplacant' => $remplacant?->nom ?? '',
        ]);

        $incident->commentaires()->create(['auteur_id' => $technicien->id, 'contenu' => $message]);
        if ($solution) {
            $incident->commentaires()->create(['auteur_id' => $technicien->id, 'contenu' => $solution]);
        }

        $incident->update(['relance_remplacement_le' => now()]);

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_relance_remplacement',
            "Employé relancé pour restituer le poste remplaçant \"{$remplacant?->nom}\" et récupérer \"{$incident->equipement?->nom}\"",
            auteurId: $technicien->id,
            incidentId: $incident->id,
        );

        if ($incident->employe) {
            $notifications->notifier($incident->employe, __('messages.incident.signale'), $message);
        }

        $incident->load(['equipement', 'equipementRemplacement', 'employe', 'technicien']);

        return (new IncidentResource($incident))
            ->additional(['message' => __('messages.incident.relance_remplacement')])
            ->response();
    }

    /**
     * 2e clic "Résoudre" : le poste remplaçant a été rendu. Son affectation
     * temporaire est clôturée et il repasse "Hors ligne", prêt pour une
     * nouvelle affectation.
     */
    private function cloturerRestitutionRemplacement(Incident $incident, ?int $auteurId = null): void
    {
        $remplacant = $incident->equipementRemplacement;

        if ($remplacant) {
            $affectation = $remplacant->affectations()
                ->where('employe_id', $incident->employe_id)
                ->where('statut', 'EN_COURS')
                ->first();
            if ($affectation) {
                $affectation->update(['statut' => 'TERMINEE', 'date_retour' => now()]);
            }
            $remplacant->update(['etat' => EtatEquipement::HORS_LIGNE]);

            HistoriqueService::log(
                $incident->employe_id,
                $remplacant->id,
                'restitution_remplacement',
                "Poste remplaçant temporaire \"{$remplacant->nom}\" récupéré, remis hors ligne",
                auteurId: $auteurId,
                incidentId: $incident->id,
            );
        }

        $incident->update(['equipement_remplacement_id' => null, 'relance_remplacement_le' => null]);
    }
/** Demande à l'employé de ramener le poste (incident mis/maintenu "En cours"). */
    public function demanderRestitution(DemanderRestitutionRequest $request, Incident $incident, NotificationService $notifications): JsonResponse
    {
        $date = \Carbon\Carbon::parse($request->validated()['dateRestitution']);
        $incident->demanderRestitution($request->user(), $date);
        $incident->load(['equipement', 'employe', 'technicien']);

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_restitution_demandee',
            "Restitution du poste demandée pour l'incident \"{$incident->titre}\"",
            auteurId: $request->user()->id,
            incidentId: $incident->id,
        );

        $messageEmploye = __('notifications.incident_restitution_demandee', [
            'titre' => $incident->titre,
            'equipement' => $incident->equipement?->nom ?? '',
            'date' => $date->format('d/m/Y H:i'),
        ]);

        // Posté dans la discussion en plus de la notification, pour que
        // l'employé voie clairement ce qui est attendu de lui.
        $incident->commentaires()->create([
            'auteur_id' => $request->user()->id,
            'contenu' => $messageEmploye,
        ]);

        if ($incident->employe) {
            $notifications->notifier($incident->employe, __('messages.incident.signale'), $messageEmploye);
        }

        return (new IncidentResource($incident))
            ->additional(['message' => 'Restitution demandée.'])
            ->response();
    }
    /**
     * Le technicien confirme la réception du poste ramené par l'employé et
     * choisit l'un des 3 motifs : maintenance sur place, nouvelle date de
     * restitution (maintenance plus longue), ou remplacement du poste endommagé.
     */
    public function traiterRetour(TraiterRetourRequest $request, Incident $incident, NotificationService $notifications): JsonResponse
    {
        $technicien = $request->user();
        $data = $request->validated();
        $motif = MotifRetourPoste::from($data['motif']);
        $commentaire = $data['commentaire'] ?? null;

        // Marque que le poste a bien été reçu par le technicien : à partir
        // de maintenant, "date_restitution_prevue" (si le motif "Nouvelle
        // date" la redéfinit) ne signifie plus "l'employé doit ramener le
        // poste" mais "le poste réparé lui sera rendu" — le bouton "Confirmer
        // réception" ne doit donc plus réapparaître pour cet incident.
        $incident->update(['date_reception_poste' => now()]);

        $messageEmploye = match ($motif) {
            MotifRetourPoste::MAINTENANCE_SUR_PLACE => $this->traiterMaintenanceSurPlace($incident),
            MotifRetourPoste::NOUVELLE_DATE => $this->traiterNouvelleDate(
                $incident,
                \Carbon\Carbon::parse($data['dateRestitution']),
                isset($data['nouvelEquipementRemplacementId']) ? (int) $data['nouvelEquipementRemplacementId'] : null,
                $technicien->id,
            ),
            MotifRetourPoste::POSTE_REMPLACE => $this->traiterPosteRemplace($technicien, $incident, (int) $data['nouvelEquipementId']),
        };

        // Le message automatique est toujours posté dans la discussion pour que
        // l'employé voie ce qui a été décidé, en plus de la notification.
        $incident->commentaires()->create([
            'auteur_id' => $technicien->id,
            'contenu' => $messageEmploye,
        ]);

        if ($commentaire) {
            $incident->commentaires()->create([
                'auteur_id' => $technicien->id,
                'contenu' => $commentaire,
            ]);
        }

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_retour_traite',
            "Retour du poste traité pour l'incident \"{$incident->titre}\" ({$motif->label()})",
            auteurId: $technicien->id,
            incidentId: $incident->id,
        );

        $incident->load(['equipement', 'employe', 'technicien']);

        if ($incident->employe) {
            $notifications->notifier($incident->employe, __('messages.incident.signale'), $messageEmploye);
        }

        return (new IncidentResource($incident))
            ->additional(['message' => __('messages.incident.retour_traite')])
            ->response();
    }

    /** Motif 1 : la maintenance est effectuée sur place, l'incident passe "En maintenance". */
    private function traiterMaintenanceSurPlace(Incident $incident): string
    {
        $incident->update([
            'statut' => StatutIncident::EN_MAINTENANCE,
            'date_restitution_prevue' => null,
        ]);

        $incident->equipement?->update(['etat' => EtatEquipement::EN_MAINTENANCE]);

        return __('notifications.retour_maintenance_sur_place', ['titre' => $incident->titre]);
    }

    /**
     * Motif 2 : la maintenance prendra plus de temps, une nouvelle date de
     * restitution est fixée. L'ancien poste part en maintenance mais reste
     * affecté à l'employé (il lui sera restitué une fois réparé). Un poste
     * remplaçant temporaire peut lui être affecté pour patienter.
     */
    private function traiterNouvelleDate(Incident $incident, \Carbon\Carbon $nouvelleDate, ?int $nouvelEquipementRemplacementId = null, ?int $auteurId = null): string
    {
        $incident->update([
            'statut' => StatutIncident::EN_COURS,
            'date_restitution_prevue' => $nouvelleDate,
        ]);

        $incident->equipement?->update(['etat' => EtatEquipement::EN_MAINTENANCE]);

        $message = __('notifications.retour_nouvelle_date', [
            'titre' => $incident->titre,
            'date' => $nouvelleDate->format('d/m/Y H:i'),
        ]);

        if ($nouvelEquipementRemplacementId) {
            $remplacant = Equipement::findOrFail($nouvelEquipementRemplacementId);

            Affectation::create([
                'employe_id' => $incident->employe_id,
                'equipement_id' => $remplacant->id,
                'date_affectation' => now(),
                'statut' => 'EN_COURS',
            ]);
            $remplacant->update(['etat' => EtatEquipement::EN_LIGNE]);

            // Référence gardée sur l'incident pour savoir, au moment de la
            // résolution, qu'un poste remplaçant est encore à récupérer.
            $incident->update(['equipement_remplacement_id' => $remplacant->id]);

            HistoriqueService::log(
                $incident->employe_id,
                $remplacant->id,
                'affectation_temporaire',
                "Poste remplaçant temporaire \"{$remplacant->nom}\" affecté en attendant la réparation de \"{$incident->equipement?->nom}\"",
                auteurId: $auteurId,
                incidentId: $incident->id,
            );

            $message .= __('notifications.retour_nouvelle_date_remplacant', ['equipement' => $remplacant->nom]);
        }

        return $message;
    }

    /** Motif 3 : le poste est endommagé. Un poste disponible du parc est attribué à l'employé, l'incident est résolu. */
    private function traiterPosteRemplace(\App\Models\User $technicien, Incident $incident, int $nouvelEquipementId): string
    {
        $ancien = $incident->equipement;
        $nouveau = Equipement::findOrFail($nouvelEquipementId);

        $affectationActive = $ancien?->affectations()->where('statut', 'EN_COURS')->first();
        if ($affectationActive) {
            $affectationActive->update(['statut' => 'TERMINEE', 'date_retour' => now()]);
        }

        $ancien?->update(['etat' => EtatEquipement::EN_PANNE]);

        Affectation::create([
            'employe_id' => $incident->employe_id,
            'equipement_id' => $nouveau->id,
            'date_affectation' => now(),
            'statut' => 'EN_COURS',
        ]);

        $incident->update([
            'statut' => StatutIncident::RESOLU,
            'date_restitution_prevue' => null,
            'date_resolution' => now(),
            'solution' => __('notifications.retour_poste_remplace', ['titre' => $incident->titre, 'equipement' => $nouveau->nom]),
            'technicien_id' => $incident->technicien_id ?? $technicien->id,
        ]);

        HistoriqueService::log(
            $incident->employe_id,
            $nouveau->id,
            'affectation',
            "Équipement \"{$nouveau->nom}\" affecté suite au remplacement du poste endommagé",
            auteurId: $technicien->id,
            incidentId: $incident->id,
        );

        return __('notifications.retour_poste_remplace', [
            'titre' => $incident->titre,
            'equipement' => $nouveau->nom,
        ]);
    }

    /** Liste des commentaires (discussion) d'un incident. */
    public function commentaires(Request $request, Incident $incident): AnonymousResourceCollection
    {
        $user = $request->user();
        $incident->loadMissing('equipement');

        if ($user->estEmploye() && $incident->employe_id !== $user->id) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        if ($user->estTechnicien() && $user->localisation && $incident->equipement?->localisation !== $user->localisation) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        return IncidentCommentaireResource::collection($incident->commentaires()->with('auteur')->get());
    }
    /** Ajout d'un commentaire dans la discussion employé ↔ technicien. */
    public function ajouterCommentaire(StoreIncidentCommentaireRequest $request, Incident $incident, NotificationService $notifications): JsonResponse
    {
        $user = $request->user();
        $incident->loadMissing(['equipement', 'employe', 'technicien']);

        if ($user->estEmploye() && $incident->employe_id !== $user->id) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        if ($user->estTechnicien() && $user->localisation && $incident->equipement?->localisation !== $user->localisation) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        $commentaire = $incident->commentaires()->create([
            'auteur_id' => $user->id,
            'contenu' => $request->validated()['contenu'],
        ]);
        $commentaire->load('auteur');

        $destinataire = $user->id === $incident->employe_id ? $incident->technicien : $incident->employe;

        if ($destinataire) {
            $notifications->notifier(
                $destinataire,
                __('messages.incident.signale'),
                __('notifications.incident_commentaire', [
                    'titre' => $incident->titre,
                    'auteur' => $user->nomComplet(),
                ]),
            );
        }

        return (new IncidentCommentaireResource($commentaire))->response()->setStatusCode(201);
    }
    /** Réouverture par l'employé si le problème persiste, dans les 5 jours suivant la résolution. */
    public function reouvrir(ReouvrirIncidentRequest $request, Incident $incident, NotificationService $notifications): JsonResponse
    {
        $user = $request->user();

        if ($incident->employe_id !== $user->id) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        if ($incident->statut !== StatutIncident::RESOLU) {
            throw ValidationException::withMessages([
                'statut' => ["Cet incident n'est pas résolu."],
            ]);
        }

        if (! $incident->date_resolution || now()->greaterThan($incident->date_resolution->copy()->addDays(5))) {
            throw ValidationException::withMessages([
                'statut' => ["Le délai de réouverture (5 jours après résolution) est dépassé. Merci de signaler un nouvel incident."],
            ]);
        }

        $message = $request->validated()['message'];

        $incident->reouvrir();
        $incident->commentaires()->create([
            'auteur_id' => $user->id,
            'contenu' => $message,
        ]);
        $incident->load(['equipement', 'employe', 'technicien']);

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_reouvert',
            "Incident \"{$incident->titre}\" rouvert par l'employé : {$message}",
            auteurId: $user->id,
            incidentId: $incident->id,
        );

        if ($incident->technicien) {
            $notifications->notifier(
                $incident->technicien,
                __('messages.incident.signale'),
                __('notifications.incident_reouvert', [
                    'titre' => $incident->titre,
                    'equipement' => $incident->equipement?->nom ?? '',
                ]),
            );
        }

        return (new IncidentResource($incident))
            ->additional(['message' => 'Incident rouvert.'])
            ->response();
    }

    /**
     * Suppression définitive par l'employé concerné, quand le problème n'a
     * plus lieu d'être. Un commentaire justifiant la suppression est
     * obligatoire et conservé dans l'historique (l'incident, lui, disparaît
     * complètement — commentaires et pièces jointes inclus).
     */
    public function supprimer(Request $request, Incident $incident, NotificationService $notifications): JsonResponse
    {
        $user = $request->user();

        if ($incident->employe_id !== $user->id) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        // Une fois pris en charge (statut "En cours" ou au-delà), la
        // suppression n'est plus possible : il faut passer par la réouverture
        // ou laisser le technicien traiter l'incident.
        if ($incident->statut !== StatutIncident::OUVERT) {
            throw ValidationException::withMessages([
                'statut' => ["Cet incident n'est plus \"Ouvert\" et ne peut plus être supprimé."],
            ]);
        }

        $data = $request->validate([
            'commentaire' => ['required', 'string', 'max:1000'],
        ]);

        $incident->loadMissing(['equipement', 'technicien', 'piecesJointes']);

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_supprime',
            "Incident \"{$incident->titre}\" supprimé par {$user->nomComplet()}. Motif : {$data['commentaire']}",
            auteurId: $user->id,
        );

        if ($incident->technicien) {
            $notifications->notifier(
                $incident->technicien,
                __('messages.incident.signale'),
                __('notifications.incident_supprime', [
                    'titre' => $incident->titre,
                    'equipement' => $incident->equipement?->nom ?? '',
                    'motif' => $data['commentaire'],
                ]),
            );
        }

        // Nettoyage des fichiers physiques avant la suppression en cascade
        // des lignes en base (commentaires, pièces jointes).
        foreach ($incident->piecesJointes as $piece) {
            Storage::disk('public')->delete($piece->chemin);
        }
        if ($incident->piece_jointe) {
            Storage::disk('public')->delete($incident->piece_jointe);
        }

        $incident->delete();

        return response()->json(['message' => 'Incident supprimé.']);
    }

    /**
     * L'admin/super admin consulte un incident nouvellement signalé : le
     * simple fait de l'ouvrir le fait passer de "Ouvert" à "En cours" (il
     * n'est plus en attente de traitement, mais reste sans technicien tant
     * qu'il n'a pas été assigné).
     */
    public function consulter(Request $request, Incident $incident): JsonResponse
    {
        $user = $request->user();
        $incident->loadMissing('equipement');

        if ($user->role === \App\Enums\RoleUtilisateur::ADMIN && $user->localisation && $incident->equipement?->localisation !== $user->localisation) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        if ($incident->statut === StatutIncident::OUVERT) {
            $incident->update(['statut' => StatutIncident::EN_COURS]);

            HistoriqueService::log(
                $incident->employe_id,
                $incident->equipement_id,
                'incident_consulte',
                "Incident \"{$incident->titre}\" consulté par {$request->user()->nom}",
                auteurId: $user->id,
                incidentId: $incident->id,
            );
        }

        return (new IncidentResource($incident->load(['equipement', 'employe', 'technicien'])))->response();
    }

    /** Assignation d'un technicien par l'admin. */
    public function assigner(Request $request, Incident $incident): JsonResponse
    {
        $data = $request->validate([
            'technicien_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        // Un technicien ne peut pas être désigné pour gérer son propre
        // incident tant qu'un autre technicien est disponible sur le même
        // site. S'il est le seul technicien du site, l'exception saute :
        // mieux vaut qu'il traite lui-même son cas plutôt qu'un incident qui
        // reste bloqué faute d'un autre technicien à assigner.
        if ((int) $data['technicien_id'] === $incident->employe_id) {
            $incident->loadMissing('equipement');

            $autreTechnicienDisponible = \App\Models\User::query()
                ->where('role', \App\Enums\RoleUtilisateur::TECHNICIEN)
                ->where('id', '!=', $incident->employe_id)
                ->when($incident->equipement?->localisation, fn ($q, $loc) => $q->where('localisation', $loc))
                ->exists();

            if ($autreTechnicienDisponible) {
                throw ValidationException::withMessages([
                    'technicien_id' => ["Un autre technicien est disponible sur ce site : ce technicien ne peut pas être assigné à son propre incident."],
                ]);
            }
        }

        $incident->update([
            'technicien_id' => $data['technicien_id'],
            'statut' => StatutIncident::EN_COURS,
        ]);

        HistoriqueService::log(
            $incident->employe_id,
            $incident->equipement_id,
            'incident_assigne',
            "Technicien assigné à l'incident \"{$incident->titre}\"",
            auteurId: $request->user()->id,
            incidentId: $incident->id,
        );

        return (new IncidentResource($incident->load(['equipement', 'employe', 'technicien'])))
            ->additional(['message' => 'Incident assigné.'])
            ->response();
    }
}