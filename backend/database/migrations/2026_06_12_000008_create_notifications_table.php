<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Notification — destinée à un Utilisateur, éventuellement produite par une Alerte.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destinataire_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('alerte_id')->nullable()->constrained('alertes')->nullOnDelete();
            $table->string('sujet');
            $table->text('contenu');
            $table->string('canal')->default('INTERFACE'); // CanalNotification
            $table->timestamp('date_envoi');
            $table->string('statut')->default('NON_LUE');   // NON_LUE / LUE
            $table->timestamps();

            $table->index(['destinataire_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
