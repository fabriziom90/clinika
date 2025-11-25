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
        Schema::create('inventory_drugs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('drug_id')->nullable();
            $table->date('expiry_date');
            $table->integer('units');
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('clinic_rooms')->nullOnDelete();
            $table->foreign('drug_id')->references('id')->on('drugs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_drugs');
    }
};
