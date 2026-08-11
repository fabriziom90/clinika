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
        Schema::table('appointment_reminders', function (Blueprint $table) {
            $table->integer('attempt')->after('status')->default(0);
            $table->datetime('last_attempt_at')->after('attempt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_reminders', function (Blueprint $table) {
            $table->dropColumn('attempt');
            $table->dropColumn('last_attempt_at');
        });
    }
};
