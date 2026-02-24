<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_entry_versions', function (Blueprint $table) {

            $table->id();

            // Collegamento alla visita principale
            $table->foreignId('medical_entry_id')
                ->constrained()
                ->onDelete('cascade');

            // Numero versione (1,2,3...)
            $table->unsignedInteger('version');

            // Contenuto congelato della visita
            $table->string('type'); 
            $table->string('title')->nullable();
            $table->text('content');

            $table->timestamps();

            // Evita doppioni
            $table->unique(['medical_entry_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_entry_versions');
    }
};
