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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('specialty_id')->nullable();
            $table->string('personal_code', 16);
            $table->string('vat', 11);
            $table->date('birthday');
            $table->string('birth_city', 30)->nullable();
            $table->string('city', 30);
            $table->string('address', 70);
            $table->string('cap', 7);
            $table->string('province', 30);
            $table->string('phone', 15);
            $table->string('pec', 80)->unique()->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('specialty_id')->references('id')->on('specialties')->nullOnDelete();
            $table->foreign('nationality_id')->references('id')->on('nationalities')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
