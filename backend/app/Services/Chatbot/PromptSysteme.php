<?php

namespace App\Services\Chatbot;

/**
 * Construit le prompt système envoyé au LLM : les connaissances générales sur
 * GPI (config('chatbot.system_prompt')) complétées par le contexte de
 * l'utilisateur courant (nom, rôle), pour des réponses personnalisées et
 * conscientes des permissions de la personne qui pose la question.
 */
class PromptSysteme
{
    /**
     * @param  array{nom?: string, role?: string}  $contexte
     */
    public static function construire(array $contexte = []): string
    {
        $base = config('chatbot.system_prompt');

        $nom = trim((string) ($contexte['nom'] ?? ''));
        $role = trim((string) ($contexte['role'] ?? ''));

        if ($nom === '' && $role === '') {
            return $base;
        }

        $ligne = 'Tu t\'adresses actuellement à '.($nom !== '' ? $nom : 'un utilisateur');
        if ($role !== '') {
            $ligne .= ', dont le rôle dans GPI est « '.$role.' »';
        }
        $ligne .= '. Adapte tes réponses à ce rôle : ne présuppose pas d\'accès à des fonctionnalités '
            .'réservées à un rôle supérieur (par exemple l\'administration des comptes est réservée aux '
            .'administrateurs), et tutoie ou vouvoie selon l\'usage professionnel habituel en français.';

        return $base."\n\n".$ligne;
    }
}
