<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Alerte — déclenchée par un équipement via une RegleAlerte (ou, plus tard,
// une Prediction). prediction_id reçoit sa contrainte en phase Prédictions.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->foreignId('regle_alerte_id')->nullable()->constrained('regle_alertes')->nullOnDelete();
            $table->unsignedBigInteger('prediction_id')->nullable();
            $table->string('type');                // TypeAlerte
            $table->string('severite');            // Severite
            $table->text('message');
            $table->timestamp('date_creation');
            $table->string('etat')->default('ACTIVE'); // EtatAlerte
            $table->timestamp('date_resolution')->nullable();
            $table->timestamps();

            $table->index(['etat', 'severite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
