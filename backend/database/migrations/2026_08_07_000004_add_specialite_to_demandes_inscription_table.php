<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La page vitrine expose désormais, comme le formulaire d'ajout
     * d'utilisateur, une "Spécialité" pour les demandes concernant un
     * technicien.
     */
    public function up(): void
    {
        Schema::table('demandes_inscription', function (Blueprint $table) {
            if (! Schema::hasColumn('demandes_inscription', 'specialite')) {
                $table->string('specialite')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('demandes_inscription', function (Blueprint $table) {
            if (Schema::hasColumn('demandes_inscription', 'specialite')) {
                $table->dropColumn('specialite');
            }
        });
    }
};
