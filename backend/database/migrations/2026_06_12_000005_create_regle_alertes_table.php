<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// RegleAlerte — seuil configurable évalué contre les métriques.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regle_alertes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('metrique_cible');      // cpu | ram | disque
            $table->string('operateur')->default('>='); // > >= < <=
            $table->float('seuil');
            $table->string('severite');            // Severite
            $table->string('type_alerte');         // TypeAlerte levé quand la règle se déclenche
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regle_alertes');
    }
};
