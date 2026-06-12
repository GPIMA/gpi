<?php

namespace App\Http\Controllers;

use App\Enums\CanalNotification;
use App\Enums\EtatAlerte;
use App\Enums\EtatEquipement;
use App\Enums\ExpediteurType;
use App\Enums\RoleUtilisateur;
use App\Enums\Severite;
use App\Enums\StatutIncident;
use App\Enums\TypeAlerte;
use App\Enums\TypeEquipement;
use Illuminate\Http\JsonResponse;

/**
 * Exposes every data-dictionary enum (localized) as the single source of truth
 * for the frontend's select inputs, filters and labels — no option list is
 * duplicated in the React code.
 */
class EnumController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'typeEquipement' => TypeEquipement::options(),
            'etatEquipement' => EtatEquipement::options(),
            'typeAlerte' => TypeAlerte::options(),
            'severite' => Severite::options(),
            'etatAlerte' => EtatAlerte::options(),
            'canalNotification' => CanalNotification::options(),
            'statutIncident' => StatutIncident::options(),
            'expediteurType' => ExpediteurType::options(),
            'roleUtilisateur' => RoleUtilisateur::options(),
        ]);
    }
}
