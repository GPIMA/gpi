<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Incident — signalé par un Employe sur un Equipement, pris en charge et résolu
// par un Technicien.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->foreignId('employe_id')->constrained('users')->cascadeOnDelete();      // signalé par
            $table->foreignId('technicien_id')->nullable()->constrained('users')->nullOnDelete(); // résolu par
            $table->string('titre');
            $table->text('description');
            $table->timestamp('date_signalement');
            $table->timestamp('date_resolution')->nullable();
            $table->string('statut')->default('OUVERT');  // StatutIncident
            $table->string('priorite')->default('MOYENNE'); // Severite
            $table->text('solution')->nullable();
            $table->timestamps();

            $table->index(['statut', 'priorite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
