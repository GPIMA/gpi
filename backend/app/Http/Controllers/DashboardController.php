<?php

namespace App\Http\Controllers;

use App\Enums\EtatAlerte;
use App\Enums\EtatEquipement;
use App\Http\Resources\AlerteResource;
use App\Models\Alerte;
use App\Models\Equipement;
use Illuminate\Http\JsonResponse;

/**
 * Agrégats du tableau de bord (cas d'utilisation « Consulter le Dashboard »).
 */
class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        // Répartition du parc par état (toutes les valeurs de l'enum, même à 0).
        $comptes = Equipement::query()
            ->selectRaw('etat, count(*) as total')
            ->groupBy('etat')
            ->pluck('total', 'etat');

        $parEtat = collect(EtatEquipement::cases())->map(fn ($etat) => [
            'etat' => $etat->value,
            'label' => $etat->label(),
            'total' => (int) ($comptes[$etat->value] ?? 0),
        ]);

        $alertesActives = Alerte::whereIn('etat', [EtatAlerte::ACTIVE, EtatAlerte::EN_COURS]);

        return response()->json([
            'parc' => [
                'total' => Equipement::count(),
                'parEtat' => $parEtat,
            ],
            'alertes' => [
                'actives' => (clone $alertesActives)->count(),
                'parSeverite' => (clone $alertesActives)
                    ->selectRaw('severite, count(*) as total')
                    ->groupBy('severite')
                    ->pluck('total', 'severite'),
                'recentes' => AlerteResource::collection(
                    Alerte::with(['equipement', 'regle'])
                        ->whereIn('etat', [EtatAlerte::ACTIVE, EtatAlerte::EN_COURS])
                        ->orderByDesc('date_creation')
                        ->limit(6)
                        ->get(),
                ),
            ],
        ]);
    }
}
