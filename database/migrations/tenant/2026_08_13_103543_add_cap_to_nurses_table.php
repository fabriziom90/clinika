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
        Schema::table('nurses', function (Blueprint $table) {
            $table->text('cap')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('nurses', function (Blueprint $table) {
            $table->dropColumn('cap');
        });
    }
};
