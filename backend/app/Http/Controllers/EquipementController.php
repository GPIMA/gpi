<?php

namespace App\Http\Controllers;

use App\Enums\EtatEquipement;
use App\Enums\RoleUtilisateur;
use App\Enums\StatutDemandeChangementEtat;
use App\Services\HistoriqueService;
use App\Services\NotificationService;
use App\Http\Requests\StoreEquipementRequest;
use App\Http\Requests\UpdateEquipementRequest;
use App\Http\Resources\EquipementResource;
use App\Models\Affectation;
use App\Models\DemandeChangementEtat;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Validation\ValidationException;

class EquipementController extends Controller
{
    /** Liste filtrable et paginée du parc, scopée selon le rôle de l'utilisateur. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $equipements = Equipement::query()
            ->with(['affectationActive.employe', 'demandeChangementEtatEnAttente'])
           ->when($user->role === RoleUtilisateur::EMPLOYE, function ($q) use ($user) {
    $q->whereHas('affectations', function ($sub) use ($user) {
        $sub->where('employe_id', $user->id)->where('statut', 'EN_COURS');
    });
})
->when($user->role === RoleUtilisateur::ADMIN && $user->localisation, function ($q) use ($user) {
                $q->where('localisation', $user->localisation);
            })
->when($user->role === RoleUtilisateur::TECHNICIEN && $user->localisation, function ($q) use ($user) {
                $q->where('localisation', $user->localisation);
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('etat'), fn ($q) => $q->where('etat', $request->string('etat')))
            ->when($request->filled('localisation'), fn ($q) => $q->where('localisation', $request->string('localisation')))
            // Équipements actuellement affectés à un utilisateur donné — sert
            // notamment au champ "Équipement concerné" quand un Admin/Technicien/
            // Super Admin déclare un incident pour le compte de quelqu'un d'autre.
            ->when($request->filled('assigne_a'), function ($q) use ($request) {
                $q->whereHas('affectations', fn ($sub) => $sub
                    ->where('employe_id', $request->integer('assigne_a'))
                    ->where('statut', 'EN_COURS'));
            })
            ->when($request->filled('statut_affectation'), function ($q) use ($request) {
                $statut = $request->string('statut_affectation')->toString();
                if ($statut === 'affecte') {
                    $q->whereHas('affectations', fn ($sub) => $sub->where('statut', 'EN_COURS'));
                } elseif ($statut === 'disponible') {
                    $q->whereDoesntHave('affectations', fn ($sub) => $sub->where('statut', 'EN_COURS'));
                }
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $terme = '%'.mb_strtolower($request->string('q')->toString()).'%';

                $q->where(fn ($sub) => $sub
                    ->whereRaw('LOWER(nom) LIKE ?', [$terme])
                    ->orWhereRaw('LOWER(COALESCE(adresse_ip, \'\')) LIKE ?', [$terme])
                    ->orWhereRaw('LOWER(COALESCE(marque, \'\')) LIKE ?', [$terme])
                    ->orWhereRaw('LOWER(COALESCE(modele, \'\')) LIKE ?', [$terme])
                    ->orWhereRaw('LOWER(COALESCE(localisation, \'\')) LIKE ?', [$terme]));
            })
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return EquipementResource::collection($equipements);
    }
    /** Liste des localisations distinctes utilisées, pour peupler le filtre. */
    public function localisations(): JsonResponse
    {
        $localisations = Equipement::query()
            ->whereNotNull('localisation')
            ->where('localisation', '!=', '')
            ->distinct()
            ->orderBy('localisation')
            ->pluck('localisation');

        return response()->json(['data' => $localisations]);
    }
   public function store(StoreEquipementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $employeId = $validated['employeId'] ?? null;

        if ($employeId && ! $request->boolean('forcerAffectation')) {
            $this->verifierConflitAffectation($employeId, $validated['type']);
        }
$this->verifierAccesSite($request->user(), $validated['localisation'] ?? null);
        $equipement = Equipement::create($request->donnees());

        $this->synchroniserAffectation($equipement, $employeId, $request->user()->id);

        return (new EquipementResource($equipement->load(['affectationActive.employe'])))
            ->additional(['message' => __('messages.equipement.cree')])
            ->response()
            ->setStatusCode(201);
    }
    public function show(Request $request, Equipement $equipement): EquipementResource
    {
        $user = $request->user();

        if ($user->role === RoleUtilisateur::EMPLOYE) {
            $estAffecte = $equipement->affectations()
                ->where('employe_id', $user->id)
                ->where('statut', 'EN_COURS')
                ->exists();

            if (! $estAffecte) {
                throw new AccessDeniedHttpException(__('messages.forbidden'));
            }
        }

        if ($user->role === RoleUtilisateur::TECHNICIEN && $user->localisation && $equipement->localisation !== $user->localisation) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        $equipement->load(['affectationActive.employe', 'demandeChangementEtatEnAttente']);

        return new EquipementResource($equipement);
    }

    public function update(UpdateEquipementRequest $request, Equipement $equipement, NotificationService $notifications): JsonResponse
    {
        $user = $request->user();

        if ($user->role === RoleUtilisateur::TECHNICIEN && $user->localisation && $equipement->localisation !== $user->localisation) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        $validated = $request->validated();
        $employeId = array_key_exists('employeId', $validated) ? $validated['employeId'] : $equipement->affectationActive?->employe_id;

        if ($employeId && ! $request->boolean('forcerAffectation')) {
            $type = $validated['type'] ?? $equipement->type->value;
            $this->verifierConflitAffectation($employeId, $type, $equipement->id);
        }
$this->verifierAccesSite($request->user(), $validated['localisation'] ?? $equipement->localisation);

        $data = $request->donnees();
        $demande = null;

        // Un technicien ne peut pas changer l'état d'un équipement en direct
        // (hors résolution d'incident, qui passe par IncidentController et
        // n'utilise pas cette route) : le changement reste en attente d'une
        // approbation Admin/Super Admin plutôt que d'être appliqué.
        if (
            $user->role === RoleUtilisateur::TECHNICIEN
            && array_key_exists('etat', $data)
            && $data['etat'] !== $equipement->etat->value
        ) {
            $demande = $this->demanderChangementEtat($equipement, $user, $data['etat'], $notifications);
            unset($data['etat']);
        }

        $equipement->update($data);

        if (array_key_exists('employeId', $validated)) {
            $this->synchroniserAffectation($equipement, $validated['employeId'], $request->user()->id);
        }

        $message = $demande
            ? __('messages.equipement.modifie').' '.__('messages.equipement.changement_etat_en_attente')
            : __('messages.equipement.modifie');

        return (new EquipementResource($equipement->fresh(['affectationActive.employe', 'demandeChangementEtatEnAttente'])))
            ->additional(['message' => $message])
            ->response();
    }

    /**
     * Crée une demande de changement d'état en attente et notifie les
     * Admins du site concerné (+ les Admins sans site, non restreints) ainsi
     * que tous les Super Admins.
     */
    private function demanderChangementEtat(
        Equipement $equipement,
        User $demandeur,
        string $etatDemande,
        NotificationService $notifications,
    ): DemandeChangementEtat {
        $dejaEnAttente = DemandeChangementEtat::query()
            ->where('equipement_id', $equipement->id)
            ->where('statut', StatutDemandeChangementEtat::EN_ATTENTE)
            ->exists();

        if ($dejaEnAttente) {
            throw ValidationException::withMessages([
                'etat' => ["Une demande de changement de statut est déjà en attente d'approbation pour cet équipement."],
            ]);
        }

        $demande = DemandeChangementEtat::create([
            'equipement_id' => $equipement->id,
            'demandeur_id' => $demandeur->id,
            'etat_actuel' => $equipement->etat->value,
            'etat_demande' => $etatDemande,
            'statut' => StatutDemandeChangementEtat::EN_ATTENTE,
        ]);

        HistoriqueService::log(
            $demandeur->id,
            $equipement->id,
            'changement_etat_demande',
            "Changement de statut demandé par {$demandeur->nomComplet()} pour \"{$equipement->nom}\" : {$equipement->etat->label()} → {$demande->etat_demande->label()}",
            auteurId: $demandeur->id,
        );

        $destinataires = User::query()
            ->where(function ($q) use ($equipement) {
                $q->where('role', RoleUtilisateur::SUPER_ADMIN)
                    ->orWhere(function ($q2) use ($equipement) {
                        $q2->where('role', RoleUtilisateur::ADMIN)
                            ->where(function ($q3) use ($equipement) {
                                $q3->whereNull('localisation')->orWhere('localisation', $equipement->localisation);
                            });
                    });
            })
            ->get();

        foreach ($destinataires as $destinataire) {
            $notifications->notifier(
                $destinataire,
                __('messages.equipement.modifie'),
                __('notifications.changement_etat_demande', [
                    'technicien' => $demandeur->nomComplet(),
                    'equipement' => $equipement->nom,
                    'actuel' => $equipement->etat->label(),
                    'demande' => $demande->etat_demande->label(),
                ]),
            );
        }

        return $demande;
    }
    public function destroy(Equipement $equipement): JsonResponse
    {
        $equipement->delete();

        return response()->json(['message' => __('messages.equipement.supprime')]);
    }
    /**
     * Empêche un Admin (pas Super Admin) de créer/modifier un équipement
     * en dehors de son propre site.
     */
    private function verifierAccesSite(User $currentUser, ?string $localisation): void
    {
        if ($currentUser->role !== RoleUtilisateur::ADMIN || ! $currentUser->localisation) {
            return;
        }

        if ($localisation !== $currentUser->localisation) {
            throw ValidationException::withMessages([
                'localisation' => ["Vous ne pouvez gérer que les équipements du site {$currentUser->localisation}."],
            ]);
        }
    }
    /**
     * Bloque l'affectation si l'employé possède déjà un équipement actif du
     * même type, sauf si l'admin coche « Forcer l'affectation ».
     */
    private function verifierConflitAffectation(int $employeId, string $type, ?int $excludeEquipementId = null): void
    {
        $conflit = Equipement::query()
            ->where('type', $type)
            ->when($excludeEquipementId, fn ($q) => $q->where('id', '!=', $excludeEquipementId))
            ->whereHas('affectations', function ($q) use ($employeId) {
                $q->where('employe_id', $employeId)->where('statut', 'EN_COURS');
            })
            ->first();

        if ($conflit) {
            throw ValidationException::withMessages([
                'employeId' => ["Cet utilisateur possède déjà un équipement de type « {$conflit->type->label()} » (\"{$conflit->nom}\"). Cochez « Forcer l'affectation » pour l'ajouter quand même."],
            ]);
        }
    }
    /**
     * Crée, change ou termine l'affectation active d'un équipement selon
     * l'employé sélectionné dans le formulaire (null = retirer l'affectation).
     */
    private function synchroniserAffectation(Equipement $equipement, ?int $employeId, ?int $auteurId = null): void
{
    $active = $equipement->affectations()->where('statut', 'EN_COURS')->first();

    if ($active && $active->employe_id === $employeId) {
        return;
    }

    if ($active) {
        $active->update(['statut' => 'TERMINEE', 'date_retour' => now()]);

        HistoriqueService::log(
            $active->employe_id,
            $equipement->id,
            'retour',
            "Équipement \"{$equipement->nom}\" retiré à l'utilisateur",
            auteurId: $auteurId,
        );
    }

    if ($employeId) {
        Affectation::create([
            'employe_id' => $employeId,
            'equipement_id' => $equipement->id,
            'date_affectation' => now(),
            'statut' => 'EN_COURS',
        ]);

        if ($equipement->etat !== EtatEquipement::EN_LIGNE) {
            $equipement->update(['etat' => EtatEquipement::EN_LIGNE]);
        }

        HistoriqueService::log(
            $employeId,
            $equipement->id,
            'affectation',
            "Équipement \"{$equipement->nom}\" affecté à l'utilisateur",
            auteurId: $auteurId,
        );
    }
}
}