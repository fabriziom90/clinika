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
        Schema::table('patients', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
            $table->text('personal_code')->nullable()->change();
            $table->text('birthday')->nullable()->change();
            $table->text('city')->nullable()->change();
            $table->text('zip_code')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('genre')->nullable()->change();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('first_visit')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('first_visit');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->text('email')->nullable(false)->change();
            $table->text('personal_code')->nullable(false)->change();
            $table->text('birthday')->nullable(false)->change();
            $table->text('city')->nullable(false)->change();
            $table->text('zip_code')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
            $table->text('genre')->nullable(false)->change();
        });
    }
};
