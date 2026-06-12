<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ModeleIA — modèle de prédiction des pannes.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modele_ias', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('algorithme');
            $table->timestamp('date_entrainement')->nullable();
            $table->float('precision')->default(0);
            $table->string('version')->default('1.0');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modele_ias');
    }
};
