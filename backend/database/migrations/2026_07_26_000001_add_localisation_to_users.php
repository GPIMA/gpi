<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Doublon de la migration 2026_07_09_152636_add_localisation_to_users_table
     * (même colonne "localisation" sur "users"), qui casse toute migration
     * fraîche (colonne déjà existante). On rend l'opération idempotente en
     * vérifiant l'existence de la colonne, comme pour
     * 2026_08_07_000003_add_localisation_to_demandes_inscription_table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'localisation')) {
                $table->string('localisation')->nullable()->after('departement');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'localisation')) {
                $table->dropColumn('localisation');
            }
        });
    }
};
