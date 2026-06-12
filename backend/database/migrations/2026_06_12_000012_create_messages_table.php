<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Message — appartient à une Conversation (composition : cascade).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->text('contenu');
            $table->string('expediteur'); // ExpediteurType : UTILISATEUR | CHATBOT
            $table->timestamp('date_envoi');
            $table->timestamps();

            $table->index(['conversation_id', 'date_envoi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
