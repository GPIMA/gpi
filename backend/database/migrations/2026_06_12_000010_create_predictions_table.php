<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Prediction — produite par un ModeleIA pour un Equipement, peut produire une
// Alerte préventive. On finalise ici la contrainte alertes.prediction_id.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modele_ia_id')->constrained('modele_ias')->cascadeOnDelete();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->timestamp('date_generation');
            $table->string('type_panne');        // TypeAlerte
            $table->float('probabilite');         // 0..1
            $table->unsignedInteger('horizon_jours');
            $table->timestamps();

            $table->index(['equipement_id', 'date_generation']);
        });

        Schema::table('alertes', function (Blueprint $table) {
            $table->foreign('prediction_id')->references('id')->on('predictions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alertes', function (Blueprint $table) {
            $table->dropForeign(['prediction_id']);
        });
        Schema::dropIfExists('predictions');
    }
};
