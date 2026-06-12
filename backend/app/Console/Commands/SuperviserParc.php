<?php

namespace App\Console\Commands;

use App\Services\SupervisionService;
use Illuminate\Console\Command;

/**
 * Un « tick » de supervision : relève les métriques de tous les équipements
 * en ligne et évalue les règles d'alerte. Planifiable (voir routes/console.php).
 */
class SuperviserParc extends Command
{
    protected $signature = 'parc:superviser';

    protected $description = 'Relève les métriques des équipements en ligne et évalue les règles d\'alerte';

    public function handle(SupervisionService $supervision): int
    {
        $resultat = $supervision->tick();

        $this->info(sprintf(
            'Supervision : %d métrique(s), %d alerte(s) générée(s).',
            $resultat['metriques'],
            $resultat['alertes'],
        ));

        return self::SUCCESS;
    }
}
