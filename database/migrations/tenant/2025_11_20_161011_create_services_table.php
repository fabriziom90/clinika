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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->string('code', 40)->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('default_duration')->default(30);
            $table->boolean('active')->default(1);
            $table->timestamps();

            $table->foreign('specialization_id')->references('id')->on('specialties')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
