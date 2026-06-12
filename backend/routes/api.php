<?php

use App\Http\Controllers\AlerteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\MetriqueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\RegleAlerteController;
use App\Http\Controllers\ScanReseauController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — Système Intelligent de Gestion de Parc IT
|--------------------------------------------------------------------------
| Routes consommées par le frontend React (déploiement séparé).
*/

Route::get('/health', fn () => response()->json(['status' => 'ok', 'service' => 'HK API']));

Route::post('/login', [AuthController::class, 'login']);
Route::get('/enums', [EnumController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // — Parc d'équipements ————————————————————————————————
    // Consultation ouverte à tous les rôles authentifiés…
    Route::get('/equipements', [EquipementController::class, 'index']);
    Route::get('/equipements/{equipement}', [EquipementController::class, 'show']);

    // — Tableau de bord ——————————————————————————————————
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // — Supervision ————————————————————————————————————————
    Route::get('/supervision/apercu', [MetriqueController::class, 'apercu']);
    Route::get('/equipements/{equipement}/metriques', [MetriqueController::class, 'historique']);

    // — Alertes ————————————————————————————————————————————
    Route::get('/alertes', [AlerteController::class, 'index']);

    // — Règles d'alerte (lecture) —————————————————————————
    Route::get('/regles-alerte', [RegleAlerteController::class, 'index']);

    // — Incidents ——————————————————————————————————————————
    // Tous peuvent consulter (portée selon rôle) et signaler.
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']);
    Route::post('/incidents', [IncidentController::class, 'store']);

    // — Notifications ——————————————————————————————————————
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/lues', [NotificationController::class, 'marquerLues']);

    // — Prédictions (IA) ———————————————————————————————————
    Route::get('/predictions', [PredictionController::class, 'index']);
    Route::get('/predictions/modele', [PredictionController::class, 'modele']);

    // — Assistant (chatbot) ————————————————————————————————
    Route::get('/assistant/conversations', [ConversationController::class, 'index']);
    Route::get('/assistant/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/assistant/message', [ConversationController::class, 'envoyer']);

    // …création / modification / suppression / scan réservées à l'administrateur.
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/equipements', [EquipementController::class, 'store']);
        Route::put('/equipements/{equipement}', [EquipementController::class, 'update']);
        Route::delete('/equipements/{equipement}', [EquipementController::class, 'destroy']);
        Route::post('/scan-reseau', [ScanReseauController::class, 'store']);

        Route::post('/regles-alerte', [RegleAlerteController::class, 'store']);
        Route::put('/regles-alerte/{regle}', [RegleAlerteController::class, 'update']);
        Route::delete('/regles-alerte/{regle}', [RegleAlerteController::class, 'destroy']);

        // Gestion des utilisateurs.
        Route::get('/utilisateurs', [UtilisateurController::class, 'index']);
        Route::post('/utilisateurs', [UtilisateurController::class, 'store']);
        Route::put('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'update']);
        Route::delete('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'destroy']);
    });

    // Traitement des alertes et incidents : techniciens et administrateurs.
    Route::middleware('role:ADMIN,TECHNICIEN')->group(function () {
        Route::post('/alertes/{alerte}/prendre', [AlerteController::class, 'prendre']);
        Route::post('/alertes/{alerte}/resoudre', [AlerteController::class, 'resoudre']);
        Route::post('/incidents/{incident}/prendre', [IncidentController::class, 'prendre']);
        Route::post('/incidents/{incident}/resoudre', [IncidentController::class, 'resoudre']);
        Route::post('/predictions/generer', [PredictionController::class, 'generer']);
    });
});
