<?php

namespace App\Services\Chatbot;

/**
 * Contrat d'un moteur de réponse de l'assistant. Permet de basculer entre une
 * base de connaissances locale et une véritable API LLM sans toucher au reste
 * de l'application.
 */
interface ChatbotDriver
{
    /**
     * @param  string  $question  dernière question de l'utilisateur
     * @param  array<int, array{role: string, content: string}>  $historique  tours précédents
     * @param  array{nom?: string, role?: string}  $contexte  infos sur l'utilisateur courant, pour personnaliser la réponse
     */
    public function repondre(string $question, array $historique = [], array $contexte = []): string;
}
