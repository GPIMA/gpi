<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ScanReseau — un balayage réseau (simulé en l'absence d'agents SNMP réels)
// qui détecte des équipements sur une plage d'adresses.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_reseaux', function (Blueprint $table) {
            $table->id();
            $table->string('plage_ip');
            $table->timestamp('date_scan');
            $table->unsignedInteger('duree')->default(0); // secondes
            $table->unsignedInteger('nb_detectes')->default(0);
            $table->foreignId('lance_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_reseaux');
    }
};
