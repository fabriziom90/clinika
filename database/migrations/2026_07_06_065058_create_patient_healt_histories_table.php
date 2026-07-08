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
        Schema::create('patient_health_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('version');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('change_reason')->nullable();
            $table->longText('allergies')->nullable();
            $table->longText('chronic_diseases')->nullable();
            $table->longText('current_therapies')->nullable();
            $table->longText('surgical_history')->nullable();
            $table->longText('family_history')->nullable();
            $table->longText('lifestyle')->nullable();
            $table->longText('vaccinations')->nullable();
            $table->longText('notes')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_healt_histories');
    }
};
