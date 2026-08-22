<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La page vitrine (formulaire de contact) envoie un champ "localisation"
     * (Casablanca/Rabat/Tanger), validé dans DemandeInscriptionController,
     * mais la migration d'origine de la table (create_demandes_inscription_table)
     * a été éditée après coup pour ajouter telephone/departement/localisation
     * au lieu de passer par de nouvelles migrations — ces colonnes n'ont donc
     * jamais été créées sur les bases déjà migrées. On les ajoute ici de
     * façon défensive (avec vérification d'existence) pour rattraper toutes
     * les colonnes manquantes, quel que soit l'état réel de la table.
     */
    public function up(): void
    {
        Schema::table('demandes_inscription', function (Blueprint $table) {
            if (! Schema::hasColumn('demandes_inscription', 'telephone')) {
                $table->string('telephone')->nullable();
            }
            if (! Schema::hasColumn('demandes_inscription', 'departement')) {
                $table->string('departement')->nullable();
            }
            if (! Schema::hasColumn('demandes_inscription', 'localisation')) {
                $table->string('localisation')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('demandes_inscription', function (Blueprint $table) {
            foreach (['telephone', 'departement', 'localisation'] as $colonne) {
                if (Schema::hasColumn('demandes_inscription', $colonne)) {
                    $table->dropColumn($colonne);
                }
            }
        });
    }
};
