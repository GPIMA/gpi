<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Equipement — un actif du parc informatique.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('type');                 // TypeEquipement
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->string('adresse_ip')->nullable();
            $table->string('adresse_mac')->nullable();
            $table->string('etat')->default('HORS_LIGNE'); // EtatEquipement
            $table->string('localisation')->nullable();
            $table->date('date_acquisition')->nullable();
            // ← Detected by 1 ScanReseau
            $table->foreignId('scan_reseau_id')->nullable()->constrained('scan_reseaux')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'etat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
