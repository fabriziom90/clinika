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
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('genre')->after('pec');
        });

        Schema::table('nurses', function (Blueprint $table) {
            $table->string('genre')->after('pec');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table-dropColumn('genre');
        });

        Schema::table('nurses', function (Blueprint $table) {
            $table-dropColumn('genre');
        });
    }
};
