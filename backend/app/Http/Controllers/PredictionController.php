<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Http\Resources\PredictionResource;
use App\Models\ModeleIA;
use App\Models\Prediction;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Prédictions des futurs incidents (cas d'utilisation « Consulter les
 * prédictions », acteur Système IA).
 */
class PredictionController extends Controller
{
    /** Dernières prédictions, les plus probables en tête, scopées selon le rôle. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $predictions = Prediction::query()
            ->with(['equipement', 'modele'])
            ->when($user->role === RoleUtilisateur::EMPLOYE, function ($q) use ($user) {
                $q->whereHas('equipement.affectations', function ($sub) use ($user) {
                    $sub->where('employe_id', $user->id)->where('statut', 'EN_COURS');
                });
            })
            ->when($user->role === RoleUtilisateur::TECHNICIEN, function ($q) use ($user) {
                $q->whereHas('equipement', function ($sub) use ($user) {
                    $sub->where('technicien_id', $user->id);
                });
            })
            ->when($request->filled('equipement_id'), fn ($q) => $q->where('equipement_id', $request->integer('equipement_id')))
            ->orderByDesc('date_generation')
            ->orderByDesc('probabilite')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return PredictionResource::collection($predictions);
    }

    /** Relance une campagne de prédiction sur le parc (predire du diagramme). */
    public function generer(PredictionService $service): JsonResponse
    {
        $predictions = $service->genererPourParc();

        return response()->json([
            'message' => __('messages.prediction.generee', ['count' => $predictions->count()]),
            'data' => PredictionResource::collection($predictions->load(['equipement', 'modele'])),
        ], 201);
    }

    /** Métadonnées du modèle actif. */
    public function modele(): JsonResponse
    {
        $modele = ModeleIA::first();

        return response()->json([
            'data' => $modele ? [
                'nom' => $modele->nom,
                'algorithme' => $modele->algorithme,
                'version' => $modele->version,
                'precision' => $modele->precision,
                'dateEntrainement' => $modele->date_entrainement,
            ] : null,
        ]);
    }
}