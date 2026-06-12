<?php

namespace App\Services\Chatbot;

use App\Enums\ExpediteurType;
use App\Models\Conversation;

/**
 * Orchestration de l'assistant : choisit le moteur selon la configuration et
 * construit l'historique de la conversation transmis au moteur.
 */
class ChatbotService
{
    public function moteur(): ChatbotDriver
    {
        return match (config('chatbot.driver')) {
            'openai' => new OpenAiCompatibleDriver(new RuleBasedDriver),
            default => new RuleBasedDriver,
        };
    }

    /** Génère la réponse de l'assistant à la dernière question d'une conversation. */
    public function repondre(Conversation $conversation, string $question): string
    {
        $historique = $conversation->messages
            ->map(fn ($m) => [
                'role' => $m->expediteur === ExpediteurType::CHATBOT ? 'assistant' : 'user',
                'content' => $m->contenu,
            ])
            ->values()
            ->all();

        return $this->moteur()->repondre($question, $historique);
    }
}
