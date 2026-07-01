<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Http\Requests\StoreEquipementRequest;
use App\Http\Requests\UpdateEquipementRequest;
use App\Http\Resources\EquipementResource;
use App\Models\Affectation;
use App\Models\Equipement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EquipementController extends Controller
{
    /** Liste filtrable et paginée du parc, scopée selon le rôle de l'utilisateur. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $equipements = Equipement::query()
            ->with(['affectationActive.employe'])
           ->when($user->role === RoleUtilisateur::EMPLOYE, function ($q) use ($user) {
    $q->whereHas('affectations', function ($sub) use ($user) {
        $sub->where('employe_id', $user->id)->where('statut', 'EN_COURS');
    });
})
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('etat'), fn ($q) => $q->where('etat', $request->string('etat')))
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
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return EquipementResource::collection($equipements);
    }

    public function store(StoreEquipementRequest $request): JsonResponse
    {
        $equipement = Equipement::create($request->donnees());

        $this->synchroniserAffectation($equipement, $request->validated()['employeId'] ?? null);

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

        if ($user->role === RoleUtilisateur::TECHNICIEN && $equipement->technicien_id !== $user->id) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }

        $equipement->load(['affectationActive.employe', 'scanReseau']);

        return new EquipementResource($equipement);
    }

    public function update(UpdateEquipementRequest $request, Equipement $equipement): JsonResponse
    {
        $equipement->update($request->donnees());

        $validated = $request->validated();
        if (array_key_exists('employeId', $validated)) {
            $this->synchroniserAffectation($equipement, $validated['employeId']);
        }

        return (new EquipementResource($equipement->fresh(['affectationActive.employe'])))
            ->additional(['message' => __('messages.equipement.modifie')])
            ->response();
    }

    public function destroy(Equipement $equipement): JsonResponse
    {
        $equipement->delete();

        return response()->json(['message' => __('messages.equipement.supprime')]);
    }

    /**
     * Crée, change ou termine l'affectation active d'un équipement selon
     * l'employé sélectionné dans le formulaire (null = retirer l'affectation).
     */
    private function synchroniserAffectation(Equipement $equipement, ?int $employeId): void
    {
        $active = $equipement->affectations()->where('statut', 'EN_COURS')->first();

        // Aucun changement nécessaire : même employé déjà affecté.
        if ($active && $active->employe_id === $employeId) {
            return;
        }

        // Termine l'affectation active existante, le cas échéant.
        if ($active) {
            $active->update(['statut' => 'TERMINEE', 'date_retour' => now()]);
        }

        // Crée la nouvelle affectation si un employé a été sélectionné.
        if ($employeId) {
            Affectation::create([
                'employe_id' => $employeId,
                'equipement_id' => $equipement->id,
                'date_affectation' => now(),
                'statut' => 'EN_COURS',
            ]);
        }
    }
}