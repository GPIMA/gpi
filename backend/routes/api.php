<?php

use App\Http\Controllers\AlerteController;
use App\Http\Controllers\ContactController;
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
use App\Http\Controllers\DemandeInscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — Système intelligent de gestion de parc informatique
|--------------------------------------------------------------------------
| Routes consommées par le frontend React.
*/

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'service' => 'GPI API',
    'version' => '1.0.0',
]));
Route::post('/contact', [ContactController::class, 'store']);
Route::post('/demandes-inscription', [DemandeInscriptionController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/enums', [EnumController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/utilisateurs', [UtilisateurController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/equipements', [EquipementController::class, 'index']);
    Route::get('/equipements/{equipement}', [EquipementController::class, 'show']);

    Route::get('/supervision/apercu', [MetriqueController::class, 'apercu']);
    Route::get('/equipements/{equipement}/metriques', [MetriqueController::class, 'historique']);

    Route::get('/alertes', [AlerteController::class, 'index']);

    Route::middleware('role:SUPER_ADMIN,ADMIN')->group(function () {
        Route::get('/regles-alerte', [RegleAlerteController::class, 'index']);
    });

    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']);
    Route::post('/incidents', [IncidentController::class, 'store']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/lues', [NotificationController::class, 'marquerLues']);

    Route::get('/predictions', [PredictionController::class, 'index']);
    Route::get('/predictions/modele', [PredictionController::class, 'modele']);

    Route::get('/assistant/conversations', [ConversationController::class, 'index']);
    Route::get('/assistant/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/assistant/message', [ConversationController::class, 'envoyer']);

    Route::middleware('role:SUPER_ADMIN,ADMIN')->group(function () {
        Route::post('/equipements', [EquipementController::class, 'store']);
        Route::put('/equipements/{equipement}', [EquipementController::class, 'update']);
        Route::delete('/equipements/{equipement}', [EquipementController::class, 'destroy']);
        Route::post('/scan-reseau', [ScanReseauController::class, 'store']);

        Route::post('/regles-alerte', [RegleAlerteController::class, 'store']);
        Route::put('/regles-alerte/{regle}', [RegleAlerteController::class, 'update']);
        Route::delete('/regles-alerte/{regle}', [RegleAlerteController::class, 'destroy']);

        Route::post('/utilisateurs', [UtilisateurController::class, 'store']);
        Route::put('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'update']);
        Route::delete('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'destroy']);
    
        Route::get('/demandes-inscription', [DemandeInscriptionController::class, 'index']);
Route::post('/demandes-inscription/{demande}/approuver', [DemandeInscriptionController::class, 'approuver']);
Route::post('/demandes-inscription/{demande}/rejeter', [DemandeInscriptionController::class, 'rejeter']);
Route::post('/incidents/{incident}/assigner', [IncidentController::class, 'assigner']);
});

    Route::middleware('role:SUPER_ADMIN,ADMIN,TECHNICIEN')->group(function () {
        Route::post('/alertes/{alerte}/prendre', [AlerteController::class, 'prendre']);
        Route::post('/alertes/{alerte}/resoudre', [AlerteController::class, 'resoudre']);
        Route::post('/incidents/{incident}/prendre', [IncidentController::class, 'prendre']);
        Route::post('/incidents/{incident}/resoudre', [IncidentController::class, 'resoudre']);
        Route::post('/predictions/generer', [PredictionController::class, 'generer']);
    });
});