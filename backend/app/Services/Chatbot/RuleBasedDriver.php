<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Str;

/**
 * Assistant hors-ligne : une base de connaissances par mots-clés couvrant les
 * demandes de support IT les plus fréquentes. Sert de moteur par défaut et de
 * repli quand aucune API LLM n'est configurée.
 */
class RuleBasedDriver implements ChatbotDriver
{
    /** @var array<int, array{cles: array<int, string>, reponse: string}> */
    private const BASE = [
        [
            'cles' => ['mot de passe', 'mdp', 'password', 'oublie', 'reinitialiser'],
            'reponse' => "Pour réinitialiser votre mot de passe : utilisez le lien « Mot de passe oublié » de votre session, ou contactez le support. Pensez à choisir un mot de passe d'au moins 12 caractères.",
        ],
        [
            'cles' => ['imprimante', 'imprime', 'impression', 'bourrage'],
            'reponse' => "Problème d'imprimante ? Vérifiez qu'elle est allumée et connectée au réseau, retirez tout bourrage papier, puis relancez l'impression. Si elle reste hors service, signalez un incident depuis l'application.",
        ],
        [
            'cles' => ['reseau', 'internet', 'connexion', 'wifi', 'deconnecte'],
            'reponse' => "Pas de réseau ? Vérifiez le câble Ethernet ou le Wi-Fi, redémarrez votre poste, et testez un autre site. Si la coupure persiste sur plusieurs postes, il peut s'agir d'une panne réseau — signalez-la.",
        ],
        [
            'cles' => ['lent', 'lenteur', 'rame', 'performance'],
            'reponse' => "Poste lent ? Fermez les applications inutilisées, redémarrez la machine, et vérifiez l'espace disque disponible. Si la lenteur persiste, créez un incident pour qu'un technicien analyse les ressources.",
        ],
        [
            'cles' => ['ecran', 'affichage', 'moniteur', 'noir'],
            'reponse' => "Écran noir ou problème d'affichage ? Vérifiez le câble vidéo et l'alimentation du moniteur, puis testez un autre câble ou port. Si rien ne s'affiche, signalez un incident matériel.",
        ],
        [
            'cles' => ['email', 'e-mail', 'mail', 'messagerie', 'outlook'],
            'reponse' => "Souci de messagerie ? Vérifiez votre connexion réseau et vos identifiants. En cas d'erreur de synchronisation, redémarrez le client de messagerie. Si le problème continue, ouvrez un incident.",
        ],
        [
            'cles' => ['incident', 'signaler', 'panne', 'probleme'],
            'reponse' => "Pour signaler une panne, ouvrez la page « Incidents » et cliquez sur « Signaler un incident ». Décrivez le problème et choisissez l'équipement concerné : un technicien le prendra en charge.",
        ],
        [
            'cles' => ['bonjour', 'salut', 'bonsoir', 'aide', 'help'],
            'reponse' => "Bonjour ! Je suis l'assistant d'aide informatique. Posez-moi votre question (mot de passe, réseau, imprimante, lenteurs…) ou décrivez votre problème.",
        ],
    ];

    public function repondre(string $question, array $historique = []): string
    {
        $normalisee = Str::lower(Str::ascii($question));
        $meilleur = null;
        $scoreMax = 0;

        foreach (self::BASE as $entree) {
            $score = 0;
            foreach ($entree['cles'] as $cle) {
                if (str_contains($normalisee, Str::ascii($cle))) {
                    $score++;
                }
            }
            if ($score > $scoreMax) {
                $scoreMax = $score;
                $meilleur = $entree['reponse'];
            }
        }

        return $meilleur ?? "Je n'ai pas de réponse précise à cette question. Reformulez-la, ou signalez un incident depuis l'application pour qu'un technicien vous aide.";
    }
}
