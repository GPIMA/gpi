<?php

namespace App\Http\Controllers;

use App\Http\Resources\EquipementResource;
use App\Http\Resources\MetriqueResource;
use App\Models\Equipement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Supervision des ressources : aperçu du parc et historique par équipement.
 */
class MetriqueController extends Controller
{
    /** Aperçu : chaque équipement en ligne avec son dernier relevé. */
    public function apercu(): JsonResponse
    {
        $equipements = Equipement::query()
            ->with(['metriques' => fn ($q) => $q->latest('date_heure')->limit(1)])
            ->orderBy('nom')
            ->get();

        return response()->json([
            'data' => $equipements->map(function (Equipement $e) {
                $derniere = $e->metriques->first();

                return [
                    'equipement' => new EquipementResource($e),
                    'metrique' => $derniere ? new MetriqueResource($derniere) : null,
                ];
            }),
        ]);
    }

    /** Historique des n derniers relevés d'un équipement (ordre chronologique). */
    public function historique(Request $request, Equipement $equipement): JsonResponse
    {
        $limite = min($request->integer('limite', 100), 500);

        $metriques = $equipement->metriques()
            ->latest('date_heure')
            ->limit($limite)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'equipement' => new EquipementResource($equipement),
            'data' => MetriqueResource::collection($metriques),
        ]);
    }
}
