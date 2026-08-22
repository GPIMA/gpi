<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Demande de changement d'état d'un équipement soumise par un technicien,
// en attente d'approbation par un Admin (de son site) ou un Super Admin.
// Ne concerne que les changements hors résolution d'incident : ceux-ci
// restent appliqués immédiatement par IncidentController.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_changement_etat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->foreignId('demandeur_id')->constrained('users')->cascadeOnDelete();
            $table->string('etat_actuel');   // EtatEquipement au moment de la demande
            $table->string('etat_demande');  // EtatEquipement souhaité
            $table->string('statut')->default('EN_ATTENTE'); // StatutDemandeChangementEtat
            $table->text('motif')->nullable();
            $table->foreignId('traite_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('traite_le')->nullable();
            $table->text('commentaire_traitement')->nullable();
            $table->timestamps();

            $table->index(['equipement_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_changement_etat');
    }
};
