<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Discussion libre entre le technicien demandeur et l'Admin/Super Admin qui
// traite une demande de changement d'état.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_changement_etat_commentaires', function (Blueprint $table) {
            $table->id();
            // Nom de contrainte explicite : le nom auto-généré à partir de la
            // table + colonne ("demande_changement_etat_commentaires_demande_
            // changement_etat_id_foreign") dépasse la limite MySQL de 64
            // caractères pour les identifiants.
            $table->foreignId('demande_changement_etat_id')
                ->constrained(table: 'demandes_changement_etat', indexName: 'dcec_demande_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('auteur_id')->constrained('users')->cascadeOnDelete();
            $table->text('contenu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_changement_etat_commentaires');
    }
};
