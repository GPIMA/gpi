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
            'anthropic' => new AnthropicDriver(new RuleBasedDriver),
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

        return $this->nettoyerMarkdown($this->moteur()->repondre($question, $historique, $this->contexte($conversation)));
    }

    /**
     * Filet de sécurité : les réponses s'affichent en texte brut dans une
     * bulle de chat (pas de rendu Markdown). Le prompt système demande déjà
     * au LLM de ne pas en utiliser, mais ce n'est pas garanti — on nettoie
     * donc la syntaxe Markdown résiduelle plutôt que de laisser passer des
     * "**", "#" ou "|" littéraux à l'écran.
     */
    private function nettoyerMarkdown(string $texte): string
    {
        // Titres "# Titre" -> "Titre"
        $texte = preg_replace('/^#{1,6}\s*/m', '', $texte);
        // Gras/italique "**texte**", "__texte__", "*texte*" -> "texte"
        $texte = preg_replace('/\*\*(.+?)\*\*/s', '$1', $texte);
        $texte = preg_replace('/__(.+?)__/s', '$1', $texte);
        $texte = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '$1', $texte);
        // Lignes séparatrices "---" / "***" seules sur leur ligne
        $texte = preg_replace('/^(-{3,}|\*{3,})$/m', '', $texte);
        // Lignes de séparation de tableau Markdown "|---|---|"
        $texte = preg_replace('/^\|?[\s:|-]+\|[\s:|-]+\|?$/m', '', $texte);
        // Barres de tableau restantes "| a | b |" -> "a — b"
        $texte = preg_replace('/^\|\s*(.+?)\s*\|$/m', '$1', $texte);
        $texte = str_replace('|', ' — ', $texte);

        // Réduit les lignes vides en excès laissées par le nettoyage.
        return trim(preg_replace('/\n{3,}/', "\n\n", $texte));
    }

    /**
     * Le moteur actif est-il une vraie IA (clé API configurée), ou le mode
     * hors-ligne (base de connaissances locale) ? Affiché dans l'interface
     * pour indiquer clairement à l'utilisateur quel mode répond.
     *
     * @return array{ia: bool, moteur: string}
     */
    public function statut(): array
    {
        $driver = config('chatbot.driver');

        $ia = match ($driver) {
            'anthropic' => ! empty(config('chatbot.anthropic.api_key')),
            'openai' => ! empty(config('chatbot.openai.api_key')),
            default => false,
        };

        return [
            'ia' => $ia,
            'moteur' => $ia ? $driver : 'rule',
        ];
    }

    /** Infos sur l'utilisateur de la conversation, pour personnaliser la réponse (nom, rôle). */
    private function contexte(Conversation $conversation): array
    {
        $utilisateur = $conversation->user;

        if (! $utilisateur) {
            return [];
        }

        return [
            'nom' => trim((string) $utilisateur->prenom) ?: trim($utilisateur->prenom.' '.$utilisateur->nom),
            'role' => $utilisateur->role?->label(),
        ];
    }
}
