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

            $table->string('personal_code')->change();
            $table->string('vat')->change();
            $table->string('birthday')->change();
            $table->string('birth_city')->nullable()->change();
            $table->string('city')->change();
            $table->string('address')->change();
            $table->string('cap')->nullable()->after('address');

            $table->string('phone')->change();
            $table->string('pec')->change();
            $table->string('genre')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('personal_code', 16)->change();
            $table->string('vat', 11)->change();
            $table->date('birthday')->change();
            $table->string('birth_city', 30)->nullable()->change();
            $table->string('city', 30)->change();
            $table->string('address', 70)->change();
            $table->string('cap', 7)->change();
            $table->string('phone', 15)->change();
            $table->string('pec', 80)->nullable()->change();
            $table->string('genre')->change();

            $table->unique('pec');
        });
    }
};
