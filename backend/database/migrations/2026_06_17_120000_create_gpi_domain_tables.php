<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('scan_reseaux')) {
            Schema::create('scan_reseaux', function (Blueprint $table) {
                $table->id();
                $table->string('plage_ip');
                $table->dateTime('date_scan')->index();
                $table->unsignedInteger('duree')->default(0);
                $table->unsignedInteger('nb_detectes')->default(0);
                $table->foreignId('lance_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('equipements')) {
            Schema::create('equipements', function (Blueprint $table) {
                $table->id();
                $table->string('nom')->index();
                $table->string('type')->index();
                $table->string('marque')->nullable();
                $table->string('modele')->nullable();
                $table->string('adresse_ip')->nullable()->unique();
                $table->string('adresse_mac')->nullable()->unique();
                $table->string('etat')->index();
                $table->string('localisation')->nullable();
                $table->date('date_acquisition')->nullable();
                $table->foreignId('scan_reseau_id')->nullable()->constrained('scan_reseaux')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affectations')) {
            Schema::create('affectations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employe_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
                $table->date('date_affectation');
                $table->date('date_retour')->nullable();
                $table->string('statut')->default('EN_COURS')->index();
                $table->timestamps();
                $table->index(['employe_id', 'statut']);
                $table->index(['equipement_id', 'statut']);
            });
        }

        if (! Schema::hasTable('metriques')) {
            Schema::create('metriques', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
                $table->dateTime('date_heure')->index();
                $table->decimal('cpu_usage', 5, 2)->default(0);
                $table->decimal('ram_usage', 5, 2)->default(0);
                $table->decimal('disk_usage', 5, 2)->default(0);
                $table->timestamps();
                $table->index(['equipement_id', 'date_heure']);
            });
        }

        if (! Schema::hasTable('regle_alertes')) {
            Schema::create('regle_alertes', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('metrique_cible');
                $table->string('operateur')->default('>=');
                $table->decimal('seuil', 6, 2);
                $table->string('severite')->index();
                $table->string('type_alerte')->index();
                $table->boolean('actif')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('modele_ias')) {
            Schema::create('modele_ias', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('algorithme');
                $table->dateTime('date_entrainement')->nullable();
                $table->decimal('precision', 5, 4)->default(0);
                $table->string('version')->default('1.0');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('predictions')) {
            Schema::create('predictions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('modele_ia_id')->nullable()->constrained('modele_ias')->nullOnDelete();
                $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
                $table->dateTime('date_generation')->index();
                $table->string('type_panne')->index();
                $table->decimal('probabilite', 5, 4)->default(0);
                $table->unsignedSmallInteger('horizon_jours')->default(7);
                $table->timestamps();
                $table->index(['equipement_id', 'date_generation']);
            });
        }

        if (! Schema::hasTable('alertes')) {
            Schema::create('alertes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
                $table->foreignId('regle_alerte_id')->nullable()->constrained('regle_alertes')->nullOnDelete();
                $table->foreignId('prediction_id')->nullable()->constrained('predictions')->nullOnDelete();
                $table->string('type')->index();
                $table->string('severite')->index();
                $table->text('message');
                $table->dateTime('date_creation')->index();
                $table->string('etat')->index();
                $table->dateTime('date_resolution')->nullable();
                $table->timestamps();
                $table->index(['etat', 'severite']);
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('destinataire_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('alerte_id')->nullable()->constrained('alertes')->nullOnDelete();
                $table->string('sujet');
                $table->text('contenu');
                $table->string('canal')->default('INTERFACE');
                $table->dateTime('date_envoi')->index();
                $table->string('statut')->default('NON_LUE')->index();
                $table->timestamps();
                $table->index(['destinataire_id', 'statut']);
            });
        }

        if (! Schema::hasTable('incidents')) {
            Schema::create('incidents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipement_id')->nullable()->constrained('equipements')->nullOnDelete();
                $table->foreignId('employe_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('technicien_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('titre');
                $table->text('description');
                $table->dateTime('date_signalement')->index();
                $table->dateTime('date_resolution')->nullable();
                $table->string('statut')->index();
                $table->string('priorite')->index();
                $table->text('solution')->nullable();
                $table->timestamps();
                $table->index(['statut', 'priorite']);
            });
        }

        if (! Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('titre')->nullable();
                $table->dateTime('date_debut')->index();
                $table->dateTime('date_fin')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
                $table->text('contenu');
                $table->string('expediteur')->index();
                $table->dateTime('date_envoi')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('alertes');
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('modele_ias');
        Schema::dropIfExists('regle_alertes');
        Schema::dropIfExists('metriques');
        Schema::dropIfExists('affectations');
        Schema::dropIfExists('equipements');
        Schema::dropIfExists('scan_reseaux');
        Schema::dropIfExists('personal_access_tokens');
    }
};
