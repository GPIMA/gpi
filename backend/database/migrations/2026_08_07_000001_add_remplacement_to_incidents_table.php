<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Poste remplaçant temporaire (motif "Nouvelle date") : on garde une
     * référence sur l'incident tant que l'employé ne l'a pas rendu, plus un
     * horodatage marquant qu'une relance de restitution a déjà été envoyée
     * (sert à distinguer le 1er et le 2e clic sur "Résoudre").
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('equipement_remplacement_id')
                ->nullable()
                ->after('equipement_id')
                ->constrained('equipements')
                ->nullOnDelete();
            $table->timestamp('relance_remplacement_le')->nullable()->after('date_restitution_prevue');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipement_remplacement_id');
            $table->dropColumn('relance_remplacement_le');
        });
    }
};
