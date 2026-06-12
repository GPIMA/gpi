<?php

namespace App\Http\Controllers;

use App\Enums\StatutIncident;
use App\Http\Requests\ResoudreIncidentRequest;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IncidentController extends Controller
{
    /** Les employés ne voient que leurs incidents ; techniciens/admins voient tout. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $incidents = Incident::query()
            ->with(['equipement', 'employe', 'technicien'])
            ->when($user->estEmploye(), fn ($q) => $q->where('employe_id', $user->id))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->string('statut')))
            ->when($request->filled('priorite'), fn ($q) => $q->where('priorite', $request->string('priorite')))
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

        $incident = Incident::create([
            'equipement_id' => $data['equipementId'],
            'employe_id' => $request->user()->id,
            'titre' => $data['titre'],
            'description' => $data['description'],
            'priorite' => $data['priorite'],
            'statut' => StatutIncident::OUVERT,
            'date_signalement' => now(),
        ]);

        return (new IncidentResource($incident->load(['equipement', 'employe'])))
            ->additional(['message' => __('messages.incident.signale')])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Incident $incident): IncidentResource
    {
        return new IncidentResource($incident->load(['equipement', 'employe', 'technicien']));
    }

    /** Prise en charge par le technicien connecté. */
    public function prendre(Request $request, Incident $incident): JsonResponse
    {
        $incident->prendreEnCharge($request->user());

        return (new IncidentResource($incident->load(['equipement', 'employe', 'technicien'])))->response();
    }

    /** Résolution + notification de l'employé à l'origine du signalement. */
    public function resoudre(ResoudreIncidentRequest $request, Incident $incident, NotificationService $notifications): JsonResponse
    {
        $incident->resoudre($request->user(), $request->validated()['solution']);
        $incident->load(['equipement', 'employe', 'technicien']);

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
}
