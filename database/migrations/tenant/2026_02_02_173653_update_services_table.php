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
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign('services_specialization_id_foreign');
            $table->dropColumn('specialization_id');
            $table->dropColumn('description');

            $table->float('default_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('default_duration')->default(30);

            $table->foreign('specialization_id')->references('id')->on('specialties')->nullOnDelete();
        });
    }
};
