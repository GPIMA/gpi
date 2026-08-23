<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Moteur LLM via une API compatible OpenAI (Groq, OpenRouter, OpenAI…).
 * Tout est piloté par la configuration/l'environnement ; en cas d'erreur ou
 * d'absence de clé, on retombe proprement sur la base de connaissances locale.
 */
class OpenAiCompatibleDriver implements ChatbotDriver
{
    public function __construct(private readonly RuleBasedDriver $repli)
    {
    }

    public function repondre(string $question, array $historique = [], array $contexte = []): string
    {
        $config = config('chatbot.openai');

        if (empty($config['api_key'])) {
            return $this->repli->repondre($question, $historique, $contexte);
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => PromptSysteme::construire($contexte)]],
            array_slice($historique, -20),
            [['role' => 'user', 'content' => $question]],
        );

        try {
            $reponse = Http::withToken($config['api_key'])
                ->timeout($config['timeout'])
                ->acceptJson()
                ->post(rtrim($config['base_url'], '/').'/chat/completions', [
                    'model' => $config['model'],
                    'messages' => $messages,
                    'temperature' => 0.3,
                ]);

            if ($reponse->failed()) {
                Log::warning('Chatbot LLM échec', ['status' => $reponse->status()]);

                return $this->repli->repondre($question, $historique, $contexte);
            }

            $contenu = $reponse->json('choices.0.message.content');

            return is_string($contenu) && $contenu !== ''
                ? trim($contenu)
                : $this->repli->repondre($question, $historique, $contexte);
        } catch (\Throwable $e) {
            Log::warning('Chatbot LLM exception', ['message' => $e->getMessage()]);

            return $this->repli->repondre($question, $historique, $contexte);
        }
    }
}
