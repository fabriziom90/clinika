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
        Schema::create('secretaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('personal_code');
            $table->string('birthday');
            $table->string('birth_city');
            $table->foreignId('nationality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('city');
            $table->string('address');
            $table->string('zip_code');
            $table->string('phone');
            $table->string('genre');
            $table->string('employee_code');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secretaries');
    }
};
