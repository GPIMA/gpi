<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// La fonctionnalité « Scan réseau » est retirée du projet : on supprime la
// colonne de liaison sur equipements puis la table scan_reseaux elle-même.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('equipements', 'scan_reseau_id')) {
            Schema::table('equipements', function (Blueprint $table) {
                $table->dropForeign(['scan_reseau_id']);
                $table->dropColumn('scan_reseau_id');
            });
        }

        Schema::dropIfExists('scan_reseaux');
    }

    public function down(): void
    {
        Schema::create('scan_reseaux', function (Blueprint $table) {
            $table->id();
            $table->string('plage_ip');
            $table->timestamp('date_scan');
            $table->unsignedInteger('duree')->default(0);
            $table->unsignedInteger('nb_detectes')->default(0);
            $table->foreignId('lance_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('equipements', function (Blueprint $table) {
            $table->foreignId('scan_reseau_id')->nullable()->after('id')->constrained('scan_reseaux')->nullOnDelete();
        });
    }
};
