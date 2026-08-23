<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Moteur LLM via l'API Anthropic (Claude) — endpoint /v1/messages.
 * Le prompt système est construit dynamiquement par {@see PromptSysteme},
 * avec les connaissances GPI et le contexte de l'utilisateur courant.
 * Contrairement aux API compatibles OpenAI, le prompt système est un champ
 * dédié et l'authentification se fait via l'en-tête `x-api-key`.
 * Tout est piloté par la configuration/l'environnement ; en cas d'erreur ou
 * d'absence de clé, on retombe proprement sur la base de connaissances locale.
 */
class AnthropicDriver implements ChatbotDriver
{
    public function __construct(private readonly RuleBasedDriver $repli)
    {
    }

    public function repondre(string $question, array $historique = [], array $contexte = []): string
    {
        $config = config('chatbot.anthropic');

        if (empty($config['api_key'])) {
            return $this->repli->repondre($question, $historique, $contexte);
        }

        // L'API Anthropic n'accepte que les rôles user/assistant dans
        // `messages` : le prompt système part séparément. On borne
        // l'historique pour garder le prompt léger et rapide.
        $messages = array_merge(
            array_slice($historique, -20),
            [['role' => 'user', 'content' => $question]],
        );

        try {
            $reponse = Http::withHeaders([
                'x-api-key' => $config['api_key'],
                'anthropic-version' => $config['version'],
            ])
                ->timeout($config['timeout'])
                ->acceptJson()
                ->post(rtrim($config['base_url'], '/').'/messages', [
                    'model' => $config['model'],
                    'max_tokens' => $config['max_tokens'],
                    'system' => PromptSysteme::construire($contexte),
                    'messages' => $messages,
                    'temperature' => 0.3,
                ]);

            if ($reponse->failed()) {
                Log::warning('Chatbot LLM (Anthropic) échec', ['status' => $reponse->status(), 'body' => $reponse->body()]);

                return $this->repli->repondre($question, $historique, $contexte);
            }

            $blocs = $reponse->json('content', []);
            $texte = collect($blocs)
                ->first(fn ($bloc) => ($bloc['type'] ?? null) === 'text')['text'] ?? null;

            return is_string($texte) && $texte !== ''
                ? trim($texte)
                : $this->repli->repondre($question, $historique, $contexte);
        } catch (\Throwable $e) {
            Log::warning('Chatbot LLM (Anthropic) exception', ['message' => $e->getMessage()]);

            return $this->repli->repondre($question, $historique, $contexte);
        }
    }
}
