<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "date_restitution_prevue" sert à 2 moments différents avec 2 sens
     * opposés : la date à laquelle l'employé doit ramener son poste au
     * technicien (demanderRestitution), puis, une fois le poste reçu et le
     * motif "Nouvelle date" choisi, la date à laquelle le poste réparé sera
     * rendu à l'employé. "date_reception_poste" marque le moment où le
     * technicien a effectivement reçu le poste (traiterRetour), ce qui
     * permet de distinguer les deux phases côté interface (ex. le bouton
     * "Confirmer réception" ne doit plus s'afficher une fois le poste reçu).
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->timestamp('date_reception_poste')->nullable()->after('date_restitution_prevue');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('date_reception_poste');
        });
    }
};
