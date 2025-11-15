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
         Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('nurse_id');

            $table->date('date');
            $table->dateTime('start_time');
            $table->dateTime('end_time'); 
            $table->integer('duration_minutes')->default(30); 

            $table->string('type')->nullable(); 
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('doctor_id')
                ->references('id')->on('doctors')
                ->cascadeOnDelete();

            $table->foreign('nurse_id')
                ->references('id')->on('nurses')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
