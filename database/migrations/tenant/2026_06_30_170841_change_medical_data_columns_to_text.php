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
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->text('drug_name')->change();
            $table->text('dosage')->nullable()->change();
            $table->text('frequency')->nullable()->change();
            $table->text('duration')->nullable()->change();
        });

        Schema::table('vital_parameters', function (Blueprint $table) {
            $table->text('pressure')->nullable()->change();
            $table->text('heart_rate')->nullable()->change();
            $table->text('temperature')->nullable()->change();
            $table->text('weight')->nullable()->change();
            $table->text('height')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('drug_name')->change();
            $table->string('dosage')->nullable()->change();
            $table->string('frequency')->nullable()->change();
            $table->string('duration')->nullable()->change();
        });

        Schema::table('vital_parameters', function (Blueprint $table) {
            $table->string('pressure')->nullable()->change();
            $table->integer('heart_rate')->nullable()->change();
            $table->float('temperature')->nullable()->change();
            $table->float('weight')->nullable()->change();
            $table->float('height')->nullable()->change();
        });
    }
};
