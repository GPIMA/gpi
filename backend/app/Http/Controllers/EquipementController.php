<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipementRequest;
use App\Http\Requests\UpdateEquipementRequest;
use App\Http\Resources\EquipementResource;
use App\Models\Equipement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EquipementController extends Controller
{
    /** Liste filtrable et paginée du parc. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $equipements = Equipement::query()
            ->with(['affectationActive.employe'])
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

        return (new EquipementResource($equipement))
            ->additional(['message' => __('messages.equipement.cree')])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Equipement $equipement): EquipementResource
    {
        $equipement->load(['affectationActive.employe', 'scanReseau']);

        return new EquipementResource($equipement);
    }

    public function update(UpdateEquipementRequest $request, Equipement $equipement): JsonResponse
    {
        $equipement->update($request->donnees());

        return (new EquipementResource($equipement->fresh(['affectationActive.employe'])))
            ->additional(['message' => __('messages.equipement.modifie')])
            ->response();
    }

    public function destroy(Equipement $equipement): JsonResponse
    {
        $equipement->delete();

        return response()->json(['message' => __('messages.equipement.supprime')]);
    }
}
