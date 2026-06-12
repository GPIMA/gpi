<?php

namespace App\Services;

use App\Enums\CanalNotification;
use App\Models\Alerte;
use App\Models\Notification;
use App\Models\User;

/**
 * Émission des notifications. Le canal INTERFACE est rendu dans l'application ;
 * EMAIL/SMS seraient routés vers un transporteur réel — le point d'extension
 * est isolé dans envoyer().
 */
class NotificationService
{
    public function notifier(
        User $destinataire,
        string $sujet,
        string $contenu,
        CanalNotification $canal = CanalNotification::INTERFACE,
        ?Alerte $alerte = null,
    ): Notification {
        $notification = Notification::create([
            'destinataire_id' => $destinataire->id,
            'alerte_id' => $alerte?->id,
            'sujet' => $sujet,
            'contenu' => $contenu,
            'canal' => $canal,
            'date_envoi' => now(),
            'statut' => 'NON_LUE',
        ]);

        $this->envoyer($notification);

        return $notification;
    }

    /** Acheminement réel selon le canal (simulé hors interface). */
    private function envoyer(Notification $notification): void
    {
        // INTERFACE : déjà persistée, l'app l'affichera.
        // EMAIL / SMS : brancher ici un Mailable / une passerelle SMS.
    }
}
