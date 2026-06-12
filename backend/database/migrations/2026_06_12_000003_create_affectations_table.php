<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Affectation — association attribuée entre un Employe et un Equipement
// (classe d'association du diagramme).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->date('date_affectation');
            $table->date('date_retour')->nullable();
            $table->string('statut')->default('EN_COURS'); // EN_COURS / RETOURNE
            $table->timestamps();

            $table->index(['equipement_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
