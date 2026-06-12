<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegleAlerteRequest;
use App\Http\Requests\UpdateRegleAlerteRequest;
use App\Http\Resources\RegleAlerteResource;
use App\Models\RegleAlerte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Configuration des seuils (cas d'utilisation « Configurer les seuils »).
 */
class RegleAlerteController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RegleAlerteResource::collection(RegleAlerte::orderBy('nom')->get());
    }

    public function store(StoreRegleAlerteRequest $request): JsonResponse
    {
        $regle = RegleAlerte::create($request->donnees());

        return (new RegleAlerteResource($regle))->response()->setStatusCode(201);
    }

    public function update(UpdateRegleAlerteRequest $request, RegleAlerte $regle): JsonResponse
    {
        $regle->update($request->donnees());

        return (new RegleAlerteResource($regle->fresh()))->response();
    }

    public function destroy(RegleAlerte $regle): JsonResponse
    {
        $regle->delete();

        return response()->json(['message' => __('messages.regle.supprimee')]);
    }
}
