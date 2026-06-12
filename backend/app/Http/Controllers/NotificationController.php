<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notifications destinées à l'utilisateur connecté (canal INTERFACE).
 */
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('destinataire_id', $request->user()->id)
            ->orderByDesc('date_envoi')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => NotificationResource::collection($notifications),
            'nonLues' => $notifications->where('statut', 'NON_LUE')->count(),
        ]);
    }

    public function marquerLues(Request $request): JsonResponse
    {
        Notification::where('destinataire_id', $request->user()->id)
            ->where('statut', 'NON_LUE')
            ->update(['statut' => 'LUE']);

        return response()->json(['message' => 'ok']);
    }
}
