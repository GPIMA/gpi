<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutDemandeChangementEtat;
use App\Http\Requests\StoreDemandeChangementEtatCommentaireRequest;
use App\Http\Resources\DemandeChangementEtatCommentaireResource;
use App\Http\Resources\DemandeChangementEtatResource;
use App\Models\DemandeChangementEtat;
use App\Models\Equipement;
use App\Models\User;
use App\Services\HistoriqueService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Demandes de changement d'état soumises par un technicien (hors résolution
 * d'incident) — en attente d'approbation par un Admin ou un Super Admin.
 */
class DemandeChangementEtatController extends Controller
{
    /**
     * Technicien : uniquement ses propres demandes. Admin : celles de son
     * site (ou toutes, s'il n'a pas de site). Super Admin : tout.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $demandes = DemandeChangementEtat::query()
            ->with(['equipement', 'demandeur', 'traitePar'])
            ->when($user->role === RoleUtilisateur::TECHNICIEN, fn ($q) => $q->where('demandeur_id', $user->id))
            ->when($user->role === RoleUtilisateur::ADMIN && $user->localisation, function ($q) use ($user) {
                $q->whereHas('equipement', fn ($sub) => $sub->where('localisation', $user->localisation));
            })
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->string('statut')))
            ->orderByRaw("CASE statut WHEN 'EN_ATTENTE' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return DemandeChangementEtatResource::collection($demandes);
    }

    /** Applique l'état demandé et clôture la demande. */
    public function approuver(Request $request, DemandeChangementEtat $demande, NotificationService $notifications): JsonResponse
    {
        $this->autoriserTraitement($request, $demande);

        if (! $demande->estEnAttente()) {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $demande->load('equipement', 'demandeur');
        $ancienEtat = $demande->equipement->etat;
        $admin = $request->user();

        $demande->equipement->update(['etat' => $demande->etat_demande]);

        $demande->update([
            'statut' => StatutDemandeChangementEtat::APPROUVEE,
            'traite_par_id' => $admin->id,
            'traite_le' => now(),
        ]);

        HistoriqueService::log(
            $demande->demandeur_id,
            $demande->equipement_id,
            'changement_etat_approuve',
            "Changement de statut demandé par {$demande->demandeur?->nomComplet()} approuvé par {$admin->nomComplet()} pour \"{$demande->equipement->nom}\" : {$ancienEtat->label()} → {$demande->etat_demande->label()}",
            auteurId: $admin->id,
        );

        $notifications->notifier(
            $demande->demandeur,
            __('messages.equipement.modifie'),
            __('notifications.changement_etat_approuvee', [
                'equipement' => $demande->equipement->nom,
                'etat' => $demande->etat_demande->label(),
            ]),
        );

        return (new DemandeChangementEtatResource($demande->load('traitePar')))
            ->additional(['message' => __('messages.equipement.changement_etat_approuve')])
            ->response();
    }

    /** Rejette la demande sans toucher à l'équipement. */
    public function rejeter(Request $request, DemandeChangementEtat $demande, NotificationService $notifications): JsonResponse
    {
        $this->autoriserTraitement($request, $demande);

        if (! $demande->estEnAttente()) {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $data = $request->validate([
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $demande->load('equipement', 'demandeur');
        $admin = $request->user();

        $demande->update([
            'statut' => StatutDemandeChangementEtat::REJETEE,
            'traite_par_id' => $admin->id,
            'traite_le' => now(),
            'commentaire_traitement' => $data['commentaire'] ?? null,
        ]);

        HistoriqueService::log(
            $demande->demandeur_id,
            $demande->equipement_id,
            'changement_etat_rejete',
            "Changement de statut demandé par {$demande->demandeur?->nomComplet()} rejeté par {$admin->nomComplet()} pour \"{$demande->equipement->nom}\" (demandé : {$demande->etat_demande->label()})".
                (isset($data['commentaire']) ? " — Motif : {$data['commentaire']}" : ''),
            auteurId: $admin->id,
        );

        $notifications->notifier(
            $demande->demandeur,
            __('messages.equipement.modifie'),
            __('notifications.changement_etat_rejetee', [
                'equipement' => $demande->equipement->nom,
                'etat' => $demande->etat_demande->label(),
            ]),
        );

        return (new DemandeChangementEtatResource($demande->load('traitePar')))
            ->additional(['message' => __('messages.equipement.changement_etat_rejete')])
            ->response();
    }

    /** Discussion : historique des messages entre demandeur et Admin/Super Admin. */
    public function commentaires(Request $request, DemandeChangementEtat $demande): AnonymousResourceCollection
    {
        $this->autoriserAcces($request, $demande);

        return DemandeChangementEtatCommentaireResource::collection(
            $demande->commentaires()->with('auteur')->get()
        );
    }

    /** Ajout d'un message dans la discussion, notifiant l'autre partie. */
    public function ajouterCommentaire(
        StoreDemandeChangementEtatCommentaireRequest $request,
        DemandeChangementEtat $demande,
        NotificationService $notifications,
    ): JsonResponse {
        $this->autoriserAcces($request, $demande);
        $user = $request->user();
        $demande->loadMissing('equipement', 'demandeur');

        $commentaire = $demande->commentaires()->create([
            'auteur_id' => $user->id,
            'contenu' => $request->validated()['contenu'],
        ]);
        $commentaire->load('auteur');

        // Le demandeur commente → on notifie les approbateurs éligibles ;
        // un Admin/Super Admin commente → on notifie uniquement le demandeur.
        if ($user->id === $demande->demandeur_id) {
            foreach ($this->approbateursEligibles($demande->equipement) as $destinataire) {
                $notifications->notifier(
                    $destinataire,
                    __('messages.equipement.modifie'),
                    __('notifications.changement_etat_commentaire', [
                        'auteur' => $user->nomComplet(),
                        'equipement' => $demande->equipement->nom,
                    ]),
                );
            }
        } elseif ($demande->demandeur) {
            $notifications->notifier(
                $demande->demandeur,
                __('messages.equipement.modifie'),
                __('notifications.changement_etat_commentaire', [
                    'auteur' => $user->nomComplet(),
                    'equipement' => $demande->equipement->nom,
                ]),
            );
        }

        return (new DemandeChangementEtatCommentaireResource($commentaire))->response()->setStatusCode(201);
    }

    /** Un Admin lié à un site ne peut traiter que les demandes de son site. */
    private function autoriserTraitement(Request $request, DemandeChangementEtat $demande): void
    {
        $user = $request->user();

        if ($user->role === RoleUtilisateur::ADMIN && $user->localisation) {
            $demande->loadMissing('equipement');

            if ($demande->equipement?->localisation !== $user->localisation) {
                throw new AccessDeniedHttpException(__('messages.forbidden'));
            }
        }
    }

    /**
     * Un Technicien ne peut accéder qu'à ses propres demandes. Un Admin lié
     * à un site ne peut accéder qu'à celles de son site. Super Admin : tout.
     */
    private function autoriserAcces(Request $request, DemandeChangementEtat $demande): void
    {
        $user = $request->user();

        if ($user->role === RoleUtilisateur::TECHNICIEN && $demande->demandeur_id !== $user->id) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        if ($user->role === RoleUtilisateur::ADMIN && $user->localisation) {
            $demande->loadMissing('equipement');

            if ($demande->equipement?->localisation !== $user->localisation) {
                throw new AccessDeniedHttpException(__('messages.forbidden'));
            }
        }
    }

    /** Admins du site (+ ceux sans site) et tous les Super Admins — mêmes destinataires qu'à la création de la demande. */
    private function approbateursEligibles(Equipement $equipement): \Illuminate\Support\Collection
    {
        return User::query()
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
    }
}
