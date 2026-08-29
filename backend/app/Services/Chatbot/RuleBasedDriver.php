<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Str;

/**
 * Assistant hors-ligne : une base de connaissances par mots-clés couvrant le
 * support IT courant ET les modules de l'application GPI elle-même. Sert de
 * moteur par défaut et de repli quand aucune API LLM n'est configurée ou
 * qu'elle échoue — donc doit rester utile à lui seul, sans réseau.
 */
class RuleBasedDriver implements ChatbotDriver
{
    /** @var array<int, array{sujet: string, cles: array<int, string>, reponse: string}> */
    private const BASE = [
        // --- Support IT courant ---
        [
            'sujet' => 'mot de passe',
            'cles' => ['mot de passe', 'mdp', 'password', 'oublie', 'reinitialiser'],
            'reponse' => "Pour réinitialiser votre mot de passe : utilisez le lien « Mot de passe oublié » de votre session, ou contactez un administrateur. Choisissez un mot de passe d'au moins 12 caractères, avec majuscules, chiffres et symboles.",
        ],
        [
            'sujet' => 'imprimante',
            'cles' => ['imprimante', 'imprime', 'impression', 'bourrage', 'cartouche', 'toner'],
            'reponse' => "Problème d'imprimante ? Vérifiez qu'elle est allumée et connectée au réseau, retirez tout bourrage papier, vérifiez le niveau d'encre/toner, puis relancez l'impression. Si elle reste hors service, signalez un incident depuis la page « Incidents ».",
        ],
        [
            'sujet' => 'réseau',
            'cles' => ['reseau', 'internet', 'connexion', 'wifi', 'wi-fi', 'deconnecte', 'ethernet'],
            'reponse' => "Pas de réseau ? Vérifiez le câble Ethernet ou le Wi-Fi, redémarrez votre poste et votre box/routeur si possible, puis testez un autre site. Si la coupure touche plusieurs postes, signalez un incident réseau.",
        ],
        [
            'sujet' => 'lenteur',
            'cles' => ['lent', 'lenteur', 'rame', 'performance', 'bloque', 'freeze', 'plante'],
            'reponse' => "Poste lent ou qui plante ? Fermez les applications inutilisées, redémarrez la machine, vérifiez l'espace disque disponible et l'utilisation CPU/RAM (visible sur le tableau de bord si l'agent de supervision est actif). Si la lenteur persiste, créez un incident pour qu'un technicien analyse les ressources.",
        ],
        [
            'sujet' => 'écran',
            'cles' => ['ecran', 'affichage', 'moniteur', 'noir', 'clignote', 'pixel'],
            'reponse' => "Écran noir ou problème d'affichage ? Vérifiez le câble vidéo et l'alimentation du moniteur, testez un autre câble ou port, et vérifiez que la source d'entrée est correcte. Si rien ne s'affiche, signalez un incident matériel.",
        ],
        [
            'sujet' => 'e-mail',
            'cles' => ['email', 'e-mail', 'mail', 'messagerie', 'outlook', 'courriel'],
            'reponse' => "Souci de messagerie ? Vérifiez votre connexion réseau et vos identifiants. En cas d'erreur de synchronisation, redémarrez le client de messagerie ou reconnectez le compte. Si le problème continue, ouvrez un incident.",
        ],
        [
            'sujet' => 'vpn',
            'cles' => ['vpn', 'acces distant', 'teletravail'],
            'reponse' => "Problème de VPN ? Vérifiez que le client VPN est à jour et vos identifiants valides. Redémarrez le client, puis votre poste si l'erreur persiste. Si la connexion échoue toujours, signalez un incident en précisant le message d'erreur exact.",
        ],
        [
            'sujet' => 'sauvegarde',
            'cles' => ['sauvegarde', 'backup', 'restaurer', 'fichier perdu', 'fichier supprime'],
            'reponse' => "Pour une sauvegarde ou une restauration de fichiers, vérifiez d'abord votre outil de sauvegarde habituel (OneDrive, dossier partagé…). Si un fichier important est perdu et non récupérable vous-même, signalez un incident en précisant le chemin du fichier et la date approximative de perte.",
        ],
        [
            'sujet' => 'mise à jour',
            'cles' => ['mise a jour', 'mise a jour windows', 'update', 'installer un logiciel', 'logiciel manquant'],
            'reponse' => "Pour une mise à jour système ou l'installation d'un logiciel, vérifiez d'abord si vous avez les droits nécessaires sur votre poste. Si l'installation est bloquée ou nécessite des droits administrateur, signalez un incident en précisant le logiciel concerné.",
        ],
        [
            'sujet' => 'sécurité',
            'cles' => ['virus', 'antivirus', 'malware', 'phishing', 'hameconnage', 'suspect'],
            'reponse' => "Doute sur un virus, un e-mail suspect ou une tentative de phishing ? Ne cliquez sur aucun lien ni pièce jointe suspecte, déconnectez le poste du réseau par précaution si une infection est probable, et signalez immédiatement un incident de sécurité — c'est prioritaire.",
        ],
        [
            'sujet' => 'périphériques',
            'cles' => ['usb', 'bluetooth', 'clavier', 'souris', 'casque', 'peripherique', 'webcam'],
            'reponse' => "Périphérique (USB, Bluetooth, clavier, souris, casque, webcam) non reconnu ? Débranchez/rebranchez ou re-appairez, testez un autre port, et redémarrez le poste. Si le périphérique reste inutilisable, signalez un incident matériel.",
        ],
        [
            'sujet' => 'batterie',
            'cles' => ['batterie', 'charge', 'chargeur', 'autonomie'],
            'reponse' => "Problème de batterie ou de charge ? Vérifiez le câble et le bloc d'alimentation, testez une autre prise, et surveillez si la batterie se décharge anormalement vite même après recharge complète. Si le problème persiste, signalez un incident matériel pour un diagnostic ou un remplacement.",
        ],
        [
            'sujet' => 'redémarrage',
            'cles' => ['redemarrer', 'redemarrage', 'eteindre', 'ne s\'allume plus', 'ne demarre pas'],
            'reponse' => "Poste qui ne démarre plus ou comportement anormal ? Faites d'abord un redémarrage complet (arrêt total, pas juste une mise en veille). Si le poste ne s'allume toujours pas ou boucle, signalez un incident matériel en décrivant ce qui s'affiche (écran noir, message d'erreur, bips…).",
        ],

        // --- Modules de l'application GPI ---
        [
            'sujet' => 'incidents',
            'cles' => ['incident', 'signaler', 'panne', 'probleme materiel', 'declarer'],
            'reponse' => "Pour signaler une panne, ouvrez la page « Incidents » et cliquez sur « Signaler un incident ». Décrivez le problème, choisissez l'équipement concerné et joignez une photo si utile : un technicien le prendra en charge et vous pourrez suivre son avancement.",
        ],
        [
            'sujet' => 'changement d\'état',
            'cles' => ['changement d\'etat', 'changement etat', 'restitution', 'demande de changement'],
            'reponse' => "Le changement de statut d'un incident (par exemple passage en réparation ou restitution du poste) se fait via une demande de changement d'état, soumise à l'approbation d'un responsable. Vous pouvez suivre ces demandes et leurs commentaires depuis la page « Demandes de changement d'état ».",
        ],
        [
            'sujet' => 'équipements',
            'cles' => ['equipement', 'materiel informatique', 'inventaire', 'numero de serie', 'poste de travail'],
            'reponse' => "La page « Équipements » liste tout le parc informatique (postes, périphériques) avec leur statut et numéro de série. Vous y voyez les équipements qui vous sont affectés ; un administrateur ou technicien gère l'ajout et le suivi complet du parc.",
        ],
        [
            'sujet' => 'affectations',
            'cles' => ['affectation', 'attribue', 'qui a', 'assigner un equipement'],
            'reponse' => "Les affectations relient un équipement à un employé. Vous pouvez voir vos équipements affectés dans votre espace ; l'attribution ou le transfert d'un équipement à un autre employé est géré par un administrateur ou technicien.",
        ],
        [
            'sujet' => 'alertes',
            'cles' => ['alerte', 'seuil', 'regle d\'alerte', 'notification automatique'],
            'reponse' => "Les alertes sont déclenchées automatiquement selon des règles basées sur les métriques de supervision (CPU, RAM, disque). Vous les consultez sur la page « Alertes » ; un administrateur peut configurer les seuils qui les déclenchent.",
        ],
        [
            'sujet' => 'supervision',
            'cles' => ['cpu', 'ram', 'disque plein', 'espace disque', 'supervision', 'metrique'],
            'reponse' => "La supervision CPU/RAM/disque des postes est visible sur le tableau de bord et alimente les alertes automatiques. Si un poste montre des métriques anormales de façon persistante, signalez un incident pour qu'un technicien investigue.",
        ],
        [
            'sujet' => 'prédictions',
            'cles' => ['prediction', 'ia predictive', 'maintenance predictive', 'anticiper une panne'],
            'reponse' => "La page « Prédictions » utilise l'IA pour anticiper des besoins de maintenance ou risques de panne à partir des données du parc, afin d'agir avant l'incident plutôt qu'après.",
        ],
        [
            'sujet' => 'notifications',
            'cles' => ['notification', 'notifications internes'],
            'reponse' => "Les notifications internes vous informent des événements qui vous concernent (incident traité, demande approuvée, alerte...). Elles sont accessibles depuis l'icône de notifications de l'application.",
        ],
        [
            'sujet' => 'tableau de bord',
            'cles' => ['tableau de bord', 'dashboard', 'accueil de l\'application'],
            'reponse' => "Le tableau de bord donne une vue d'ensemble : métriques de supervision, alertes actives, incidents récents. C'est le meilleur point de départ pour voir l'état général du parc.",
        ],
        [
            'sujet' => 'rôles et permissions',
            'cles' => ['role', 'permission', 'droits', 'administrateur', 'technicien', 'employe'],
            'reponse' => "GPI distingue plusieurs rôles : Super Administrateur et Administrateur (gestion complète des comptes et approbations), Technicien (traitement des incidents et des équipements), Employé (déclare des incidents, consulte ses équipements). Si une action vous semble bloquée, c'est peut-être une question de permission — contactez un administrateur.",
        ],
        [
            'sujet' => 'inscription',
            'cles' => ['inscription', 'creer un compte', 'nouvel utilisateur', 'demande d\'inscription'],
            'reponse' => "La création de compte passe par une demande d'inscription, validée ensuite par un administrateur depuis la page « Demandes d'inscription ». Si votre demande est en attente depuis longtemps, contactez directement un administrateur.",
        ],

        // --- Social ---
        [
            'sujet' => 'salutation',
            'cles' => ['bonjour', 'salut', 'bonsoir', 'coucou', 'hello'],
            'reponse' => "Bonjour ! Je suis l'assistant GPI. Posez-moi une question sur un problème informatique (mot de passe, réseau, imprimante, lenteurs…) ou sur l'application elle-même (équipements, incidents, alertes, prédictions…).",
        ],
        [
            'sujet' => 'aide générale',
            'cles' => ['aide', 'help', 'que peux tu faire', 'comment ca marche'],
            'reponse' => "Je peux vous aider sur deux plans : le support IT courant (mot de passe, réseau, imprimante, lenteurs, écran, e-mail, sécurité, périphériques…) et l'utilisation de l'application GPI (équipements, affectations, incidents, alertes, prédictions, rôles…). Décrivez votre situation, je vous oriente.",
        ],
        [
            'sujet' => 'remerciement',
            'cles' => ['merci', 'super', 'parfait', 'top'],
            'reponse' => "Avec plaisir ! N'hésitez pas à revenir si vous avez une autre question.",
        ],
        [
            'sujet' => 'au revoir',
            'cles' => ['au revoir', 'bye', 'a plus', 'a bientot'],
            'reponse' => "À bientôt ! N'hésitez pas à signaler un incident si un problème persiste.",
        ],
        [
            'sujet' => 'contact humain',
            'cles' => ['parler a quelqu\'un', 'technicien humain', 'vrai personne', 'contacter le support'],
            'reponse' => "Pour parler directement à un humain, le plus rapide est de signaler un incident depuis la page « Incidents » : un technicien le prendra en charge. Pour une question de compte ou de permission, contactez un administrateur.",
        ],
    ];

    public function repondre(string $question, array $historique = [], array $contexte = []): string
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
                $meilleur = $entree;
            }
        }

        if ($meilleur === null) {
            return $this->reponseParDefaut($contexte);
        }

        $reponse = $meilleur['reponse'];

        // Personnalise la salutation avec le prénom, quand on le connaît.
        if ($meilleur['sujet'] === 'salutation' && ! empty($contexte['nom'])) {
            $reponse = "Bonjour {$contexte['nom']} ! ".Str::after($reponse, 'Bonjour ! ');
        }

        return $reponse;
    }

    /** Réponse de repli quand aucun sujet connu ne correspond : liste les sujets couverts plutôt qu'un simple "je ne sais pas". */
    private function reponseParDefaut(array $contexte): string
    {
        $sujets = collect(self::BASE)
            ->pluck('sujet')
            ->reject(fn ($s) => in_array($s, ['salutation', 'aide générale', 'remerciement', 'au revoir', 'contact humain'], true))
            ->implode(', ');

        return "Je n'ai pas de réponse précise à cette question. Je peux vous aider sur : {$sujets}. "
            ."Reformulez votre question, ou signalez un incident depuis l'application pour qu'un technicien vous aide directement.";
    }
}
