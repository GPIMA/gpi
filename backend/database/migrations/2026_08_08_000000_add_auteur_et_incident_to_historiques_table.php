<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "user_id" désigne l'employé concerné par l'action, pas forcément la
     * personne qui l'a effectuée. On ajoute :
     * - auteur_id : le membre du staff (admin/technicien) qui a réalisé
     *   l'action (ex. affectation d'un équipement).
     * - incident_id : l'incident concerné, pour pouvoir retrouver le
     *   technicien qui lui est actuellement assigné depuis l'historique.
     */
    public function up(): void
    {
        Schema::table('historiques', function (Blueprint $table) {
            $table->foreignId('auteur_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('incident_id')->nullable()->after('equipement_id')->constrained('incidents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('historiques', function (Blueprint $table) {
            $table->dropConstrainedForeignId('auteur_id');
            $table->dropConstrainedForeignId('incident_id');
        });
    }
};
