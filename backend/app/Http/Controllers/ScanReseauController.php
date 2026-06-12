<?php

namespace App\Http\Controllers;

use App\Http\Resources\EquipementResource;
use App\Services\ScanReseauService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Détection automatique des périphériques (cas d'utilisation « Détecter
 * automatiquement les périphériques », acteur SNMP).
 */
class ScanReseauController extends Controller
{
    public function store(Request $request, ScanReseauService $service): JsonResponse
    {
        $data = $request->validate([
            'plageIP' => ['nullable', 'string', 'max:32'],
        ]);

        $plage = $data['plageIP'] ?? config('parc.scan.plage_par_defaut');

        ['scan' => $scan, 'equipements' => $equipements] = $service->lancer($plage, $request->user());

        return response()->json([
            'message' => __('messages.scan.termine', ['count' => $scan->nb_detectes]),
            'scan' => [
                'id' => $scan->id,
                'plageIP' => $scan->plage_ip,
                'dateScan' => $scan->date_scan,
                'duree' => $scan->duree,
                'nbDetectes' => $scan->nb_detectes,
            ],
            'equipements' => EquipementResource::collection($equipements),
        ], 201);
    }
}
