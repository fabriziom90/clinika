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
            $table->dropColumn('cap');
            $table->dropColumn('province');
        });

        Schema::table('nurses', function (Blueprint $table) {
            $table->dropColumn('cap');
            $table->dropColumn('province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('cap', 7);
            $table->string('province', 30);
        });

        Schema::table('nurses', function (Blueprint $table) {
            $table->string('cap', 7);
            $table->string('province', 30);
        });
    }
};
