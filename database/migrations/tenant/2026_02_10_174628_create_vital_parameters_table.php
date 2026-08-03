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
        Schema::create('vital_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_entry_id')->constrained()->onDelete('cascade');
            $table->string('pressure')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->float('temperature')->nullable();
            $table->float('weight')->nullable();
            $table->float('height')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_parameters');
    }
};
