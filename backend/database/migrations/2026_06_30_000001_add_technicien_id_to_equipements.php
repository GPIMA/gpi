<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->foreignId('technicien_id')
                  ->nullable()
                  ->after('scan_reseau_id')
                  ->constrained('users')
                  ->nullOnDelete();
            $table->index('technicien_id');
        });
    }

    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->dropForeign(['technicien_id']);
            $table->dropColumn('technicien_id');
        });
    }
};