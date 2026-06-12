<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Metrique — relevé de supervision (CPU/RAM/Disque) appartenant à un Equipement
// (composition : suppression en cascade).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metriques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->timestamp('date_heure');
            $table->float('cpu_usage');
            $table->float('ram_usage');
            $table->float('disk_usage');
            $table->timestamps();

            $table->index(['equipement_id', 'date_heure']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metriques');
    }
};
