<?php

namespace App\Http\Controllers;

use App\Enums\EtatAlerte;
use App\Enums\RoleUtilisateur;
use App\Http\Resources\AlerteResource;
use App\Models\Alerte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlerteController extends Controller
{
    /** Liste filtrable (état, sévérité, équipement) triée par urgence, scopée selon le rôle. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $alertes = Alerte::query()
            ->with(['equipement', 'regle'])
           ->when($user->role === RoleUtilisateur::EMPLOYE, function ($q) use ($user) {
    $q->whereHas('equipement.affectations', function ($sub) use ($user) {
        $sub->where('employe_id', $user->id)->where('statut', 'EN_COURS');
    });
})
            ->when($request->filled('etat'), fn ($q) => $q->where('etat', $request->string('etat')))
            ->when($request->filled('severite'), fn ($q) => $q->where('severite', $request->string('severite')))
            ->when($request->filled('equipement_id'), fn ($q) => $q->where('equipement_id', $request->integer('equipement_id')))
            ->orderByRaw("CASE etat WHEN 'ACTIVE' THEN 0 WHEN 'EN_COURS' THEN 1 ELSE 2 END")
            ->orderByDesc('date_creation')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return AlerteResource::collection($alertes);
    }

    /** Marque une alerte « en cours » de traitement. */
    public function prendre(Alerte $alerte): JsonResponse
    {
        if ($alerte->etat === EtatAlerte::ACTIVE) {
            $alerte->update(['etat' => EtatAlerte::EN_COURS]);
        }

        return (new AlerteResource($alerte->load(['equipement', 'regle'])))->response();
    }

    /** Résout une alerte (resoudre() du diagramme). */
    public function resoudre(Alerte $alerte): JsonResponse
    {
        $alerte->resoudre();

        return (new AlerteResource($alerte->load(['equipement', 'regle'])))
            ->additional(['message' => __('messages.alerte.resolue')])
            ->response();
    }
}